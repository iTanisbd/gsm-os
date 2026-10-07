<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // রিসিভ করার সময় চেকলিস্ট (যেমন: Display Broken, Scratches)
            $table->json('pre_repair_checklist')->nullable();

            // কাস্টমারের অভিযোগের বাইরে আসল সমস্যা
            $table->text('actual_fault')->nullable();

            // ফিজিক্যাল লোকেশন ট্র্যাকিং
            $table->string('rack_number')->nullable();
            $table->string('drawer_number')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn(['pre_repair_checklist', 'actual_fault', 'rack_number', 'drawer_number']);
        });
    }
};
