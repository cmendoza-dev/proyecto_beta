document.addEventListener("alpine:init", () => {
    Alpine.data("attendanceScanner", () => ({
        scannerActive: false,
        dniValue: "",
        scanning: false,
        lastScan: "",
        scanCount: 0,
        detectedCodes: [],
        stream: null,

        // Variables para lector de código de barras
        barcodeBuffer: "",
        barcodeTimeout: null,

        init() {
            this.$watch("scannerActive", (value) => {
                if (!value) {
                    this.stopScanner();
                }
            });

            window.addEventListener("beforeunload", () => {
                if (this.scannerActive) {
                    this.stopScanner();
                }
            });

            // Escuchar eventos del lector de código de barras
            this.initBarcodeReader();
        },

        // ============================================
        // LECTOR DE CÓDIGO DE BARRAS (FÍSICO)
        // ============================================
        initBarcodeReader() {
            console.log("📟 Inicializando detector de lector de código de barras...");

            document.addEventListener("keypress", (e) => {
                // Ignorar si estamos usando la cámara
                if (this.scannerActive) return;

                // Ignorar si el usuario está escribiendo en un input (excepto el DNI)
                const target = e.target;
                if (target.tagName === "INPUT" || target.tagName === "TEXTAREA") {
                    if (target.id !== "dni-input") return;
                }

                // Los lectores de código de barras escriben muy rápido (< 50ms entre teclas)
                clearTimeout(this.barcodeTimeout);

                // Agregar el carácter al buffer
                if (e.key === "Enter") {
                    // El lector envía Enter al final
                    this.processBarcodeInput(this.barcodeBuffer);
                    this.barcodeBuffer = "";
                } else {
                    this.barcodeBuffer += e.key;

                    // Timeout para limpiar el buffer (si el usuario escribe lento, no es el lector)
                    this.barcodeTimeout = setTimeout(() => {
                        this.barcodeBuffer = "";
                    }, 100);
                }
            });

            console.log("✅ Detector de lector de código de barras activo");
            console.log("💡 Enfoca el campo DNI y escanea con el lector físico");
        },

        processBarcodeInput(input) {
            if (!input || input.length < 6) return;

            console.log("==========================================");
            console.log("📟 CÓDIGO DE BARRAS DETECTADO (LECTOR FÍSICO)");
            console.log("   Input completo:", input);

            // Limpiar el código (solo números)
            const cleanCode = input.replace(/[^0-9]/g, "");

            console.log("   Código limpio:", cleanCode);
            console.log("   Longitud:", cleanCode.length);

            // Validar longitud (DNI argentino: 6-9 dígitos)
            if (cleanCode.length >= 6 && cleanCode.length <= 9) {
                console.log("✅ CÓDIGO VÁLIDO");
                this.processCode(cleanCode);
            } else {
                console.log("⚠️ CÓDIGO INVÁLIDO - longitud fuera de rango");
                Swal.fire({
                    icon: "warning",
                    title: "Código inválido",
                    text: `El código tiene ${cleanCode.length} dígitos. Se esperan entre 6-9 dígitos.`,
                    timer: 2000,
                });
            }

            console.log("==========================================\n");
        },

        // ============================================
        // ESCÁNER CON CÁMARA
        // ============================================
        toggleScanner() {
            this.scannerActive = !this.scannerActive;
            if (this.scannerActive) {
                this.$nextTick(() => {
                    this.initScanner();
                });
            } else {
                this.stopScanner();
            }
        },

        initScanner() {
            console.log("📷 Inicializando escáner de cámara...");

            // Verificar si estamos en HTTPS o localhost
            if (
                location.protocol !== "https:" &&
                location.hostname !== "localhost" &&
                location.hostname !== "127.0.0.1"
            ) {
                Swal.fire({
                    icon: "error",
                    title: "Conexión no segura",
                    html:
                        "La cámara solo funciona en:<br>• HTTPS (conexión segura)<br>• localhost<br><br>URL actual: " +
                        location.protocol +
                        "//" +
                        location.hostname,
                    confirmButtonText: "Entendido",
                });
                this.scannerActive = false;
                return;
            }

            if (typeof Quagga === "undefined") {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "El lector de códigos de barras no está disponible. Recarga la página e intenta nuevamente.",
                });
                this.scannerActive = false;
                return;
            }

            const container = document.querySelector("#scanner-container");
            if (!container) {
                console.error("Contenedor del escáner no encontrado");
                this.scannerActive = false;
                return;
            }

            // Limpiar contenedor
            container.innerHTML = "";

            // Solicitar permisos de cámara primero
            navigator.mediaDevices
                .getUserMedia({
                    video: { facingMode: "environment" },
                })
                .then((stream) => {
                    console.log("✅ Permisos de cámara concedidos");
                    // Detener el stream temporal
                    stream.getTracks().forEach((track) => track.stop());

                    // Ahora iniciar Quagga
                    this.startQuagga(container);
                })
                .catch((err) => {
                    console.error(
                        "❌ Error al solicitar permisos de cámara:",
                        err
                    );
                    let errorMsg = "No se pudo acceder a la cámara.";

                    if (err.name === "NotAllowedError") {
                        errorMsg =
                            "Permisos de cámara denegados. Por favor, permite el acceso a la cámara en la configuración de tu navegador.";
                    } else if (err.name === "NotFoundError") {
                        errorMsg =
                            "No se encontró ninguna cámara en tu dispositivo.";
                    } else if (err.name === "NotReadableError") {
                        errorMsg =
                            "La cámara está siendo usada por otra aplicación.";
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Error de cámara",
                        html: errorMsg + "<br><br>Error: " + err.message,
                        confirmButtonText: "Entendido",
                    });

                    this.scannerActive = false;
                    container.innerHTML = "";
                });
        },

        startQuagga(container) {
            Quagga.init(
                {
                    inputStream: {
                        name: "Live",
                        type: "LiveStream",
                        target: container,
                        constraints: {
                            width: { min: 640, ideal: 1920, max: 1920 },
                            height: { min: 480, ideal: 1080, max: 1080 },
                            facingMode: "environment",
                            aspectRatio: { min: 1, max: 2 },
                        },
                        area: {
                            top: "15%",
                            right: "5%",
                            left: "5%",
                            bottom: "15%",
                        },
                    },
                    locator: {
                        patchSize: "large",
                        halfSample: false,
                    },
                    numOfWorkers: navigator.hardwareConcurrency || 4,
                    frequency: 5,
                    decoder: {
                        readers: [
                            "code_128_reader",
                            "code_39_reader",
                            "code_39_vin_reader",
                            "ean_reader",
                            "ean_8_reader",
                            "upc_reader",
                            "upc_e_reader",
                            "codabar_reader",
                            "i2of5_reader",
                            "2of5_reader",
                            "code_93_reader",
                        ],
                        debug: {
                            drawBoundingBox: true,
                            showFrequency: true,
                            drawScanline: true,
                            showPattern: true,
                        },
                        multiple: false,
                    },
                    locate: true,
                },
                (err) => {
                    if (err) {
                        console.error("❌ Error inicializando Quagga:", err);
                        Swal.fire({
                            icon: "error",
                            title: "Error al iniciar escáner",
                            html:
                                err.message +
                                "<br><br>Asegúrate de:<br>• Permitir acceso a la cámara<br>• Usar HTTPS o localhost<br>• Tener buena iluminación",
                            confirmButtonText: "Entendido",
                        });
                        this.scannerActive = false;
                        return;
                    }

                    console.log("✅ Quagga iniciado correctamente");
                    Quagga.start();

                    this.stream = Quagga.CameraAccess.getActiveStreamLabel();

                    Swal.fire({
                        icon: "success",
                        title: "Escáner activo",
                        text: "Coloca el código de barras del DNI frente a la cámara",
                        timer: 2000,
                        showConfirmButton: false,
                    });
                }
            );

            // Evento para dibujar detecciones en tiempo real
            Quagga.onProcessed((result) => {
                const drawingCtx = Quagga.canvas.ctx.overlay;
                const drawingCanvas = Quagga.canvas.dom.overlay;

                if (result) {
                    drawingCtx.clearRect(
                        0,
                        0,
                        drawingCanvas.width,
                        drawingCanvas.height
                    );

                    // Dibujar cajas de posibles códigos (verde)
                    if (result.boxes) {
                        result.boxes
                            .filter((box) => box !== result.box)
                            .forEach((box) => {
                                Quagga.ImageDebug.drawPath(
                                    box,
                                    { x: 0, y: 1 },
                                    drawingCtx,
                                    {
                                        color: "rgba(0, 255, 0, 0.5)",
                                        lineWidth: 2,
                                    }
                                );
                            });
                    }

                    // Dibujar caja del código detectado (azul)
                    if (result.box) {
                        Quagga.ImageDebug.drawPath(
                            result.box,
                            { x: 0, y: 1 },
                            drawingCtx,
                            {
                                color: "#00F",
                                lineWidth: 3,
                            }
                        );
                    }

                    // Dibujar línea del código leído (rojo)
                    if (result.codeResult && result.codeResult.code) {
                        Quagga.ImageDebug.drawPath(
                            result.line,
                            { x: "x", y: "y" },
                            drawingCtx,
                            {
                                color: "red",
                                lineWidth: 3,
                            }
                        );
                    }
                }
            });

            // Evento cuando se detecta un código con cámara
            Quagga.onDetected((result) => {
                if (this.scanning) return;

                const code = result.codeResult.code;
                const format = result.codeResult.format;

                console.log("==========================================");
                console.log("📷 CÓDIGO DETECTADO (CÁMARA)");
                console.log("   Código original:", code);
                console.log("   Formato:", format);
                console.log("   Longitud original:", code.length);
                console.log("==========================================");

                // Limpiar el código (remover caracteres no numéricos)
                const cleanCode = code.replace(/[^0-9]/g, "");

                console.log("🧹 CÓDIGO LIMPIO:", cleanCode);
                console.log("   Longitud limpia:", cleanCode.length);

                // Aceptar códigos de 6-9 dígitos
                if (cleanCode.length >= 6 && cleanCode.length <= 9) {
                    this.detectedCodes.push(cleanCode);
                    this.scanCount++;

                    console.log("✅ CÓDIGO VÁLIDO AGREGADO");
                    console.log("   Código:", cleanCode);
                    console.log("   Total detectados:", this.detectedCodes.length);
                    console.log("   Últimos códigos:", this.detectedCodes.slice(-3));

                    // Si detectamos el mismo código 2 veces consecutivas, procesarlo
                    if (this.detectedCodes.length >= 2) {
                        const lastTwo = this.detectedCodes.slice(-2);
                        console.log("🔄 Comparando últimos 2:", lastTwo);

                        if (lastTwo[0] === lastTwo[1]) {
                            console.log("🎉 CÓDIGO CONFIRMADO (2 lecturas iguales)");
                            this.processCode(lastTwo[0]);
                        }
                    }
                } else {
                    console.log("⚠️ CÓDIGO IGNORADO (longitud incorrecta)");
                    console.log("   Código:", cleanCode);
                    console.log("   Longitud:", cleanCode.length);
                    console.log("   Se esperan entre 6-9 dígitos");
                }

                // Mantener solo los últimos 5 códigos
                if (this.detectedCodes.length > 10) {
                    this.detectedCodes = this.detectedCodes.slice(-5);
                }

                console.log("==========================================\n");
            });
        },

        // ============================================
        // PROCESAMIENTO COMÚN
        // ============================================
        processCode(code) {
            if (this.scanning) return;

            this.scanning = true;

            // Formatear DNI: asegurar 8 dígitos
            if (code.length === 7) {
                this.dniValue = "0" + code;
            } else if (code.length === 6) {
                this.dniValue = "00" + code;
            } else {
                this.dniValue = code;
            }

            this.lastScan = this.dniValue;

            console.log("🎯 DNI PROCESADO:", this.dniValue);

            const container = document.querySelector("#scanner-container");
            if (container) {
                container.style.borderColor = "#10b981";
                container.style.borderWidth = "6px";
            }

            this.playBeep();

            Swal.fire({
                icon: "success",
                title: "DNI Escaneado",
                html:
                    '<strong style="font-size: 24px;">DNI: ' +
                    this.dniValue +
                    "</strong><br><br>Registrando asistencia...",
                timer: 2000,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            if (this.scannerActive) {
                this.stopScanner();
            }

            setTimeout(() => {
                const form = document.getElementById("attendance-form");
                if (form) {
                    form.submit();
                } else {
                    console.error("❌ Formulario 'attendance-form' no encontrado");
                }
            }, 2000);
        },

        playBeep() {
            try {
                const audioContext = new (window.AudioContext ||
                    window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 800;
                oscillator.type = "sine";

                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(
                    0.01,
                    audioContext.currentTime + 0.5
                );

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);
            } catch (error) {
                console.log("No se pudo reproducir el sonido:", error);
            }
        },

        stopScanner() {
            console.log("🛑 Deteniendo escáner de cámara...");

            try {
                if (typeof Quagga !== "undefined") {
                    Quagga.stop();
                    Quagga.offDetected();
                    Quagga.offProcessed();
                }
            } catch (error) {
                console.error("Error deteniendo Quagga:", error);
            }

            this.scannerActive = false;
            this.scanning = false;
            this.detectedCodes = [];
            this.scanCount = 0;

            const container = document.querySelector("#scanner-container");
            if (container) {
                container.style.borderColor = "#3b82f6";
                container.style.borderWidth = "4px";
                container.innerHTML = "";
            }
        },
    }));
});
