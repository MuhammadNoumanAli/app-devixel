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
        Schema::create('hr_employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('employee_code')->nullable()->unique();    // e.g. EMP-1001
            $table->foreignId('shift_id')->nullable()->constrained('hr_shifts')->nullOnDelete();
            $table->string('designation')->nullable();                // e.g. Freight Dispatcher, Sales Agent
            $table->date('joining_date')->nullable();
            $table->decimal('base_salary', 10, 2)->default(0.00);    // Monthly gross
            $table->string('biometric_thumb_id')->nullable()->unique()->comment('Biometric device fingerprint ID');

            // Banking
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('iban')->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // Per-Dispatcher Appreciation Target (configured individually per dispatcher)
            $table->decimal('monthly_load_target_amount', 10, 2)->nullable()->comment('e.g. 2000.00 or 4000.00 threshold');
            $table->enum('target_bonus_type', ['fixed', 'percentage', 'both'])->default('fixed');
            $table->decimal('target_bonus_fixed', 10, 2)->default(0.00)->comment('Flat bonus amount e.g. $150');
            $table->decimal('target_bonus_percentage', 5, 2)->default(0.00)->comment('Percentage bonus e.g. 3.5%');

            $table->enum('status', ['active', 'probation', 'resigned', 'terminated'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_employee_profiles');
    }
};
