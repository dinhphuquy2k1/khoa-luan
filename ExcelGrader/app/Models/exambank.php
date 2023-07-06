<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class exambank extends Model
{
    use HasFactory;
    protected $primaryKey = 'ExamBankId';
    public $table = "exambank";
    protected $guarded = [];
}
