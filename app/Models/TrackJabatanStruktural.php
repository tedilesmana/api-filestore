<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackJabatanStruktural extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hrd_personal_track_jbtn_struktural';
    protected $primaryKey = 'trackStruct_uid';

    public function jabatan()
    {
        return $this->belongsTo(MasterJabatan::class,  'track_jabatan_struktural', 'acajbt_uid');
    }
}
