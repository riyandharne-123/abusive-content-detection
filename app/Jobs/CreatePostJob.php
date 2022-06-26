<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Filestack\FilestackClient;
use Filestack\FilestackSecurity;
use Filestack\Filelink;

//helpers
use App\Services\AbuseHelper;

//models
use App\Models\Post;

class CreatePostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $client = new FilestackClient(config('app.filestack_api_key'), new FilestackSecurity(config('app.filestack_security_key')));
        $image = $this->data['image'];

        $uploadedFile = Storage::path($image);

        $file = $client->upload($uploadedFile);

        if (isset($file->handle)) {
            Storage::delete($image);

            $security = new FilestackSecurity(config('app.filestack_security_key'), [
                'expiry' => now()->addYears(99)->getTimestamp(),
                'call' => ['read'],
                'handle' => $file->handle
            ]);

            $filelink = new Filelink($file->handle, config('app.filestack_api_key'), $security);
        }

        $abuseHelper = new AbuseHelper();

        $post = new Post();
        $post->title = $this->data['title'];
        $post->description = $this->data['description'];
        if (isset($file->handle)) {
            $post->image = $filelink->signedUrl($security);
            $post->api_key = $file->handle;
        } else {
            $post->image = $image;
            $post->api_key = '1234';
        }

        $post->save();

        $abuseHelper->verify($post->id);
    }
}
