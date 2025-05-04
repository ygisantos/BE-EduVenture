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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->string('status')->default('active'); // active, inactive

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
        Schema::dropIfExists('books');
    }
};
