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
        Schema::create('bills', function (Blueprint $table) {
            $table->id();

            // রিলেশনশিপ (কোন দোকান, কোন কাস্টমার এবং কোন ডিভাইসের বিল)
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('device_id')->constrained('devices')->cascadeOnDelete();

            // বিলিংয়ের হিসাব-নিকাশ
            $table->decimal('total_amount', 10, 2); // মোট বিল
            $table->decimal('discount', 10, 2)->default(0); // ডিসকাউন্ট
            $table->decimal('paid_amount', 10, 2)->default(0); // কাস্টমার কত টাকা দিল
            $table->decimal('due_amount', 10, 2)->default(0); // বকেয়া কত থাকল

            // পেমেন্ট স্ট্যাটাস
            $table->enum('payment_status', ['paid', 'due', 'partial'])->default('due');

            $table->text('note')->nullable(); // কোনো নোট থাকলে

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
