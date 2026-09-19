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
        // 1. Add assignment status columns to carriers table if they don't exist
        Schema::table('carriers', function (Blueprint $table) {
            if (!Schema::hasColumn('carriers', 'assignment_status')) {
                $table->string('assignment_status')->default('pending')->nullable()->after('assign_to');
            }
            if (!Schema::hasColumn('carriers', 'assigned_at')) {
                $table->timestamp('assigned_at')->nullable()->after('assignment_status');
            }
            if (!Schema::hasColumn('carriers', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('assigned_at');
            }
            if (!Schema::hasColumn('carriers', 'completed_by')) {
                $table->foreignId('completed_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            }
        });

        // 2. Create carrier_notes table for discussion trail and document sharing
        if (!Schema::hasTable('carrier_notes')) {
            Schema::create('carrier_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('carrier_id')->constrained('carriers')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status')->nullable();
                $table->text('message');
                $table->string('attachment')->nullable();
                $table->string('attachment_original_name')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrier_notes');

        Schema::table('carriers', function (Blueprint $table) {
            if (Schema::hasColumn('carriers', 'completed_by')) {
                $table->dropForeign(['completed_by']);
                $table->dropColumn('completed_by');
            }
            if (Schema::hasColumn('carriers', 'completed_at')) {
                $table->dropColumn('completed_at');
            }
            if (Schema::hasColumn('carriers', 'assigned_at')) {
                $table->dropColumn('assigned_at');
            }
            if (Schema::hasColumn('carriers', 'assignment_status')) {
                $table->dropColumn('assignment_status');
            }
        });
    }
};
