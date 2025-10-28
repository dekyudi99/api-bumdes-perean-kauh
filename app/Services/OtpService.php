<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\Temporary_token;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    public function generate($email)
    {
        $hasToken = Temporary_token::where('email', $email)->first();

        if (!$hasToken) {
            $otp = Temporary_token::create([
                'email'      => $email,
                'token'      => random_int(100000, 999999),
                'expired_at' => Carbon::now()->addMinutes(5),
            ]);
            Mail::to($email)->send(new OtpMail($otp->token));

            return $otp;
        }

        $hasToken->token = random_int(100000, 999999);
        $hasToken->expired_at = Carbon::now()->addMinutes(5);
        $hasToken->save();

        Mail::to($email)->send(new OtpMail($hasToken->token));
        return $hasToken;
    }

    public function verify($token)
    {
        $otp = Temporary_token::where('email', Auth::user()->email)
            ->where('token', $token)
            ->first();

        if (!$otp || now()->greaterThan($otp->expired_at)) {
            return false;
        }

        $otp->delete();
        return true;
    }
}