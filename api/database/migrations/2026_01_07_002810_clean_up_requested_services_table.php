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
        Schema::table('requested_services', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropColumn([
                'provider_id',
                'price',
                'provider_observations',
                'rating',
                'rating_comment'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requested_services', function (Blueprint $table) {
            $table->foreignId('provider_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('price', 10, 2)->nullable();
            $table->text('provider_observations')->nullable();
            $table->integer('rating')->nullable();
            $table->text('rating_comment')->nullable();
        });
    }
};
