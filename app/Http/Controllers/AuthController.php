<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')
            ->except([
                'register', 'forgetPassword', 'resetPassword', 'resetPasswordByToken'
            ]);
    }

    public function register(RegisterRequest $request)
    {
        // 创建用户
        $user = User::create([
            'username' => $request->get('username'),
            'email' => $request->get('email'),
            'password' => bcrypt($request->get('password')),
        ]);

        // 发送激活邮件
        $user->sendActiveMail();

        // 分发个人访问令牌
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;

        return response()->json([
            'token' => $token,
        ]);
    }

    public function reset(Request $request)
    {
        return $request->get('email') ?
            $this->resetPasswordByToken($request) :
            $this->resetPassword($request) ;
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|hash:'.auth()->user()->password,
            'password' => 'required|different:old_password|confirmed|min:6',
        ], [
            'old_password.hash' => '旧密码输入错误！',
        ], [
            'old_password' => '旧密码',
        ]);

        auth()->user()->update([
            'password' => bcrypt($request->get('password')),
        ]);

        return response()->json();
    }

    public function resetPasswordByToken(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $this->broker()->reset(
            $this->credentials($request), function ($user, $passpord) {
                $user->password = Hash::make($password);

                $user->setRemenberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return response()->json();
    }

    protected function credentials(Request $request)
    {
        return $request->only(
            'email', 'password', 'password_confirmation', 'token'
        );
    }

    public function forgetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $this->broker()->sendResetLink(
            $request->only('email')
        );

        return response()->json();
    }

    public function broker()
    {
        return Password::broker();
    }
}
