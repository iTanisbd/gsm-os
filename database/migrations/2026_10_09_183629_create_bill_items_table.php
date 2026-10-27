<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->onDelete('cascade'); // বিল ডিলিট হলে আইটেমও ডিলিট হবে
            $table->string('description'); // কাজের নাম (যেমন: FRP Lock, Display)
            $table->decimal('amount', 10, 2); // কাজের বিল
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bill_items');
    }
};
