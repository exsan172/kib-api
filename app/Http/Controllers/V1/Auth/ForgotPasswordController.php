<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Str;

class ForgotPasswordController extends Controller
{
    /**
     * Send a password reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        // response validation error
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'messages' => $validator->errors(),
            ], 400);
        }

        // check if user exists
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        try {
            DB::beginTransaction();
            $token = Str::random(64);
            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                ['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now()]
            );
            $actionUrl = env('FRONTEND_APP_URL') . '/reset-password/' . $token . '/' . base64_encode($request->email);
            Mail::send('email.forgot-password', [
                'url' => $actionUrl,
                'user_name' => $user->name,

            ], function ($message) use ($request) {
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $message->to($request->email);

                $message->subject('Lupa Kata Sandi');
            });
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Link Ubah Kata Sandi Berhasil Dikirim'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Link Lupa Kata Sandi Gagal Dikirim',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function resetPassword(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'password' => 'required|string|confirmed|min:6',
            'token' => 'required',
            'email' => 'required',
        ]);

        if ($validate->fails()) {
            $respon = [
                'error' => true,
                'status_code' => 400,
                'message' => 'Silahkan isi semua form yang tersedia',
                'messages' => $validate->errors(),
            ];
            return response()->json($respon, 400);
        }



        $token = DB::table('password_resets')->where('token', $request->token)->first();

        if ($token) {
            $user = User::whereEmail($token->email)->first();

            if ($user) {
                $user->update(['password' => Hash::make($request->password)]);

                $respon = [
                    'error' => false,
                    'status_code' => 200,
                    'message' => 'Kata sandi berhasil diubah',
                ];
                // delete token
                DB::table('password_resets')->where('token', $request->token)->delete();
                return response()->json($respon, 200);
            }
        }

        $respon = [
            'error' => true,
            'status_code' => 400,
            'message' => 'Kata sandi gagal diubah',
        ];

        return response()->json($respon, 400);
    }
}
