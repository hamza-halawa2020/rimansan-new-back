<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM ('cash_on_delivery', 'visa', 'vodafone_cash','points') DEFAULT 'cash_on_delivery'");            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM ('cash_on_delivery', 'visa', 'vodafone_cash','points') DEFAULT 'cash_on_delivery'");                        
        });
    }
};
