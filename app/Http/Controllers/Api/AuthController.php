<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    public function register(RegisterRequest $request)
    {
        $tenant = Tenant::firstOrCreate(['name' => $request->tenant_name]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'tenant_id' => $tenant->id,
        ]);

        return $this->successResponse($user, 'User created.', 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->only('email', 'password');

        if (!auth()->attempt($data)) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        $user = auth()->user();
        $token = $user->createToken('Access Token')->accessToken;

        return $this->successResponse([
            'token' => $token,
            'user' => $user,
        ], 'Logged in successfully.', 200);
    }
}
