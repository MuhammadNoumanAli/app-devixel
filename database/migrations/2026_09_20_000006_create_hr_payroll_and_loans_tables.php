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
        // Payroll cycles (monthly batches)
        Schema::create('hr_payroll_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('cycle_code')->unique();     // e.g. "2026-09"
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'approved', 'locked'])->default('draft');
            $table->decimal('total_gross', 12, 2)->default(0.00);
            $table->decimal('total_net', 12, 2)->default(0.00);
            $table->timestamps();
        });

        // Individual payslips
        Schema::create('hr_payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_cycle_id')->constrained('hr_payroll_cycles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Attendance summary
            $table->integer('eligible_work_days')->default(0);
            $table->decimal('present_days', 4, 1)->default(0);
            $table->decimal('paid_leave_days', 4, 1)->default(0);
            $table->decimal('unpaid_leave_days', 4, 1)->default(0);
            $table->integer('late_count')->default(0);

            // Earnings
            $table->decimal('base_salary_earned', 10, 2)->default(0.00);
            $table->decimal('sales_lead_bonus_earned', 10, 2)->default(0.00);
            $table->decimal('dispatcher_target_bonus_earned', 10, 2)->default(0.00);
            $table->decimal('dispatcher_load_commission_earned', 10, 2)->default(0.00);
            $table->decimal('total_earnings', 10, 2)->default(0.00);

            // Deductions
            $table->decimal('late_deductions_total', 10, 2)->default(0.00);
            $table->decimal('unpaid_leave_deductions_total', 10, 2)->default(0.00);
            $table->decimal('loan_deductions_total', 10, 2)->default(0.00);
            $table->decimal('total_deductions', 10, 2)->default(0.00);

            // Net
            $table->decimal('net_salary', 10, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->enum('payment_method', ['bank_transfer', 'cash', 'cheque'])->default('bank_transfer');
            $table->timestamps();

            $table->unique(['payroll_cycle_id', 'user_id']);
        });

        // Itemized payslip line items (for the Detailed Payslip view)
        Schema::create('hr_payslip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained('hr_payslips')->cascadeOnDelete();
            $table->enum('item_type', ['earning', 'deduction']);
            $table->string('code');           // BASE, SALES_LEAD_BONUS, DISPATCHER_TARGET_BONUS, DISPATCH_COMMISSION, LATE_PENALTY, LEAVE_DEDUCTION, LOAN_RECOVERY
            $table->string('description');    // Human readable: "Lead Bonus: MC#123456 Load #LD-102 ($450, 12 days)"
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->date('reference_date')->nullable()->comment('Specific date for late penalty or leave');
            $table->timestamps();

            $table->index(['payslip_id', 'item_type']);
        });

        // Salary advance loans
        Schema::create('hr_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('principal_amount', 10, 2);
            $table->decimal('monthly_installment', 10, 2);
            $table->decimal('remaining_balance', 10, 2);
            $table->string('purpose')->nullable();
            $table->enum('status', ['pending', 'approved', 'active', 'repaid', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // Loan installment tracking
        Schema::create('hr_loan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('hr_loans')->cascadeOnDelete();
            $table->foreignId('payslip_id')->nullable()->constrained('hr_payslips')->nullOnDelete();
            $table->integer('installment_number');
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->enum('status', ['pending', 'deducted', 'waived'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_loan_installments');
        Schema::dropIfExists('hr_loans');
        Schema::dropIfExists('hr_payslip_items');
        Schema::dropIfExists('hr_payslips');
        Schema::dropIfExists('hr_payroll_cycles');
    }
};
