<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('address_id')->constrained('addresses')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnUpdate()->cascadeOnDelete();
            $table->decimal('shipment_cost', 10, 2);
            $table->decimal('coupon_discount', 10, 2)->nullable();
            $table->decimal('total_price', 10, 2);
            $table->string('order_number')->unique();
            $table->text('notes')->nullable();
            $table->enum('payment_method', ['cash_on_delivery', 'visa', 'vodafone_cash'])->default('cash_on_delivery');
            $table->enum('status', ['Pending', 'Canceled', 'Returned', 'Awaiting Payment', 'Shipped', 'Delivered'])->default('Pending');
            $table->softDeletes();
        });

    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
