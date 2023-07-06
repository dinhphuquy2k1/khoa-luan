<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamshiftsTable extends Migration
{
    //Ca thi
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('examshift', function (Blueprint $table) {
            $table->id('ExamShiftId');
            $table->string('ExamShiftCode');
            $table->string('ExamShiftName');
            $table->datetime('DateTimeStart');
            $table->datetime('DateTimeEnd');
            $table->unsignedBigInteger('ExamId');
            $table->foreign('ExamId')->references('ExamId')->on('exam')->onDelete('cascade');
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
        Schema::dropIfExists('examshift');
    }
}
