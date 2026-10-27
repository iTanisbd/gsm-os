<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // কে খরচ অ্যাড করেছে

            $table->string('category'); // যেমন: Parts, Rent, Salary, Snacks, Others
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('note')->nullable(); // খরচের বিবরণ

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
