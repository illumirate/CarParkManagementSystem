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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['user', 'admin', 'staff'])->default('user')->after('phone');
            $table->enum('user_type', ['student', 'staff'])->nullable()->after('role');
            $table->string('student_id')->nullable()->after('user_type');
            $table->string('staff_id')->nullable()->after('student_id');
            $table->string('faculty')->nullable()->after('staff_id');
            $table->string('department')->nullable()->after('faculty');
            $table->string('course')->nullable()->after('department');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('course');
            $table->decimal('credit_balance', 10, 2)->default(0.00)->after('status');
            $table->timestamp('last_login_at')->nullable()->after('credit_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'role',
                'user_type',
                'student_id',
                'staff_id',
                'faculty',
                'department',
                'course',
                'status',
                'credit_balance',
                'last_login_at',
            ]);
        });
    }
};
