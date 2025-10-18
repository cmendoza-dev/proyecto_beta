<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReniecController extends Controller
{
    public function searchByDni(Request $request)
    {
        $request->validate([
            'dni' => 'required|digits:8'
        ]);

        $dni = $request->input('dni');

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('RENIEC_API_TOKEN'),
                    'Accept' => 'application/json',
                ])
                ->get(env('RENIEC_API_URL'), [
                    'numero' => $dni
                ]);

            if ($response->successful()) {
                $data = $response->json();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'dni' => $data['numeroDocumento'] ?? $dni,
                        'first_name' => $data['nombres'] ?? '',
                        'last_name' => ($data['apellidoPaterno'] ?? '') . ' ' . ($data['apellidoMaterno'] ?? ''),
                    ]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se encontró información para el DNI proporcionado'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error al consultar RENIEC: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al consultar el servicio de RENIEC. Por favor, ingrese los datos manualmente.'
            ], 500);
        }
    }
}
