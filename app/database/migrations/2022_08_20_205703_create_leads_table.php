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
            $table->integer('amo_contact_phone')->nullable();
            $table->integer('amo_contact_email')->nullable();
            $table->string('amo_children_name')->nullable();
            $table->string('amo_children_bd')->nullable();
            $table->string('amo_lead_source')->nullable();
            $table->string('amo_lead_instagram')->nullable();
            $table->string('amo_lead_vk')->nullable();
            $table->text('amo_lead_notes')->nullable();
            $table->integer('amo_lead_id')->nullable()->unique();
            $table->string('amo_lead_name')->nullable();
            $table->string('amo_contact_name')->nullable();
            $table->integer('alfa_lead_id')->nullable();
            $table->integer('alfa_client_id')->nullable();
            $table->string('status')->default('recorded');
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
