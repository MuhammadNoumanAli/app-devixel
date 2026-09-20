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
        // 1. Add department to hr_employee_profiles
        if (Schema::hasTable('hr_employee_profiles') && !Schema::hasColumn('hr_employee_profiles', 'department')) {
            Schema::table('hr_employee_profiles', function (Blueprint $table) {
                $table->string('department')->nullable()->after('designation')->comment('e.g. Sales, Dispatch, Accounts, HR, Operations');
            });
        }

        // 2. Create user_login_logs table for location tracking & security audit
        if (!Schema::hasTable('user_login_logs')) {
            Schema::create('user_login_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->string('city')->nullable();
                $table->string('region')->nullable();
                $table->string('country')->nullable();
                $table->string('country_code', 10)->nullable();
                $table->string('postal_code', 20)->nullable();
                $table->string('latitude', 30)->nullable();
                $table->string('longitude', 30)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('device', 50)->nullable();    // Desktop, Mobile, Tablet
                $table->string('browser', 50)->nullable();   // Chrome, Edge, Safari
                $table->string('platform', 50)->nullable();  // Windows, Mac, Linux, Android
                $table->dateTime('login_at');
                $table->enum('status', ['success', 'failed'])->default('success');
                $table->timestamps();

                $table->index(['user_id', 'login_at']);
                $table->index(['ip_address', 'login_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_login_logs');

        if (Schema::hasTable('hr_employee_profiles') && Schema::hasColumn('hr_employee_profiles', 'department')) {
            Schema::table('hr_employee_profiles', function (Blueprint $table) {
                $table->dropColumn('department');
            });
        }
    }
};
