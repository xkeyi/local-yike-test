<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserResource;
use App\Models\Notification;
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
                'index', 'activate', 'show', 'updateEmail',
            ]);
    }

    public function index(Request $request)
    {
        $users = User::withoutBanned()
                    ->filter($request->all())
                    ->paginate($request->get('per_page', $request->get('limit', 20)));

        return UserResource::collection($users);
    }

    public function exists(Request $request)
    {
        if ($request->has('email')) {
            return ['success' => User::whereEmail($request->get('email'))->exists()];
        }

        if ($request->has('username')) {
            return ['success' => User::isUsernameExists($request->get('username'))];
        }

        \abort(400);
    }

    /**
     * 激活邮箱
     */
    public function activate(Request $request)
    {
        // 验证
        if (UrlSigner::validate($request->fullUrl())) {
            $user = User::whereEmail($request->email)->first();
            // 激活
            $user->activate();

            // 发送激活通知
            $user->notify(new Welcome());

            // 可修改为激活成功/失败后重定向的地址 return redirect($url)
            return response('邮件激活成功', 200);
        }

        return response('邮件激活失败', 400);
    }

    public function sendActiveMail(Request $request)
    {
        $request->user()->sendActiveMail();

        return response()->json([
            'message' => '激活邮件已发送，请注意查收！',
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user());
    }

    public function natifications(Request $request)
    {
        $notifications = Notification::whereNotifiableId(auth()->id())
                            ->latest()
                            ->filter($request->all())
                            ->paginate($request->get('per_page', 20));

        $request->user()->unreadNotifications->markAsRead();

        return NotificationResource::collection($notifications);
    }

    public function followers(Request $request, User $user)
    {
        $users = $user->followers()->paginate($request->get('per_page', 20));

        return UserResource::collection($users);
    }

    public function followings(Request $request, User $user)
    {
        $users = $user->followings()->paginate($request->get('per_page', 20));

        return UserResource::collection($users);
    }

    // 最新动态
    public function activities(Request $request, User $user)
    {
        $activities = $user->activities()->whereIn('log_name', [
            'published.thread', 'commented.thread', 'follow.user', 'like.thread', 'subscribe.thread', 'subscribe.node'
        ])->paginate($request->get('per_page', 20));

        return ActivityResource::collection($activities);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {

        $this->authorize('update', auth()->user(), $user);
        //$this->authorize('update', $user)

        // todo
        // 添加 FormRequest 验证，或者 $this->validate($request, []);

        $user->update($request->only([
            'name', 'avatar', 'realname', 'bio', 'extends', 'settings', 'cache', 'gender', 'banned_at',
        ]));

        return new UserResource($user);
    }

    public function editEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
        ]);

        $request->user()->sendUpdateMail($request->get('email'));

        return response()->json([
            'message' => '确认邮件已发送到新邮箱，请注意查收！',
        ]);
    }

    public function updateEmail(Request $request)
    {
        if (UrlSigner::validate($request->fullUrl())) {
            $user = User::findOrFail($request->get('user_id'));

            $user->update(['email' => $request->get('email')]);

            return response('邮件修改成功', 200);
        }

        return response('链接已失效', 401);
    }
}
