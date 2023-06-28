<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubMenu extends Model
{
    use HasFactory;
    use SoftDeletes;
    const CODE = 'A1-SMENU';
    protected $guarded = array('id');

    public function additionalMenus()
    {
        return $this->hasMany(AdditionalMenu::class);
    }
}
