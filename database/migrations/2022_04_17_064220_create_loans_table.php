<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('amount');
            $table->integer('term')->comment('in weeks');
            $table->integer('repayment_frequency')->default(1)->comment('in weeks');
            $table->integer('status')->default(0)->comment('0::PENDING, 1::APPROVED, 2::PAID');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->timestamps();
        });
        Schema::table('loans', function (Blueprint $table) {
            $table->foreign('customer_id')->references('id')->on('users');
            $table->foreign('approver_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loans');
    }
}
