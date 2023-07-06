<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class criteria extends Model
{
    use HasFactory;
    public $table = "criteria";
    protected $primaryKey = 'CriteriaID';
    protected $guarded =[];
}
