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
        //acount no
        //
        Schema::create('transaction_histories', function (Blueprint $table) {
            $table->id();
            $table->foreign('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('account_no')->nullable();
            $table->string('account_title')->nullable();
            $table->integer('amount')->nullable();
            $table->string('bank_type')->nullable();
            $table->text('image')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_histories');
    }
};
