<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DlbEmployee extends Model
{
    use HasFactory;
    const CODE = 'A1-DLBEM';
    protected $guarded = array('id');
}
