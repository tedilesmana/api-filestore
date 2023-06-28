<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterLovGroup extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $guarded = array('id');

    public function masterLovValues()
    {
        return $this->hasMany(MasterLovValue::class);
    }
}
