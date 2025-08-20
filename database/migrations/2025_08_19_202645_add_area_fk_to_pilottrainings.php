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
        Schema::table('pilot_trainings', function (Blueprint $table) {
            $table->unsignedInteger('area_id')->nullable();

            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
        });

        DB::table('areas')->insert([
            ['id' => 2, 'name' => 'Norway', 'contact' => 'example@example.com'],
            ['id' => 3, 'name' => 'Sweden', 'contact' => 'example@example.com'],
            ['id' => 4, 'name' => 'Finland', 'contact' => 'example@example.com'],
            ['id' => 5, 'name' => 'Denmark', 'contact' => 'example@example.com'],
            ['id' => 6, 'name' => 'Iceland', 'contact' => 'example@example.com'],

        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilot_trainings', function (Blueprint $table) {
            $table->dropColumn('area_id');
        });
    }
};
