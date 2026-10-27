<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // ENUM এ নতুন 'returned' অপশনটি অ্যাড করা হলো
        DB::statement("ALTER TABLE devices MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'delivered', 'returned') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE devices MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'delivered') NOT NULL DEFAULT 'pending'");
    }
};
