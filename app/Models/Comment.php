<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory;
    use SoftDeletes;
    const CODE = 'A1-COMNT';
    protected $guarded = array('id');

    public function user(){
        return $this->belongsTo(\App\Models\User::class);
    }
}
