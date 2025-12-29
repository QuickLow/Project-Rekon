<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekonTelkom extends Model
{
    protected $table = 'rekon_telkom';

    protected $fillable = [
        'project_id',
        'no_layanan',
        'nama_pelanggan',
        'nilai',
    ];
    protected $casts = [
        'nilai' => 'decimal:2',
    ];
}
