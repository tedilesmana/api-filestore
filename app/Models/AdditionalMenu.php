<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdditionalMenu extends Model
{
    use HasFactory;
    use SoftDeletes;
    const CODE = 'A1-AMENU';
    protected $guarded = array('id');
}
