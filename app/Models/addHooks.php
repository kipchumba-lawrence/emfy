<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class addHooks extends Model
{
    use HasFactory;
    protected $fillable = ['dealname', 'responsible', 'timecreated'];
}
