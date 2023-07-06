<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamresultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('examresult', function (Blueprint $table) {
            $table->id();
            $table->string('StudentCode');
            $table->string('CandidateNumber');  //số báo danh
            $table->string('Path');   //đường dẫn đến bài thi
            $table->unsignedBigInteger('ExamShiftDetailId');
            $table->foreign('ExamShiftDetailId')->references('ExamShiftDetailId')->on('ExamShiftDetail')->onDelete('cascade');
            $table->string('StudentName');
            $table->string('DepartmentName');
            $table->string('TotalScore');
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
        Schema::dropIfExists('examresult');
    }
}
