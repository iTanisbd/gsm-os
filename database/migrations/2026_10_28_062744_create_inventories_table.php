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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete(); // Multi-tenant support

            $table->string('name'); // পার্টসের নাম (যেমন: Samsung A52 Display)
            $table->string('category')->nullable(); // ক্যাটাগরি (Display, Battery, IC, Accessories)
            $table->string('brand')->nullable(); // ব্র্যান্ড (Samsung, Xiaomi, Apple)

            $table->decimal('purchase_price', 10, 2)->default(0); // কেনা দাম
            $table->decimal('selling_price', 10, 2)->default(0); // বিক্রয় মূল্য

            $table->integer('stock')->default(0); // বর্তমান স্টকের পরিমাণ
            $table->integer('low_stock_threshold')->default(5); // স্টক এর নিচে নামলে ড্যাশবোর্ডে অ্যালার্ট দেবে

            $table->string('sku')->nullable(); // বারকোড বা পার্টস স্ক্যান করার জন্য (Zero Click Search এর জন্য)
            $table->text('note')->nullable(); // অতিরিক্ত নোট

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
