<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Firmware extends Model
{
    protected $table = 'firmwares';

    protected $fillable = [
        'filename',
        'filepath',
        'filesize',
        'version',
        'description',
        'uploaded_by',
    ];
}
