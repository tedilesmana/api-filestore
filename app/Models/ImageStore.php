<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImageStore extends Model
{
    use HasFactory;
    use SoftDeletes;
    const CODE = 'A1-IMGST';
    protected $guarded = array('id');

    public function category(){
        return $this->belongsTo(\App\Models\Category::class);
    }

    public function user(){
        return $this->belongsTo(\App\Models\User::class);
    }
}
