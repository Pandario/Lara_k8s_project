<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    protected $table = 'api_logs';
    protected $fillable = ['level', 'message', 'context'];
    public $timestamps = false;
}