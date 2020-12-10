<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolApi extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'client_id',
        'client_secret',
    ];
}
