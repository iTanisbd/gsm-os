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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete(); // কোন দোকানের কাস্টমার
            $table->string('name'); // কাস্টমারের নাম
            $table->string('phone'); // কাস্টমারের ফোন নম্বর
            $table->string('email')->nullable(); // ইমেইল (থাকলে)
            $table->text('address')->nullable(); // ঠিকানা
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
