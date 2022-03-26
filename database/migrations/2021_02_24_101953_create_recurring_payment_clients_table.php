<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecurringPaymentClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recurring_payment_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client_name', 100);
            $table->string('email', 120);
            $table->string('phone', 50)->nullable();
            $table->json('user_info')->nullable();
            $table->date('valid_to');
            $table->float('amount', 10, 2)->nullable();
            $table->float('total_paid', 10, 2)->nullable();
            $table->date('last_payment')->nullable();
            $table->text('description')->nullable();
            $table->smallInteger('period')->nullable();
            $table->tinyInteger('notified')->nullable()->default(1);
            $table->tinyInteger('status')->nullable()->default(1);
            $table->tinyInteger('accepted_by_client')->nullable()->default(0);
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
    }
}
