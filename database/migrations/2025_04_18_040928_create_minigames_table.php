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
        Schema::create('minigames', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->double('default_timer')->default(0);
            $table->integer('default_points')->default(0);
            $table->dateTime('starts_at');

            $table->unsignedBigInteger('account_id'); // Who made it
            $table->foreign('account_id')
                  ->references('id')
                  ->on('accounts')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->dateTime('deleted_at')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('minigames');
    }
};
