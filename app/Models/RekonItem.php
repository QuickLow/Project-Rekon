<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekonItem extends Model
{
    protected $table = 'rekon_items';

    protected $fillable = [
        'project_id',
        'mitra_name',
        'lop_name',
        'designator',
        'qty_gudang',
        'qty_ta',
        'qty_mitra',
    ];

    protected $casts = [
        'qty_gudang' => 'decimal:2',
        'qty_ta' => 'decimal:2',
        'qty_mitra' => 'decimal:2',
    ];
}
