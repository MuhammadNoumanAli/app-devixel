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
        // Shift definitions (Morning, Evening, Night US, etc.)
        Schema::create('hr_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Morning", "Evening", "Night US"
            $table->time('start_time');                       // 08:00, 16:00, 20:00
            $table->time('end_time');                         // 17:00, 01:00, 05:00
            $table->integer('grace_minutes')->default(15);   // Overridable per shift
            $table->boolean('is_night_shift')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Compensatory off / Working weekend exceptions
        Schema::create('hr_roster_exceptions', function (Blueprint $table) {
            $table->id();
            $table->date('exception_date');
            $table->enum('type', ['working_day', 'compensatory_off', 'holiday'])->default('holiday');
            $table->foreignId('shift_id')->nullable()->constrained('hr_shifts')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('NULL = applies to all employees');
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['exception_date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_roster_exceptions');
        Schema::dropIfExists('hr_shifts');
    }
};
