<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Http\Resources\CommentResource;
use App\Notifications\CommentMyThread;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api', 'active_user'])->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required_without:commentable_id',
            'commentable_id' => 'required_without:user_id|poly_exists:commentable_type',
        ]);

        if ($request->has('user_id')) {
            $builder = Comment::whereUserId($request->get('user_id'));
        } else {
            $model = $request->get('commentable_type');
            $builder = (new $model())->find($request->get('commentable_id'))->comments();
        }

        $comments = $builder->oldest()->valid()->filter($request->all())->paginate($request->get('per_page', 20));

        return CommentResource::collection($comments);
    }

    public function upVote(Comment $comment)
    {
        auth()->user()->upvote($comment);

        return response()->json([]);
    }

    public function downVote(Comment $comment)
    {
        auth()->user()->downvote($comment);

        return response()->json([]);
    }

    public function cancelVote(Comment $comment)
    {
        auth()->user()->cancelVote($comment);

        return response()->json([]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Comment::class);

        $this->validate($request, [
            'commentable_id' => 'required|poly_exists:commentable_type',
            'type' => 'required|in:markdown,html',
            'content.body' =>  'required_if:type,html',
            'content.markdown' => 'required_if:type,markdown',
        ]);

        // ???
        // if (!Comment::isCommentable($request->get('commentable_type'))) {
        //     abort(403, 'Invalid request.');
        // }

        $comment = Comment::create($request->all());

        // XXX: 不科学
        $comment->commentable->user->notify(new CommentMyThread($comment, auth()->user()));

        return new CommentResource($comment);
    }

    public function show(Comment $comment)
    {
        $this->authorize('view', $comment);

        return new CommentResource($comment);
    }

    public function update(Request $request, Comment $comment)
    {
        $this->authorize('update', $comment);

        $this->validate($request, [
            'type' => 'required|in:markdown,html',
            'content.body' => 'required_if:type,html',
            'content.markdown' => 'required_if:type,markdown',
        ]);

        // 疑点：1 如果 comment 表没有字段更新，就不会出发 updated 事件，也就不会更新 content 的内容了；
        //      2 数据库对应的 content 已经更新了，但是此处返回的还是旧的数据。
        $comment->update($request->all());

        return new CommentResource($comment);
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return $this->withNoContent();
    }
}
