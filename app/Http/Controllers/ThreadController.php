<?php

namespace App\Http\Controllers;

use App\Http\Resources\ThreadResource;
use App\Models\Thread;
use App\Http\Requests\ThreadRequest;
use Illuminate\Http\Request;

class ThreadController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'active_user'])->except(['index', 'show']);
    }

    public function store(ThreadRequest $request)
    {
        $this->authorize('create', Thread::class);

        $thread = Thread::create($request->all());

        return new ThreadResource($thread);
    }

    public function index(Request $request)
    {
        $threads = Thread::published()
            ->orderByDesc('pinned_at') // 置顶
            ->orderByDesc('excellent_at')
            ->orderByDesc('published_at')
            ->filter($request->all())->paginate($request->get('per_page', 20));

        /** 当 $request->include 包含 user 时，对 user 的查询存在 N + 1 的问题 ？？？ */

        return ThreadResource::collection($threads);
    }

    public function show(Thread $thread)
    {
        $thread->loadMissing('content');

        $thread->update(['cache->views_count' => $thread->cache['views_count'] + 1]);

        if (!$thread->user->is_valid) {
            \abort(404);
        }

        /** 为什么返回的数据自动包含了 User 的信息 ？？？ */

        return new ThreadResource($thread);
    }

    public function update(ThreadRequest $request, Thread $thread)
    {
        $this->authorize('update', $thread);

        $thread->update($request->all());

        return new ThreadResource($thread);
    }

    public function destroy(Thread $thread)
    {
        $this->authorize('delete', $thread);

        $thread->delete();

        return $this->withNoContent();
    }
}
