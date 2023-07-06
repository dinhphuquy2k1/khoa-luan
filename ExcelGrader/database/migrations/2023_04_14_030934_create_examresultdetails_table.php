<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamresultdetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('examresultdetail', function (Blueprint $table) {
            $table->id();
            $table->string('StudentCode')->nullable();
            $table->string('ExamBankName')->nullable();
            $table->string('DepartmentName')->nullable();
            $table->string('ExamShiftName')->nullable();
            $table->string('ExamShiftDetailId')->nullable();
            $table->string('CandidateNumber')->nullable();
            $table->string('PropertyName')->nullable();
            $table->string('Point')->nullable();
            $table->string('RealScore')->nullable();
            $table->boolean('Flag')->nullable();
            $table->string('Note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('examresultdetail');
    }
}
