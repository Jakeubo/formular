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
    Schema::create('bank_payments', function (Blueprint $table) {
        $table->id(); // primární klíč
        $table->string('variable_symbol')->nullable(); // variabilní symbol (může být NULL)
        $table->decimal('amount', 12, 2)->nullable();  // částka
        $table->string('account_number')->nullable();  // číslo účtu protistrany
        $table->longText('raw_text')->nullable();      // celé tělo mailu (uložené pro ladění)
        $table->timestamps();                          // created_at a updated_at
    });
}


    /**
     * Reverse the migrations.
     */
public function down(): void
{
    Schema::dropIfExists('bank_payments');
}

};
