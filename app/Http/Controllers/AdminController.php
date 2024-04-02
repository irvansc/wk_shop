<?php

namespace App\Http\Controllers;

use constGuard;
use constDefaults;
use App\Models\Admin;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Contracts\Session\Session;

class AdminController extends Controller
{
    public function loginHandler(Request $request)
    {
        $fielType = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if ($fielType == 'email') {
            $request->validate([
                'login_id' => 'required|email|exists:admins,email',
                'password' => 'required|min:5|max:45',
            ], [
                'login_id.required' => 'Email wajib diisi',
                'login_id.email' => 'Email tidak valid',
                'login_id.exists' => 'Email tidak terdaftar',
                'password.required' => 'Password wajib diisi',
                'password.min' => 'Password minimal 5 karakter',
                'password.max' => 'Password maksimal 45 karakter',
            ]);
        } else {
            $request->validate(
                [
                    'login_id' => 'required|exists:admins,username',
                    'password' => 'required|min:5|max:45',

                ],
                [
                    'login_id.required' => 'Username wajib diisi',
                    'login_id.exists' => 'Username tidak terdaftar',
                    'password.required' => 'Password wajib diisi',
                    'password.min' => 'Password minimal 5 karakter',
                    'password.max' => 'Password maksimal 45 karakter',

                ]
            );
        }

        $creds = array($fielType => $request->login_id, 'password' => $request->password);
        if (Auth::guard('admin')->attempt($creds)) {
            return redirect()->route('admin.home');
        } else {
            session()->flash('fail', 'Username atau Password salah');
            return redirect()->back();
        }
    }

    public function logoutHandler(Request $request)
    {
        Auth::guard('admin')->logout();
        session()->flash('fail', 'You have been logged out!');
        return redirect()->route('admin.login');
    }

    public function sendPasswordReserLink(Request $request)
    {

        $request->validate([
            'email' => 'required|email|exists:admins,email',
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Email tidak valid',
            'email.exists' => 'Email tidak terdaftar',
        ]);

        $admin = Admin::where('email', $request->email)->first();
        $token = base64_encode(Str::random(64));
        $oldToken = DB::table('password_reset_tokens')
            ->where(['email' => $request->email, 'guard' => constGuard::ADMIN])
            ->first();
        if ($oldToken) {
            DB::table('password_reset_tokens')
                ->where(['email', $request->email, 'guard' => constGuard::ADMIN])
                ->update([
                    'token' => $token,
                    'created_at' => now(),
                ]);
        } else {
            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'guard' => constGuard::ADMIN,
                'token' => $token,
                'created_at' => now(),
            ]);
        }

        $actionLink = route('admin.reset-password', ['token' => $token, 'email' => $request->email]);
        $data = array(
            'actionLink' => $actionLink,
            'admin' => $admin,
        );

        $mail_body = view('email-templates.admin-forgot-email-template', $data)->render();
        $mailConfig = array(
            'mail_from_email' => env('EMAIL_FROM_ADDRESS'),
            'mail_from_name' => env('EMAIL_FROM_NAME'),
            'mail_recipient_email' => $request->email,
            'mail_recipient_name' => $admin->name,
            'mail_subject' => 'Reset Password',
            'mail_body' => $mail_body,
        );

        if (sendEmail($mailConfig)) {
            session()->flash('success', 'We have sent a reset password link to your email address');
            return redirect()->back();
        } else {
            session()->flash('fail', 'Email gagal dikirim');
            return redirect()->back();
        }
    }

    public function resetPassword(Request $request, $token = null)
    {
        $check_token = DB::table('password_reset_tokens')
            ->where(['token' => $token, 'guard' => constGuard::ADMIN])
            ->first();

        if ($check_token) {
            // chek expired token
            $diffMins = Carbon::createFromFormat('Y-m-d H:i:s', $check_token->created_at)->diffInMinutes(Carbon::now());
            if ($diffMins > constDefaults::tokenExpiredMinutes) {
                session()->flash('fail', 'Token expired! Please try again');
                return redirect()->route('admin.forgot-password', ['token' => $token]);
            } else {
            }
            return view('back.pages.admin.auth.reset-password', ['token' => $token]);
        } else {
            session()->flash('fail', 'Token invalid! Please try again');
            return redirect()->route('admin.forgot-password', ['token' => $token]);
        }
    }

    public function resetPasswordHandler(Request $request)
    {
        $request->validate([
            'new_password' => 'required|min:5|max:45|required_with:new_password_confirmation|same:new_password_confirmation',
            'new_password_confirmation' => 'required',
        ], [
            'new_password.required' => 'Password wajib diisi',
            'new_password.min' => 'Password minimal 5 karakter',
            'new_password.max' => 'Password maksimal 45 karakter',
            'new_password_confirmation.required_with' => 'Konfirmasi password wajib diisi',
            'new_password_confirmation.same' => 'Konfirmasi password tidak sama',
        ]);

        $token = DB::table('password_reset_tokens')
            ->where(['token' => $request->token, 'guard' => constGuard::ADMIN])
            ->first();
        $admin = Admin::where('email', $token->email)->first();
        Admin::where('email', $token->email)->update([
            'password' => Hash::make($request->new_password),
        ]);
        DB::table('password_reset_tokens')
            ->where([
                'email' => $request->email,
                'token' => $request->token,
                'guard' => constGuard::ADMIN
            ])
            ->delete();
        $data = array(
            'admin' => $admin,
            'new_password' => $request->new_password,
        );

        $mail_body = view('email-templates.admin-reset-email-template', $data)->render();
        $mailConfig = array(
            'mail_from_email' => env('EMAIL_FROM_ADDRESS'),
            'mail_from_name' => env('EMAIL_FROM_NAME'),
            'mail_recipient_email' => $admin->email,
            'mail_recipient_name' => $admin->name,
            'mail_subject' => 'Password Changed',
            'mail_body' => $mail_body,
        );
       sendEmail($mailConfig);
       return redirect()->route('admin.login')->with('success', 'Password has been changed!');
    }

    public function profileView(Request $request){
        $admin = null;
        if (Auth::guard('admin')->check()) {
            $admin = Admin::findOrFail(auth()->id());
        }
        return view('back.pages.admin.profile', ['admin' => $admin]);
    }
}
