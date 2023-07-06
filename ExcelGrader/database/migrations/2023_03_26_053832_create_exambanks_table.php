<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExambanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exambank', function (Blueprint $table) {
            $table->id('ExamBankId');
            $table->string('ExamBankCode')->unique();
            $table->string('ExamBankName');
            $table->text('SheetIndexReference');
            $table->text('DataReference')->nullable();
            $table->integer('RowReference')->nullable();
            $table->string('ResourceFile')->nullable();
            $table->string('ResourcePath')->nullable();
            $table->string('FileInfo')->nullable();
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
        Schema::dropIfExists('exambank');
    }
}
