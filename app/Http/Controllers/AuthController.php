<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')
            ->except([
                'register',
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
}
