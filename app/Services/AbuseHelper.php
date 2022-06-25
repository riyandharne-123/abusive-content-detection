<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Exception\ConnectException;

//models
use App\Models\Post;
use App\Models\Review;

class AbuseHelper {
    public function verify($post_id) {
        $post = Post::with('review')->find($post_id);

        try {
            $response = Http::asForm()->post('https://apis.paralleldots.com/v4/abuse', [
                'text' => $post->title . ' ' . $post->description,
                'api_key' => config('app.komprehend_api_key')
            ]);
        } catch (ConnectException $e) {
            return response()->json([
                'error' => 'Something went wrong.'
            ], 500);
        }

        $textResult = json_decode($response->body());

        if(isset($textResult) && !strpos($textResult->abusive, 'E') && floatval($textResult->abusive) >= 0.50) {
            Review::updateOrCreate([
                'id' => isset($post->review) ? $post->review->id : null,
            ], [
                'post_id' => $post->id,
                'abusive' => true,
                'description' => 'Abusive content.'
            ]);
        } else {
            Review::updateOrCreate([
                'id' => isset($post->review) ? $post->review->id : null,
            ], [
                'post_id' => $post->id,
                'abusive' => false,
                'description' => 'This post does not contain abusive content.'
            ]);
        }
    }
}
