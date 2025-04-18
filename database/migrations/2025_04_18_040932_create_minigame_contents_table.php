<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('minigame_contents', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('minigame_id');
            $table->foreign('minigame_id')
                  ->references('id')
                  ->on('minigames')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

                  $table->string('question');
                  $table->integer('correct_answer'); // 1, 2, 3, or 4
                  $table->string('option_1');
                  $table->string('option_2');
                  $table->string('option_3');
                  $table->string('option_4');

            $table->integer('points')->default(0);
            $table->double('timer')->default(0);

            $table->unsignedBigInteger('account_id'); // Who made it
            $table->foreign('account_id')
                  ->references('id')
                  ->on('accounts')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('minigame_contents');
    }
};
