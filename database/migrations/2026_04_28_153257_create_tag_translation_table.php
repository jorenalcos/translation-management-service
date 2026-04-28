<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagTranslationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_translation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tag_id');
            $table->unsignedBigInteger('translation_id');
            $table->timestamps();

            $table->unique(['tag_id', 'translation_id']);
            $table->index('translation_id');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->foreign('translation_id')->references('id')->on('translations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tag_translation');
    }
}
