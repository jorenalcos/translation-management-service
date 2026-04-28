<?php

namespace App\Http\Controllers\Api;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'api_token' => Str::random(80),
        ]);

        return response()->json([
            'token' => $user->api_token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($data)) {
            return response()->json([
                'message' => 'The provided credentials are invalid.',
            ], 422);
        }

        $user = Auth::user();
        $user->forceFill(['api_token' => Str::random(80)])->save();

        return response()->json([
            'token' => $user->api_token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->forceFill(['api_token' => null])->save();

        return response()->json(null, 204);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
