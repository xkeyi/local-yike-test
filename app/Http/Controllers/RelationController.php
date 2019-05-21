<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Overtrue\LaracelFollow\FollowRelation;

class RelationController extends Controller
{
    public function __construct()
    {
        $this->middelware(['auth:api', 'active_user'])->except('index');
    }

    public function index(Request $request)
    {
        $this->validate($request, [
            'followable_id' => 'required|poly_exists:followable_type',
        ]);

        $relations = FollowRelation::query()
                        ->where('followable_id', $request->get('followable_id'))
                        ->where('followable_type', $request->get('followable_type'))
                        ->where('relation', $request->get('relation', 'follow'))
                        ->paginate($request->get('per_page', 20));

        return $relations;
    }

    public function toggleRelation(Request $request, $relation)
    {
        $this->validate($request, [
            'relation' => 'required|in:like,follow,subscribe,favorite,upvote,downvote',
            'followable_id' => 'required|poly_exists:followable_type',
        ]);

        $method = 'toggle'.\studly_case($relation);


        // 把 $request->user() 作为命名空间 ？
        \call_user_func_array([$request->user(), $method], $request->only(['followable_id', 'followable_type']));

        return $this->withNoContent();
    }
}
