<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'title',
        'description',
        'image'
    ];

    public function review() {
        return $this->hasOne('App\Models\Review', 'post_id', 'id');
    }
}
