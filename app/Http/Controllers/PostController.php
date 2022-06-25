<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Queue;

//models
use App\Models\Post;

//jobs
use App\Jobs\CreatePostJob;
use App\Jobs\DeletePostJob;
use App\Jobs\UpdatePostJob;

//requests
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::with(['review' => function($query) {
            $query->select('post_id', 'abusive', 'description');
        }])
        ->latest()
        ->get();

        return response()->json($posts, 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Requests\CreatePostRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreatePostRequest $request)
    {
        $image = $request->file('image');
        $imageName = $image->store('public/images');

        Queue::push(new CreatePostJob([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageName
        ]));

        return response()->json([
            'message' => 'Post created.'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::with(['review' => function($query) {
            $query->select('post_id', 'abusive', 'description');
        }])
        ->find($id);

        if(!isset($post)) {
            return response()->json([
                'error' => 'Post not found.'
            ], 404);
        }

        return response()->json($post, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Requests\UpdatePostRequest  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePostRequest $request)
    {
        $post = Post::find($request->post_id);

        if(!isset($post)) {
            return response()->json([
                'error' => 'Post not found.'
            ], 404);
        }

        $image = $request->file('image');
        $imageName = $image->store('public/images');

        Queue::push(new UpdatePostJob([
            'post_id' => $post->id,
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageName
        ]));

        return response()->json([
            'message' => 'Post updated.'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if(!isset($post)) {
            return response()->json([
                'error' => 'Post not found.'
            ], 404);
        }

        Queue::push(new DeletePostJob([
            'post_id' => $post->id
        ]));

        return response()->json([
            'message' => 'Post deleted.'
        ], 200);
    }
}
