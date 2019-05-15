<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        return $request->user()->notifacations()->get();
    }

    public function update(Request $request, string $id)
    {
        return $request->user()->unreadNotifications()->whereId($id)->firstOrFail()->markAsRead();
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
    }
}
