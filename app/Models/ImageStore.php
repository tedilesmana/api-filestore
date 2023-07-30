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
}
