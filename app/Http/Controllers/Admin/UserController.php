<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filtro por búsqueda
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->search.'%')
                    ->orWhere('last_name', 'like', '%'.$request->search.'%')
                    ->orWhere('email', 'like', '%'.$request->search.'%');
            });
        }

        // Filtro por rol
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:Administrator,Secretary'],
            'is_active' => ['required', 'boolean'],
        ]);

        User::create([
            'first_name' => strtoupper($validated['first_name']),
            'last_name' => strtoupper($validated['last_name']),
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.create', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:Administrator,Secretary,Participant'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Actualizar datos básicos
        $user->first_name = strtoupper($validated['first_name']);
        $user->last_name = strtoupper($validated['last_name']);
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->is_active = $request->has('is_active');

        // Si se proporciona una nueva contraseña, actualizarla
        if (! empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevenir que el usuario se elimine a sí mismo
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'No puedes eliminar tu propio usuario');
        }

        // $user->delete();
        $user->is_active = false;
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado correctamente');
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.show', compact('user'));
    }
}
