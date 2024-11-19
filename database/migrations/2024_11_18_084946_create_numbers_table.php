<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('numbers', function (Blueprint $table) {
            $table->id();
            $table->string('issued')->unique();
            $table->string('originalOperator')->nullable();
            $table->string('originalOperatorRaw')->nullable();
            $table->string('currentOperator')->nullable();
            $table->string('currentOperatorRaw')->nullable();
            $table->string('number')->nullable();
            $table->string('prefix')->nullable();
            $table->string('type')->nullable();
            $table->string('typeDescription')->nullable();
            $table->integer('queriesLeft')->nullable();
            $table->string('lastPortability')->nullable();
            $table->string('lastPortabilityWhen')->nullable();
            $table->string('lastPortabilityFrom')->nullable();
            $table->string('lastPortabilityFromRaw')->nullable();
            $table->string('lastPortabilityTo')->nullable();
            $table->string('lastPortabilityToRaw')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('numbers');
    }
};
