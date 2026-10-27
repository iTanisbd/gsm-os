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
        // Customers টেবিলে অডিট কলাম যুক্ত করা
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('customers', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // Devices টেবিলে অডিট কলাম যুক্ত করা
        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('devices', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // bills টেবিলে অলরেডি created_by আছে, তাই এটা কমেন্ট করা হলো
        /*
        Schema::table('bills', function (Blueprint $table) {
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
        });
        */

        /*
          Note: bills টেবিলে created_by কলামটি এর মূল মাইগ্রেশন (create_bills_table)
          ফাইলে আগেই যুক্ত করা হয়েছে, তাই এখানে ডুপ্লিকেট এড়াতে এটি বাদ দেওয়া হলো।
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('customers', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
        });

        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasColumn('devices', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('devices', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
        });
    }
};
