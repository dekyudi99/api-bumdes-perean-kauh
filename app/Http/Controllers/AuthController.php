<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\ApiResponseResources;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ChangePasswordMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Point;

class AuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function register(Request $request)
    {
        $messages = [
            'name.required'=> 'Nama wajib diisi!',
            'name.min' => 'Nama minimal 3 karakter!',
            'name.max' => 'Nama maksimal 100 karakter!',
            'email.required' => 'Email wajib diisi!',
            'email.email' => 'Format email salah!',
            'email.unique' => 'Email sudah digunakan!',
            'password.required' => 'Password wajib diisi!',
            'password.min' => 'Password minimal 8 karakter!',
        ];

        $validator = Validator::make($request->all(), [
            'name'      => 'required|min:3|max:100',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:8',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
        ]);

        if (!$user) {
            return new ApiResponseResources(false, 'Registrasi Gagal!', null, 422);
        }

        Point::create([
            'point' => 0,
            'user_id' => $user->id,
        ]);

        

        $token = $user->createToken('auth_token')->plainTextToken;
        $this->otpService->generate($request->email);

        return new ApiResponseResources(
            true,
            'Registrasi Berhasil!',
            [
                $user,
                'token' => $token,
            ],
            201
        );
    }

    public function login(Request $request)
    {
        $messages = [
            'email.required' => 'Email wajib diisi!',
            'email.email' => 'Format email salah!',
            'password.required' => 'Password wajib diisi!',
            'password.min' => 'Password minimal 8 karakter!',
        ];

        $validator = Validator::make($request->all(), [
            'email'     => 'required|email',
            'password'  => 'required|min:8',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return new ApiResponseResources(false, 'Email atau Password salah', null, 422);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $this->otpService->generate($user->email);

        if (!$user->email_verified) {
            return new ApiResponseResources(
                true,
                'Verifikasi Email Terlebih Dahulu',
                [
                    'token' => $token,
                ],
            );
        }

        return new ApiResponseResources(true, 'Login Berhasil!', [
            'user'  => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return new ApiResponseResources(true, 'Logout Berhasil');
    }

    public function verifyEmail(Request $request) {
        $messages = [
            'otp.required' => 'Kode OTP wajib diisi!',
            'otp.digits' => 'Kode OTP wajib 6 digit!',
            'otp.integer' => 'Kode OTP wajib Berupa Integer',
        ];
        
        $validator = Validator::make($request->all(), [
            'otp' => 'required|integer|digits:6',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $succeed = $this->otpService->verify($request->otp);

        if (!$succeed) {
            return new ApiResponseResources(false, 'Kode OTP salah atau Sudah Kadaluwarsa!', null, 422);
        }

        $user = User::where('id', Auth::user()->id)->first();
        $user->email_verified = 1;
        $user->save();

        return new ApiResponseResources(true, 'Verifikasi Email Berhasil', Null);
    }

    public function forgetPassword(Request $request)
    {
        $messages = [
            'email.required' => 'Email Wajib Diisi!',
            'email.email' => 'Format Email Salah!',
            'email.exists' => 'Email Tidak Terdaftar!',
        ];

        $validator = Validator::make($request->all(), [
            'email'=> 'required|email|exists:users,email',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $user = User::where('email', $request->email)->first();

        $token = Str::random(60);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $token],
        );

        Mail::to($user->email)->send(new ChangePasswordMail($user, $token));

        return new ApiResponseResources(true, 'Link Ganti Password Telah Dikirim ke Email');
    }

    public function changePassword(Request $request)
    {
        $messages = [
            'password.required' => 'Password Wajib Diisi!',
            'password.min' => 'Password Minimal 8 Karakter!',
            'confirmPassword.required' => 'Konfirmasi Password Wajib Diisi!',
            'confirmPassword.same' => 'Konfirmasi Password Harus Sama!',
        ];

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password'=> 'required|min:8|same:confirmPassword',
            'confirmPassword' => 'required|min:8',
        ], $messages);

        if ($validator->fails()) {
            return new ApiResponseResources(false, $validator->errors(), null, 422);
        }

        $reset = DB::table('password_reset_tokens')->where('token', $request->token)->first();

        if (!$reset) {
            return new ApiResponseResources(false, 'Token tidak valid atau sudah kadaluarsa.', null, 400);
        }

        $user = User::where('email', $reset->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $reset->email)->delete();

        return new ApiResponseResources(true, 'Password berhasil diperbarui.');
    }
}
