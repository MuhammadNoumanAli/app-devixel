<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carriers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('assign_to')->nullable()->comment('Assign to Dispatch Id');
            $table->bigInteger('mc_number')->nullable();
            $table->bigInteger('dot')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('number')->nullable();
            $table->string('company_name')->nullable();
            $table->integer('truck_type')->nullable();
            $table->integer('truck_size')->nullable();
            $table->integer('maximum_weight')->nullable();
            $table->integer('payment_type')->nullable();
            $table->string('percent_flat')->nullable();
            $table->string('charge_type')->nullable();
            $table->string('mc_letter')->nullable()->comment('MC Authority Letter');
            $table->string('w_form')->nullable()->comment('W-9 Form');
            $table->string('coi')->nullable()->comment('Certificate of Insurance');
            $table->string('noa')->nullable()->comment('Notice of Assignment');
            $table->string('void_cheque')->nullable()->comment('VOID Cheque');
            $table->string('extra_document')->nullable();
            $table->string('all_zones')->nullable();
            $table->string('z0')->nullable();
            $table->string('z1')->nullable();
            $table->string('z2')->nullable();
            $table->string('z3')->nullable();
            $table->string('z4')->nullable();
            $table->string('z5')->nullable();
            $table->string('z6')->nullable();
            $table->string('z7')->nullable();
            $table->string('z8')->nullable();
            $table->string('z9')->nullable();
            $table->string('street_address')->nullable();
            $table->string('city_name')->nullable();
            $table->string('state_name')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('rpm')->nullable()->comment('Rate Per Mile');
            $table->text('comment')->nullable();
            $table->boolean('notification_status')->default(false);
            $table->string('active_status')->default('active')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carriers');
    }
};
