<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    const CODE = 'A1-EMPLO';
    protected $guarded = array('id');

    public function trackJabatan()
    {
        return $this->hasMany(TrackJabatanStruktural::class,  'refkey', 'personal_uid');
    }
}
