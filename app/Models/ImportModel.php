<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportModel extends Model
{
    protected $guarded = [];

    protected $casts = [
        'rules' => 'array',
    ];
    //
}
