<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Types\Nullable;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('teacher_id')->nullable()->default(null); // teacher account (sino teacher ng student)
            $table->foreign('teacher_id')
                  ->references('id')
                  ->on('accounts')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->string('email')->unique();
            $table->string('password');
            $table->string('first_name');
            $table->string('middle_name')->nullable()->default(null);
            $table->string('last_name');
            $table->string('user_role'); // admin, teacher, student
            $table->string('status')->default('active'); // active, inactive
            $table->dateTime('deleted_at')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
