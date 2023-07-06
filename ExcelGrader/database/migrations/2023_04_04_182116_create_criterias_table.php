<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCriteriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('criteria', function (Blueprint $table) {
            $table->id('CriteriaId');
            $table->unsignedBigInteger('ExamBankId');
            $table->foreign('ExamBankId')->references('ExamBankId')->on('exambank')->onDelete('cascade');
            $table->string('SheetId')->nullable();
            $table->string('Range')->nullable();
            $table->text('DataReference')->nullable();
            $table->longText('Content');
            $table->string('PropertyName');
            $table->integer('TypeProperty');
            $table->double('Point');
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
        Schema::dropIfExists('criteria');
    }
}
