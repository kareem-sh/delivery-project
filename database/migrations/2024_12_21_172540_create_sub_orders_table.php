<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sub_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Foreign key to orders
            $table->foreignId('store_id')->constrained()->onDelete('cascade'); // Foreign key to stores
            $table->decimal('sub_total', 10, 2)->default(0); // Sub-order total price
            $table->enum('order_status', ['cart', 'pending', 'preparing', 'on_the_way','delivered', 'canceled'])->default('cart');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('sub_orders');
    }
};
