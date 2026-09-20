<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Public / Gazetted holidays calendar
        Schema::create('hr_public_holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // "Independence Day", "Eid ul Fitr", "Christmas"
            $table->date('holiday_date');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique('holiday_date');
        });

        // Leave type definitions
        Schema::create('hr_leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Casual, Sick, Annual, Unpaid
            $table->boolean('is_paid')->default(true);
            $table->integer('default_quota')->default(0)->comment('Default annual allocation');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default leave types
        DB::table('hr_leave_types')->insert([
            ['name' => 'Casual Leave', 'is_paid' => true, 'default_quota' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sick Leave', 'is_paid' => true, 'default_quota' => 8, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Annual Leave', 'is_paid' => true, 'default_quota' => 14, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Unpaid Leave', 'is_paid' => false, 'default_quota' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Per-user leave quota tracking (per year)
        Schema::create('hr_user_leave_quotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('hr_leave_types')->cascadeOnDelete();
            $table->integer('year');
            $table->decimal('allocated_days', 4, 1)->default(0);
            $table->decimal('used_days', 4, 1)->default(0);
            $table->decimal('remaining_days', 4, 1)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'leave_type_id', 'year']);
        });

        // Leave applications
        Schema::create('hr_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('hr_leave_types')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_count', 4, 1)->default(1.0);
            $table->boolean('is_paid')->default(true)->comment('Resolved based on quota & monthly limit');
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_leaves');
        Schema::dropIfExists('hr_user_leave_quotas');
        Schema::dropIfExists('hr_leave_types');
        Schema::dropIfExists('hr_public_holidays');
    }
};
