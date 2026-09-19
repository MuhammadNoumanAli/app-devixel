<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('mc_number')->nullable()->index()->after('invoice_no');
            $table->foreignId('carrier_id')->nullable()->after('mc_number')->constrained('carriers')->nullOnDelete();
            $table->string('carrier_name')->nullable()->after('carrier_id');
            $table->decimal('total_amount', 12, 2)->default(0.00)->after('carrier_name');
            $table->decimal('paid_amount', 12, 2)->default(0.00)->after('total_amount');
            $table->decimal('due_amount', 12, 2)->default(0.00)->after('paid_amount');
            $table->string('status')->default('due')->index()->after('due_amount'); // due, partial, paid
            $table->date('invoice_date')->nullable()->after('status');
            $table->date('due_date')->nullable()->after('invoice_date');
            $table->text('notes')->nullable()->after('due_date');
            $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['carrier_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'mc_number',
                'carrier_id',
                'carrier_name',
                'total_amount',
                'paid_amount',
                'due_amount',
                'status',
                'invoice_date',
                'due_date',
                'notes',
                'created_by'
            ]);
        });
    }
};
