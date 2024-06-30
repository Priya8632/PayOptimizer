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
        Schema::create('payment_customizations', function (Blueprint $table) {
            $table->id();
            $table->string('payment_cust_id');
            $table->unsignedBigInteger('customization_id');
            $table->string('title');
            $table->string('status')->default('disabled');
            $table->json('condition_fields')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->foreign('customization_id')->references('id')->on('customizations');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_customizations');
    }
};
