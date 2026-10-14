<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            


            $table->string('reference')->nullable()->unique();
            $table->string('method');

            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');
            $table->string('transaction_id')->nullable()->unique();
            $table->timestamp('paid_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
