<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekonProject extends Model
{
    protected $table = 'rekon_projects';

    protected $fillable = [
        'project_name',
    ];

}
