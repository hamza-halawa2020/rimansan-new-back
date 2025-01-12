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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('code')->unique(); // Unique coupon code
            $table->string('name'); // Coupon name
            $table->string('description')->nullable(); // Optional description
            $table->decimal('discount', 5, 2); // Discount percentage with up to 2 decimal places            
            $table->integer('max_uses'); // Maximum allowed uses
            $table->integer('uses_count')->default(0); // Track usage, default to 0
            $table->dateTime('start_date'); // Start date and time
            $table->dateTime('end_date'); // End date and time
            $table->boolean('is_active')->default(false);
            $table->foreignId('admin_id')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
