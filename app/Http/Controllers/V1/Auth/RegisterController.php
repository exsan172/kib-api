<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Str;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'telepon' => 'required|string|between:10,15|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        // response validation error
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 400);
        }

        try {
            DB::beginTransaction();
            $user = User::create([
                'uuid' => Uuid::uuid4(),
                'name' => $request->name,
                'email' => $request->email,
                'telepon' => $request->telepon,
                'password' => Hash::make($request->password),
                'current_team_id' => 1
            ]);

            $role = Role::whereRoleType('member')->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }

            // send email
            $this->sendEmail($request);

            $tokenResult = $user->createToken('api_token')->plainTextToken;

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Pendaftaran Berhasil!',
                'data' => [
                    'access_token' => $tokenResult,
                    'token_type' => 'Bearer',
                    'user' => new UserResource($user),
                ]
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create user!',
                'error' => $th->getMessage()
            ], 400);
        }
    }


    // kirim email verifikasi ketika daftar
    public function sendEmail($request)
    {
        // try {
        //     $user = User::findOneByEmail($request);
        //     $token = Str::random(64);
        //     DB::table('password_resets')->updateOrInsert(
        //         ['email' => $request->email],
        //         ['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now()]
        //     );

        //     $verification_url = env('FRONTEND_APP_URL') . '/verify-email/' . $token;
        //     $user->update([
        //         'verification_url' => $verification_url
        //     ]);
        //     // send email verification
        //     Mail::send('email.email-verification', [
        //         'verification_url' => $verification_url,
        //         'user_name' => $request->name,

        //     ], function ($message) use ($request) {
        //         $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
        //         $message->to($request->email);

        //         $message->subject('Verifikasi Email');
        //     });
        // } catch (\Throwable $th) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Gagal kirim email',
        //     ], 400);
        // }
    }


    // kirim ulang jika email tidak masuk
    public function resendEmailVerification(Request $request)
    {
        try {
            $user = User::whereEmail($request->email)->first();
            $token = Str::random(64);
            DB::table('password_resets')->updateOrInsert(
                ['email' => $request->email],
                ['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now()]
            );

            $verification_url = env('FRONTEND_APP_URL') . '/verify-email/' . $token;
            $user->update([
                'verification_url' =>  $verification_url
            ]);
            // send email verification
            Mail::send('email.email-verification', [
                'verification_url' => $verification_url,
                'user_name' => $request->name,

            ], function ($message) use ($request) {
                $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
                $message->to($request->email);

                $message->subject('Verifikasi Email');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Email verifikasi berhasil dikirim ulang',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal kirim email',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    // verifikasi email
    public function verificationEmail($token)
    {

        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'User Tidak Ditemukan',
            ], 400);
        }

        $tokenInfo = DB::table('password_resets')->where('token', $token)->first();

        if ($tokenInfo) {
            $user = User::where('email', $tokenInfo->email)->first();

            $user->update(['email_verified_at' => Carbon::now()]);
            DB::table('password_resets')->where('token', $token)->delete();
            return response()->json([
                'status' => 'error',
                'message' => 'Email Berhasil Diverifikasi',
                'data' => new UserResource($user)
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'User Tidak Ditemukan',
        ], 400);
    }
}
