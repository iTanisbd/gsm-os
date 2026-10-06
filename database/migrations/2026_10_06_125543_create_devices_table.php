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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete(); // এটি কোন দোকানের কাজ
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete(); // এটি কোন কাস্টমারের ডিভাইস

            $table->string('brand'); // ব্র্যান্ড (যেমন: Samsung, Apple, Xiaomi)
            $table->string('model'); // মডেল (যেমন: iPhone 14, Galaxy S23)
            $table->string('imei')->nullable(); // ডিভাইসের IMEI বা সিরিয়াল নম্বর (থাকলে)
            $table->text('problem_description'); // ডিভাইসে কী সমস্যা বা কাস্টমার কী সার্ভিস চাচ্ছেন
            $table->enum('status', ['pending', 'processing', 'completed', 'delivered'])->default('pending'); // কাজের বর্তমান অবস্থা

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
