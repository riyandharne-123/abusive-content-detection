<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'post_id',
        'abusive',
        'description'
    ];

    public function post() {
        return $this->belongsTo('App\Models\Post', 'id', 'post_id');
    }
}
