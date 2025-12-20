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
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->foreignId('booking_id')->nullable()->after('assigned_to_user_id')->constrained('bookings')->nullOnDelete();
            $table->string('issue_type', 100)->nullable()->after('subject');

            $table->unique('booking_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropUnique(['booking_id']);
            $table->dropForeign(['booking_id']);
            $table->dropColumn(['booking_id', 'issue_type']);
        });
    }
};
