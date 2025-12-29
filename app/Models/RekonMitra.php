<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekonMitra extends Model
{
    protected $table = 'rekon_mitra';

    protected $fillable = [
        'project_id',
        'no_layanan',
        'nilai',
    ];
     protected $casts = [
        'nilai' => 'decimal:2',
    ];
}
