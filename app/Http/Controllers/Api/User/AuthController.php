<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\TransactionHistory;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserWallet;
use App\Models\WebView;
use App\Models\WhatsApp;
use App\Models\Withdraw;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(data: $request->all(), rules: [
                "phone" => "required|unique:users",
                "password" => "required|string|min:8",
            ]);

            if ($validator->fails()) {
                return response()->json(data: [
                    "success" => false,
                    "message" => $validator->errors()->all(),
                ], status: 422);
            } else {
                $user = User::create([
                    "phone" => $request->phone,
                    "password" => Hash::make($request->password),
                ]);

                $user_wallet = new UserWallet();

                $user_wallet->user_id = $user->id;
                $user_wallet->balance = 0;
                $user_wallet->save();

                return response()->json(data: [
                    "success" => true,
                    "message" => "User registered successfully",
                ], status: 201);
            }

        } catch (\Throwable $e) {
            return response()->json(data: [
                "success" => false,
                "message" => $e->getMessage(),
            ], status: 500);
            ;
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(data: $request->all(), rules: [
                "phone" => "required|string|exists:users",
                "password" => "required|string",
            ]);

            if ($validator->fails()) {
                return response()->json(data: [
                    "success" => false,
                    "message" => $validator->errors(),
                ], status: 422);
            }

            $credentials = $request->only('phone', 'password');

            if (auth()->attempt($credentials)) {
                $user = auth()->user();
                $token = $user->createToken('authToken')->plainTextToken;

                return response()->json(data: [
                    "success" => true,
                    "message" => "User logged in successfully",
                    "access_token" => $token,
                ], status: 200);
            } else {
                return response()->json(data: [
                    "success" => false,
                    "message" => "Invalid credentials",
                ], status: 401);
            }

        } catch (\Throwable $e) {
            return response()->json(data: [
                "success" => false,
                "message" => $e->getMessage(),
            ], status: 500);
        }
    }


    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make(data: $request->all(), rules: [
                "name" => "required",
                "whatsapp" => "required|string|min:8",
            ]);

            if ($validator->fails()) {
                return response()->json(data: [
                    "success" => false,
                    "message" => $validator->errors()->all(),
                ], status: 422);
            } else {
                $user = User::findOrFail(auth()->id())->update([
                    "name" => $request->name,
                    "whatsapp" => $request->whatsapp,
                ]); 
                return response()->json(data: [
                    "success" => true,
                    "message" => "profile update successfully",
                ], status: 201);
            }

        } catch (\Throwable $e) {
            return response()->json(data: [
                "success" => false,
                "message" => $e->getMessage(),
            ], status: 500);
            ;
        }
    }

    public function makeDeposit(Request $request)
    {
        try {
            $validator = Validator::make(data: $request->all(), rules: [
                'amount' => 'required',
                'image' => 'required',
            ]);

            $transfer = Transfer::create([
                'account_name' => $request->account_name,
                'account_password' => $request->account_password,
                'amount' => $request->amount,
                'bank_type' => $request->bank_type,
                'email' => $request->email,
                'image' => $request->file('image')->store('transfer', 'public'),
                'user_id' => auth()->id(),
            ]);


            TransactionHistory::create([
                'user_id' => auth()->id(),
                'account_no' => $request->account_name,
                'account_title' => $request->account_title,
                'amount' => $request->amount,
                'bank_type' => $request->bank_type,
                'image' => $request->image,
                'payment_type' => 'deposit',
            ]);


            return response()->json([
                "success" => true,
                "message" => 'Deposit successfully'
            ]);

        } catch (\Throwable $th) {
            return response()->json(data: [
                "success" => false,
                "message" => $th->getMessage(),
            ], status: 500);
        }
    }

    public function showTransaction()
    {
        try {
            $transactions = TransactionHistory::whereUserId(auth()->id())->latest()->get();
            if (count($transactions) < 0) {
                return response()->json([
                    "success" => false,
                    "message" => "Record not found"
                ]);
            }
            return response()->json([
                "success" => true,
                "transactions" => $transactions
            ]);
        } catch (\Throwable $e) {
            return response()->json(data: [
                "success" => false,
                "message" => $e->getMessage(),
            ], status: 500);
        }
    }

    public function withdraw(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required',
                'payment_method' => 'required',
                'account_no' => 'required',
                'account_title' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->all(),
                ]);
            }

            $user_wallet=UserWallet::whereUserId(auth()->id())->first();
            if(!$user_wallet){
                return response()->json([
                    'success' => false,
                    'message' => 'wallet not found',
                ]);
            }

            if($request->amount <=0){
                return response()->json([
                    'success'=> false,
                    'message'=> 'value must be greater then 0 '
                ]);
            }

            if($user_wallet->balance < $request->amount){
                return response()->json([
                    'success'=> false,
                    'message'=> 'Your account has enough money'
                ]);
            }

            Withdraw::create([
                'user_id'=>auth()->id(),
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'account_no' => $request->account_no,
                'account_title' => $request->account_title,
            ]);

            TransactionHistory::create([
                'user_id' => auth()->id(),
                'account_no' => $request->account_no,
                'account_title' => $request->account_title,
                'amount' => $request->amount,
                'bank_type' => $request->payment_method,
                'image' => $request->image,
                'payment_type' => 'withdraw',
            ]);

            return response()->json([
                'success'=>true,
                'message'=>'Withdraw requested successfully'
            ]);

        } catch (\Throwable $e) {
            return response()->json(data: [
                "success" => false,
                "message" => $e->getMessage(),
            ], status: 500);
        }
    }

    
    public function whatsappNumber(Request $request)
    {
        try {
           
            $whatsapp = WhatsApp::first();
            if (!$whatsapp) {
                return response()->json([
                    'success' => false,
                    'message' => "Number not found"
                ], 200);
            } 
            return response()->json([
                "success"=> true,
                "whatsapp"=> $whatsapp
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }


    public function webView(Request $request)
    {
        try {
            $webview = WebView::first();
            if (!$webview) {
                
                return response()->json([
                    'success' => false,
                    'message' => "webview not found"
                ], 200);
            } else {
               
                return response()->json([
                    'success' => true,
                    'webview' => $webview
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function slider(Request $request)
    {
        try { 
            $slider = Slider::first();
            if (!$slider) { 
                return response()->json([
                    'success' => false,
                    'message' => "Slider not found"
                ], 200);
            } else { 
                return response()->json([
                    'success' => true,
                    'slider' => $slider
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}
