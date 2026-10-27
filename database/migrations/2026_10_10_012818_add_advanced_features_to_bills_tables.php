<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // bills টেবিলে ডিসকাউন্ট টাইপ এবং টার্মস যুক্ত করা হলো
        Schema::table('bills', function (Blueprint $table) {
            $table->string('discount_type')->default('flat')->after('discount'); // flat বা percentage
            $table->text('terms')->nullable()->after('note');
        });

        // bill_items টেবিলে ক্যাটাগরি এবং ওয়ারেন্টি যুক্ত করা হলো
        Schema::table('bill_items', function (Blueprint $table) {
            $table->string('category')->default('hardware')->after('bill_id'); // hardware বা software
            $table->string('warranty')->nullable()->after('amount');
        });
    }

    public function down()
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'terms']);
        });

        Schema::table('bill_items', function (Blueprint $table) {
            $table->dropColumn(['category', 'warranty']);
        });
    }
};
