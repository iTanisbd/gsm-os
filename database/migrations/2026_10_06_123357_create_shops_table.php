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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // দোকানের নাম
            $table->string('email')->nullable(); // দোকানের ইমেইল
            $table->string('phone')->nullable(); // দোকানের ফোন নম্বর
            $table->text('address')->nullable(); // দোকানের ঠিকানা
            $table->string('logo')->nullable(); // লোগোর পাথ
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active'); // সাবস্ক্রিপশন বা স্ট্যাটাস
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
