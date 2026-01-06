<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeUserMail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('role');

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== 'all') {
            if ($request->status === 'active') {
                $query->where('active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('active', false);
            }
        }

        // Exclude self if needed, or exclude superadmin from general view
        // $query->where('id', '!=', auth()->id());

        $users = $query->latest()->paginate(10);

        return response()->json([
            'data' => $users,
            'message' => 'Usuarios recuperados exitosamente.'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|exists:roles,name',
            'active' => 'boolean'
        ]);

        $password = Str::random(10);
        $role = Role::where('name', $request->role)->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role_id' => $role->id,
            'active' => $request->active ?? true,
        ]);

        // Send email with credentials
        try {
            Mail::to($user->email)->send(new WelcomeUserMail($user->load('role'), $password));
        } catch (\Exception $e) {
            return response()->json([
                'data' => $user->load('role'),
                'message' => 'Usuario creado, pero hubo un error al enviar el correo con las credenciales.',
                'detail' => $e->getMessage()
            ], 201);
        }

        return response()->json([
            'data' => $user->load('role'),
            'message' => 'Usuario creado exitosamente y correo enviado.'
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|string|email|max:255|unique:users,email,{$user->id}",
            'role' => 'required|string|exists:roles,name',
            'password' => 'nullable|string|min:8',
            'active' => 'required|boolean'
        ]);

        $role = Role::where('name', $request->role)->first();

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $role->id,
            'active' => $request->active,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'data' => $user->load('role'),
            'message' => 'Usuario actualizado exitosamente.'
        ]);
    }

    public function toggleStatus(User $user)
    {
        $user->update(['active' => !$user->active]);
        $status = $user->active ? 'activado' : 'inactivado';

        return response()->json([
            'data' => $user->load('role'),
            'message' => "Usuario {$status} exitosamente."
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->role?->name === 'superadmin') {
            return response()->json([
                'message' => 'No se puede eliminar a un superadministrador.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'data' => null,
            'message' => 'Usuario eliminado exitosamente.'
        ]);
    }
}
