<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use HasFactory;
    use SoftDeletes;
    const CODE = 'A1-MENUS';
    protected $guarded = array('id');

    public function subMenus()
    {
        return $this->hasMany(SubMenu::class);
    }
}
