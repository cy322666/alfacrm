<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('amo_contact_id')->nullable()->unique();

            $table->text('amo_lead_notes')->nullable();
            $table->string('amo_lead_vk')->nullable();
            $table->string('amo_contact_phone')->nullable();
            $table->string('amo_contact_email')->nullable();
            $table->string('amo_lead_source')->nullable();
            $table->string('amo_lead_instagram')->nullable();
            $table->string('amo_contact_name')->nullable();
            $table->integer('amo_lead_id')->nullable()->unique();

            $table->string('amo_children_1_bd')->nullable();
            $table->string('amo_children_2_name')->nullable();
            $table->string('amo_children_1_name')->nullable();
            $table->string('amo_children_2_bd')->nullable();

            $table->integer('alfa_branch_id')->nullable();
            $table->integer('alfa_client_id')->nullable();
            $table->string('status')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads');
    }
};
