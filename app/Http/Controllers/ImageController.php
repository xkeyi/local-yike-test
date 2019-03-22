<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ImageRequest;

class ImageController extends Controller
{
    public function store(ImageRequest $request)
    {
        $file_path = str_plural($request->type);

        $path = $request->file('image')->store($file_path, 'public');

        return response()->json(['path' => \Storage::disk('public')->url($path)]);
    }
}
