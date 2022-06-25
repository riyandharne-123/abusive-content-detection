<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

//helpers
use App\Services\AbuseHelper;

//models
use App\Models\Post;

class UpdatePostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $abuseHelper = new AbuseHelper();
        $post_id = $this->data['post_id'];
        $image = $this->data['image'];

        $post = Post::find($post_id);

        Storage::delete($post->image);

        $post->title = $this->data['title'];
        $post->description = $this->data['description'];
        $post->image = $image;
        $post->save();

        $abuseHelper->verify($post->id);
    }
}
