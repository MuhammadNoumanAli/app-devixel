<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->bigInteger('mc_number')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('load_number')->nullable();
            $table->string('pick_location')->nullable();
            $table->string('delivery_location')->nullable();
            $table->date('load_date')->nullable();
            $table->date('pick_date')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('truck_number')->nullable();
            $table->string('trailer_number')->nullable();
            $table->string('driver_number')->nullable();
            $table->integer('total_miles')->nullable();
            $table->integer('rate')->nullable();
            $table->string('percentage')->nullable();
            $table->integer('receivable')->nullable();
            $table->string('broker_company_name')->nullable();
            $table->string('broker_mc')->nullable();
            $table->string('broker_number')->nullable();
            $table->string('broker_email')->nullable();
            $table->string('broker_rep_name')->nullable()->comment('Broker Representative Name');
            $table->string('rate_confirmation')->nullable();
            $table->string('bol_pod')->nullable();
            $table->string('additional_doc')->nullable();
            $table->text('comment')->nullable();
            $table->boolean('notification_status')->default(false);
            $table->boolean('invoice_generate')->default(false);
            $table->string('invoice_status')->default('Pending')->nullable();
            $table->boolean('is_cancel')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatches');
    }
};
