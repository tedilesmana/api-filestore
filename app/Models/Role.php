<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    const CODE = 'A1-ROLES';
    protected $guarded = array('id');

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
