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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('company_address')->nullable();
            $table->string('company_tax_number')->nullable();
            $table->string('fiscal_year_start')->default('01-01');
            $table->string('fiscal_year_end')->default('12-31');
            $table->string('currency_code')->default('USD');
            $table->string('decimal_separator')->default('.');
            $table->string('thousand_separator')->default(',');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
