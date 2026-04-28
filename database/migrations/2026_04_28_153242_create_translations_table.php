<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('translation_key_id');
            $table->unsignedBigInteger('locale_id');
            $table->text('value');
            $table->boolean('is_reviewed')->default(false);
            $table->timestamps();

            $table->unique(['translation_key_id', 'locale_id'], 'translations_key_locale_unique');
            $table->index('locale_id');
            $table->foreign('translation_key_id')->references('id')->on('translation_keys')->onDelete('cascade');
            $table->foreign('locale_id')->references('id')->on('locales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('translations');
    }
}
