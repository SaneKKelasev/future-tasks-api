<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->dateTime('due_date');

            // Контроль значений — на уровне PHP-enum
            // В БД строки, чтобы расширять список без ALTER TABLE.
            $table->string('status', 20)->default('pending');
            $table->string('priority', 20)->default('medium');
            $table->string('category', 100)->nullable();

            $table->timestamps();

            $table->index('title');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};