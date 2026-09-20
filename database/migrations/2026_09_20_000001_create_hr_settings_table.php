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
        Schema::create('hr_settings', function (Blueprint $table) {
            $table->id();

            // Attendance & Grace
            $table->integer('grace_period_minutes')->default(15);
            $table->decimal('late_deduction_percent', 5, 2)->default(25.00)->comment('Percentage of daily salary deducted when late');

            // Sales Agent Lead Bonus Configuration
            $table->decimal('qualifying_lead_load_min_amount', 10, 2)->default(200.00)->comment('Minimum load receivable to qualify for lead bonus');
            $table->integer('qualifying_lead_max_days')->default(30)->comment('Lead must convert to load within this many days');
            $table->decimal('sales_agent_lead_bonus_amount', 10, 2)->default(25.00)->comment('Flat bonus per qualifying lead');

            // Leave Policies
            $table->integer('yearly_paid_leaves_quota')->default(14)->comment('Total paid leaves per calendar year');
            $table->integer('max_paid_leaves_per_month')->default(2)->comment('Maximum paid leaves allowed per month');

            // General
            $table->json('weekend_days')->nullable()->comment('JSON array of weekend day names, e.g. ["Sunday"]');
            $table->enum('days_in_month_mode', ['30_days', 'actual_days', 'working_days'])->default('30_days');

            $table->timestamps();
        });

        // Seed default row
        DB::table('hr_settings')->insert([
            'grace_period_minutes' => 15,
            'late_deduction_percent' => 25.00,
            'qualifying_lead_load_min_amount' => 200.00,
            'qualifying_lead_max_days' => 30,
            'sales_agent_lead_bonus_amount' => 25.00,
            'yearly_paid_leaves_quota' => 14,
            'max_paid_leaves_per_month' => 2,
            'weekend_days' => json_encode(['Sunday']),
            'days_in_month_mode' => '30_days',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_settings');
    }
};
