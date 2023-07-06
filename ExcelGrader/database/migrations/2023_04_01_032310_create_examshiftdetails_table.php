<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamshiftdetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('examshiftdetail', function (Blueprint $table) {
            $table->id('ExamShiftDetailId');
            $table->unsignedBigInteger('ExamShiftId');
            $table->foreign('ExamShiftId')->references('ExamShiftId')->on('examshift')->onDelete('cascade');
            $table->unsignedBigInteger('DepartmentId');
            $table->foreign('DepartmentId')->references('DepartmentId')->on('department')->onDelete('cascade');
            $table->unsignedBigInteger('ExamBankId');
            $table->foreign('ExamBankId')->references('ExamBankId')->on('exambank')->onDelete('cascade');
            $table->string('ResourcePath')->nullable();
            $table->string('ResourcePathFileList')->nullable();
            $table->string('ResourcePathFileAssignment')->nullable();
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
        Schema::dropIfExists('examshiftdetail');
    }
}
