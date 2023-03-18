<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::firstWhere('email', $request->username);

        if (!Hash::check($request->password, $user->password)) {
            return response(['errors' => ['password' => ['password is incorrect!']]], 422);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth');

        return response([
            'user' => $user,
            'token' => $token->plainTextToken,
        ]);
    }
}
