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
    Schema::table('bank_payments', function (Blueprint $table) {
        $table->timestamp('received_at')->nullable()->after('raw_text');
    });
}

public function down(): void
{
    Schema::table('bank_payments', function (Blueprint $table) {
        $table->dropColumn('received_at');
    });
}

};
