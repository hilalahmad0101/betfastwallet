<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) :JsonResponse{
        try{
            $validator = Validator::make(data: $request->all(), rules: [
                "phone" => "required|string|phone|unique:users",
                "password" => "required|string|min:8|confirmed",
            ]);

            if($validator->fails()){
                return response()->json(data: [
                    "success" => false,
                    "message" => $validator->errors(),
                ], status: 422);
            }else{
                $user = User::create([
                    "phone" => $request->phone,
                    "password" => Hash::make($request->password),
                ]);



                return response()->json(data: [
                    "success" => true,
                    "message" => "User registered successfully",
                ], status: 201);
            }

        }catch(\Throwable $e){
            return response()->json(data: [
                "success" => false,
                "message" => $e->getMessage(),
            ], status: 500);;
        }
    }

    public function login(Request $request) : JsonResponse{
        try{
            $validator = Validator::make(data: $request->all(), rules: [
                "phone" => "required|string|phone",
                "password" => "required|string",
            ]);

            if($validator->fails()){
                return response()->json(data: [
                    "success" => false,
                    "message" => $validator->errors(),
                ], status: 422);
            }

                $credentials = $request->only('phone', 'password');

                if(auth()->attempt($credentials)){
                    $user = auth()->user();
                    $token = $user->createToken('authToken')->plainTextToken;

                    return response()->json(data: [
                        "success" => true,
                        "message" => "User logged in successfully",
                        "access_token" => $token,
                    ], status: 200);
                }else{
                    return response()->json(data: [
                        "success" => false,
                        "message" => "Invalid credentials",
                    ], status: 401);
                }

            }catch(\Throwable $e){
            return response()->json(data: [
                "success" => false,
                "message" => $e->getMessage(),
            ], status: 500);;
        }
    }
}
