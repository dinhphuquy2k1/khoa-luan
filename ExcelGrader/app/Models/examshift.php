<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class examshift extends Model
{
    use HasFactory;
    public $table = "examshift";
    protected $primaryKey = 'ExamShiftId';
    protected $guarded = [];
}
