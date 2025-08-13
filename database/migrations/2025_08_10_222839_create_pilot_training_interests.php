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
        Schema::create('pilot_training_interests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pilot_training_id');
            $table->string('key');
            $table->timestamps();
            $table->timestamp('deadline')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->boolean('expired')->default(false);

            $table->foreign('pilot_training_id')->references('id')->on('pilot_trainings')->onDeleted('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilot_training_interests');
    }
};
