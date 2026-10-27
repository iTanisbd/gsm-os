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
        Schema::table('devices', function (Blueprint $table) {
            // ইউজারের আইডির বদলে সরাসরি নাম সেভ করার জন্য string ব্যবহার করা হলো
            $table->string('delivered_by_name')->nullable()->after('status');
            $table->string('received_by_name')->nullable()->after('delivered_by_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            //
        });
    }
};
