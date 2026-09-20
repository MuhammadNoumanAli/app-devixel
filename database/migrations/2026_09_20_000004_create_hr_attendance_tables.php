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
        // Raw biometric thumb punches
        Schema::create('hr_attendance_punches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('biometric_thumb_id')->nullable()->comment('Device-side fingerprint user ID');
            $table->dateTime('punch_time');
            $table->enum('source', ['thumb_device', 'web_kiosk', 'manual'])->default('thumb_device');
            $table->string('dedup_hash')->unique()->comment('md5(user_id + punch_time + source) for deduplication');
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'punch_time']);
        });

        // Reconciled daily attendance record
        Schema::create('hr_daily_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('work_date');
            $table->foreignId('shift_id')->nullable()->constrained('hr_shifts')->nullOnDelete();
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->enum('status', [
                'present', 'late', 'half_day', 'absent',
                'holiday', 'on_leave', 'compensatory_off', 'rest_day'
            ])->default('absent');
            $table->decimal('daily_salary_rate', 10, 2)->default(0.00)->comment('base_salary / 30');
            $table->decimal('late_deduction_amount', 10, 2)->default(0.00)->comment('daily_salary_rate x deduction%');
            $table->boolean('is_regularized')->default(false);
            $table->foreignId('regularized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'work_date']);
            $table->index(['work_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_daily_attendance');
        Schema::dropIfExists('hr_attendance_punches');
    }
};
