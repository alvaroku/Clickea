<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Get authenticated user profile
     */
    public function show(Request $request)
    {
        $user = $request->user()->load(['role', 'profilePicture']);

        return response()->json([
            'data' => $user,
            'message' => 'Perfil obtenido exitosamente.'
        ]);
    }

    /**
     * Update authenticated user profile
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        // Update basic info
        $user->name = $request->name;
        $user->email = $request->email;

        // Update password if provided
        if ($request->filled('new_password')) {
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['La contraseña actual es incorrecta.']
                ]);
            }

            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return response()->json([
            'data' => $user->load(['role', 'profilePicture']),
            'message' => 'Perfil actualizado exitosamente.'
        ]);
    }

    /**
     * Upload or replace profile picture
     */
    public function uploadPicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = $request->user();

        // Delete old profile picture if exists
        if ($user->profile_picture_id) {
            $oldFile = File::find($user->profile_picture_id);
            if ($oldFile) {
                Storage::disk('public')->delete($oldFile->path);
                $oldFile->delete();
            }
        }

        // Store new picture
        $file = $request->file('profile_picture');
        $path = $file->store('profile_pictures', 'public');

        // Create file record
        $fileRecord = File::create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'fileable_id' => $user->id,
            'fileable_type' => get_class($user),
        ]);

        // Update user
        $user->profile_picture_id = $fileRecord->id;
        $user->save();

        return response()->json([
            'data' => $user->load(['role', 'profilePicture']),
            'message' => 'Foto de perfil actualizada exitosamente.'
        ]);
    }

    /**
     * Delete profile picture
     */
    public function deletePicture(Request $request)
    {
        $user = $request->user();

        if (!$user->profile_picture_id) {
            return response()->json([
                'message' => 'No hay foto de perfil para eliminar.'
            ], 404);
        }

        $file = File::find($user->profile_picture_id);
        if ($file) {
            Storage::disk('public')->delete($file->path);
            $file->delete();
        }

        $user->profile_picture_id = null;
        $user->save();

        return response()->json([
            'data' => $user->load(['role', 'profilePicture']),
            'message' => 'Foto de perfil eliminada exitosamente.'
        ]);
    }
}
