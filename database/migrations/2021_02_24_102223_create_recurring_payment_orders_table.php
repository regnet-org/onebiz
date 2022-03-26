<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecurringPaymentOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recurring_payment_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('clientid')->nullable()->unsigned()->index();
            $table->json('user_info')->nullable();
            $table->date('valid_to');
            $table->tinyInteger('installments')->unsigned()->default(0);
            $table->tinyInteger('has_child')->unsigned()->default(0);
            $table->float('amount', 10, 2)->nullable();
            $table->string('method', 50)->nullable();
            $table->tinyInteger('recurring')->unsigned()->default(0);
            $table->tinyInteger('initial_order')->unsigned()->default(0);
            $table->string('token_id', 200)->nullable();
            $table->dateTime('token_expiration_date')->nullable();
            $table->text('description')->nullable();
            $table->smallInteger('period')->nullable();
            $table->tinyInteger('status')->unsigned()->nullable();
            $table->string('payment_status', 250)->nullable();
            $table->string('payment_response', 250)->nullable();
            $table->integer('retry')->unsigned()->default(0);
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
        Schema::dropIfExists('recurring_payment_clients');
        Schema::dropIfExists('recurring_payment_orders');
    }
}
