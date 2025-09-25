<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // unikátní číslo faktury
            $table->unsignedBigInteger('order_id')->nullable(); // odkaz na objednávku
            $table->unsignedBigInteger('customer_id')->nullable(); // odkaz na zákazníka
            $table->string('carrier')->nullable();
            $table->decimal('carrier_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('variable_symbol')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['new', 'sent', 'paid', 'overdue'])->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
