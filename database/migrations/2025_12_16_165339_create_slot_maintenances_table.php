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
        Schema::create('slot_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slot_id')->constrained('parking_slots')->onDelete('cascade');
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->default(DB::raw('DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 YEAR)'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slot_maintenances');
    }
};
