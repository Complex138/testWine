<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->unique()->after('email');
            $table->date('birth_date')->after('phone');
            $table->string('address')->nullable()->after('birth_date');
            $table->foreignId('favorite_wine_id')->nullable()->after('address')
                  ->constrained('wines')
                  ->nullOnDelete();
            $table->boolean('is_draft')->default(false)->after('favorite_wine_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['favorite_wine_id']);
            $table->dropColumn(['phone', 'birth_date', 'address', 'favorite_wine_id', 'is_draft']);
        });
    }
};