<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekonProject extends Model
{
    protected $table = 'rekon_projects';

    protected $fillable = [
        'project_name',
    ];

    public function telkom()
    {
        return $this->hasMany(RekonTelkom::class, 'project_id');
    }
    public function mitra()
    {
        return $this->hasMany(RekonMitra::class, 'project_id');
    }

}
