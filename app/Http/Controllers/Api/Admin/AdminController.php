<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Models\TransactionHistory;
use App\Models\Transfer;
use App\Models\User;
use App\Models\WebView;
use App\Models\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:8'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->all()
                ], 422);
            }


            $user = User::whereEmail($request->email)->first();
            if (!$user && Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ], 401);
            }
            $credentials = $request->only('email', 'password');
            if (auth()->attempt($credentials)) {
                $user = auth()->user();
                $token = $user->createToken('authToken')->plainTextToken;

                return response()->json(data: [
                    "success" => true,
                    "message" => "Admin login successfuly",
                    "access_token" => $token,
                ], status: 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid email or password'
                ], 401);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }


    public function whatsappNumber(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'number' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->all()
                ], 422);
            }

            $whatsapp = WhatsApp::first();
            if ($whatsapp) {
                $whatsapp->number = $request->number;
                $whatsapp->save();
                return response()->json([
                    'success' => true,
                    'message' => "Whatsapp number updated successfully"
                ], 200);
            } else {
                $whatsapp = new WhatsApp();
                $whatsapp->number = $request->number;
                $whatsapp->save();
                return response()->json([
                    'success' => true,
                    'message' => "Whatsapp number added successfully"
                ], 200);
            }
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
            $validator = Validator::make($request->all(), [
                'web_link' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->all()
                ], 422);
            }

            $webview = WebView::first();
            if ($webview) {
                $webview->web_link = $request->web_link;
                $webview->save();
                return response()->json([
                    'success' => true,
                    'message' => "webview link updated successfully"
                ], 200);
            } else {
                $webview = new WebView();
                $webview->web_link = $request->web_link;
                $webview->save();
                return response()->json([
                    'success' => true,
                    'message' => "webview link added successfully"
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
            $validator = Validator::make($request->all(), [
                'content1' => 'required',
                'content2' => 'required',
                'time' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->all()
                ], 422);
            }

            $slider = Slider::first();
            if ($slider) {
                $slider->update([
                    'content1' => $request->content1,
                    'content2' => $request->content2,
                    'time' => $request->time
                ]);
                return response()->json([
                    'success' => true,
                    'message' => "slider  updated successfully"
                ], 200);
            } else {
                Slider::create([
                    'content1' => $request->content1,
                    'content2' => $request->content2,
                    'time' => $request->time
                ]);
                return response()->json([
                    'success' => true,
                    'message' => "Slider added successfully"
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }


    public function users()
    {
        try {
            $users = User::where('id', '!=', 1)->latest()->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }


    public function dashboard()
    {
        try {
            $pending_users = User::whereStatus('pending')->count();
            $active_users = User::whereStatus('active')->count();
            $total_users = User::count();
            $total_transactions = TransactionHistory::count();
            $total_transfer = Transfer::count();

            return response()->json([
                'success' => true,
                'pending_users' => $pending_users,
                'active_users' => $active_users,
                'total_users' => $total_users,
                'total_transactions' => $total_transactions,
                'total_transfer' => $total_transfer,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }

    public function changeStatus($id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user->status == 'pending') {
                $user->status = 'active';
            } else {
                $user->status = 'pending';
            }

            $user->save();

            return response()->json([
               'success' => true,
               'message' => $user->status == 'pending'? 'User status changed to active' : 'User status changed to pending',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
}
