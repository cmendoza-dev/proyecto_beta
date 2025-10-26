<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
            $table->string('dni')->nullable(false)->unique()->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('email')->nullable(false)->unique()->change();
            $table->string('organization')->nullable(false)->change();
            $table->string('position')->nullable(false)->change();
            $table->boolean('is_active')->default(true)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropUnique(['dni']);
            $table->dropUnique(['email']);
            // Revertir a nullable si era el caso (ajusta según tu esquema original)
            $table->string('first_name')->nullable()->change();
            $table->string('last_name')->nullable()->change();
            $table->string('dni')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->string('organization')->nullable()->change();
            $table->string('position')->nullable()->change();
            $table->boolean('is_active')->nullable()->change();
        });
    }
};
