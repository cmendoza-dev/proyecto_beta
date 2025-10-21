@extends('layouts.app')

@section('title', 'Detalle Participante')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('participants.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Volver</a>
    </div>

    <div class="p-6 bg-white border border-gray-200 rounded-lg">
        <h2 class="mb-4 text-xl font-bold">{{ $participant->name }} {{ $participant->last_name }}</h2>
        <dl class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div><dt class="text-gray-500 text-sm">DNI</dt><dd class="font-medium">{{ $participant->dni }}</dd></div>
            <div><dt class="text-gray-500 text-sm">Teléfono</dt><dd class="font-medium">{{ $participant->phone }}</dd></div>
            <div><dt class="text-gray-500 text-sm">Email</dt><dd class="font-medium">{{ $participant->email }}</dd></div>
            <div><dt class="text-gray-500 text-sm">Organización</dt><dd class="font-medium">{{ $participant->organization }}</dd></div>
            <div><dt class="text-gray-500 text-sm">Cargo</dt><dd class="font-medium">{{ $participant->position }}</dd></div>
            <div><dt class="text-gray-500 text-sm">Estado</dt><dd class="font-medium">{{ $participant->is_active ? 'Activo' : 'Inactivo' }}</dd></div>
            <div><dt class="text-gray-500 text-sm">Creado</dt><dd class="font-medium">{{ $participant->created_at }}</dd></div>
            <div><dt class="text-gray-500 text-sm">Actualizado</dt><dd class="font-medium">{{ $participant->updated_at }}</dd></div>
        </dl>

        <div class="mt-6 flex gap-2">
            <a href="{{ route('participants.edit', $participant) }}" class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">Editar</a>
            <form action="{{ route('participants.destroy', $participant) }}" method="POST"
                  onsubmit="return confirm('¿Eliminar participante?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700">Eliminar</button>
            </form>
        </div>
    </div>
</div>
@endsection
