<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class exam extends Model
{
    use HasFactory;
    public $table = "exam";
    protected $primaryKey = 'ExamId';
    protected $guarded = [];
}
