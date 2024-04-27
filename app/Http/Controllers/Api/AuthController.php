<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(UserRequest $request)
    {
        return User::create($request->validated());
    }

    public function login(LoginRequest $request)
    {
        if (Auth::attempt($request->validated())) {
            $token = auth()->user()->createToken('AuthToken')->plainTextToken;
            $data = [
                'access_token' => $token,
                'token_type' => 'Bearer'
            ];
            return sendSuccess($data);
        }

        return sendError(message: 'Invalid Credentials', status: Response::HTTP_UNAUTHORIZED);
    }
}
