<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\Welcome;
use Illuminate\Http\Request;
use UrlSigner;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')
            ->except([
                'activate',
            ]);
    }

    public function activate(Request $request)
    {
        // 验证
        if (UrlSigner::validate($request->fullUrl())) {
            $user = User::whereEmail($request->email)->first();
            // 激活
            $user->activate();

            // 发送激活通知
            // $user->notify(new Welcome());

            // 可修改为激活成功/失败后重定向的地址 return redirect($url)
            return response('邮件激活成功', 200);
        }

        return response('邮件激活失败', 400);
    }
}
