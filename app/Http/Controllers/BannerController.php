<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Http\Resources\BannerResource;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $banners = Banner::latest()->filter($request->all())->paginate($request->get('per_page', 20));

        return BannerResource::collection($banners);
    }

    public function store(Request$request)
    {
        $this->authorize('create', Banner::class);

        $this->validate($request, [
            //
        ]);

        $banner = Banner::create($request->all());

        return new BannerResource($banner);
    }

    public function show(Banner $banner)
    {
        return new BannerResource($banner);
    }

    public function update(Banner $banner)
    {
        $this->authorize('update', $banner);

        $this->validate($request, [
            //
        ]);

        $banner->update($request->all());

        return new BannerResource($banner);
    }

    public function destory(Banner $banner)
    {
        $this->authorize('delete', $banner);

        $banner->delete();

        return $this->withNoContent();
    }
}
