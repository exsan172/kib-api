<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    // login controller
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        $validate = Validator::make($request->all(), [
            'email'  => 'required|email:rfc,dns',
            'password' => 'required',
        ]);


        if ($validate->fails()) {
            $respon = [
                'error' => true,
                'status_code' => 401,
                'message' => 'Maaf, Silahkan isi semua form yang tersedia',
                'messages' => $validate->errors(),
            ];
            return response()->json($respon, 401);
        }

        if (!$user) {
            $respon = [
                'error' => true,
                'status_code' => 401,
                'message' => 'Maaf, email yang Anda gunakan tidak terdaftar',
            ];
            return response()->json($respon, 401);
        }

        if ($user->status_id == 3) {
            $respon = [
                'error' => true,
                'status_code' => 401,
                'message' => 'Login Gagal, Maaf akun anda telah dinonaktifkan',
            ];
            return response()->json($respon, 401);
        }



        if (!Hash::check($request->password, $user->password)) {
            $respon = [
                'error' => true,
                'status_code' => 401,
                'message' => 'Maaf, email atau kata sandi yang Anda gunakan salah',
            ];
            return response()->json($respon, 401);
        }

        Auth::login($user);

        $tokenResult = $user->createToken('api_token')->plainTextToken;
        $respon = [
            'error' => false,
            'status_code' => 200,
            'message' => 'Selamat! Anda berhasil masuk aplikasi',
            'data' => [
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ]
        ];
        return response()->json($respon, 200);
    }
}
