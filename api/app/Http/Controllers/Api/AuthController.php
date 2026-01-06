<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|string|exists:roles,name'
        ]);

        $roleName = $request->role ?? 'user';
        $role = Role::where('name', $roleName)->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role ? $role->id : null,
        ]);

        return response()->json([
            'data' => [
                'user' => $user->load('role'),
                'token' => $user->createToken('api-token')->plainTextToken
            ],
            'message' => 'Usuario registrado exitosamente.'
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas.',
                'errors' => [
                    'email' => ['Las credenciales proporcionadas son incorrectas.']
                ]
            ], 422);
        }

        return response()->json([
            'data' => [
                'user' => $user->load('role'),
                'token' => $user->createToken('api-token')->plainTextToken
            ],
            'message' => 'Inicio de sesión exitoso.'
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'data' => null,
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }
}
