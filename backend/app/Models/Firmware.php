<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Firmware extends Model
{
    protected $fillable = [
        'filename',
        'filepath',
        'version',
        'description',
        'filesize',
        'uploaded_by',
    ];
}
