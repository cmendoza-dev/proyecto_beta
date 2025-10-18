<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->onDelete('cascade');
            $table->foreignId('participant_id')->constrained()->onDelete('cascade');
            $table->timestamp('attended_at')->nullable();
            $table->enum('status', ['present', 'absent', 'late'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['meeting_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
