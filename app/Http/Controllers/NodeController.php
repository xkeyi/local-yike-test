<?php

namespace App\Http\Controllers;

use App\Http\Resources\NodeResource;
use App\Models\Node;
use Illuminate\Http\Request;

class NodeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['index', 'show', 'threads']);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Node::class);

        // todo 字段验证
        $this->validate($request, []);

        $node = Node::create($request->all());

        return new NodeResource($node);
    }

    public function index(Request $request)
    {
        if ($request->has('all')) {
            $builder = Node::with('children')->root();
        } else {
            $builder = Node::leaf();
        }

        $nodes = $builder->latest()
                    ->filter($request->all())
                    ->paginate($request->get('per_page', 20));

        return NodeResource::collection($nodes);
    }

    public function show(Request $request, Node $node)
    {
        return new NodeResource($node);
    }

    public function update(Request $request, Node $node)
    {
        $this->authorize('update', $node);

        $request->validate([
            // validation rules
        ]);

        $node->update($request->all());

        return new NodeResource($node);
    }

    public function destroy(Request $request, Node $node)
    {
        $this->authorize('delete', $node);

        $node->delete();

        return $this->withNoContent();
    }
}
