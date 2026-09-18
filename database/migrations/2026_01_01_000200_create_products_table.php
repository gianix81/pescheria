<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('article_code', 40)->unique();
            $table->string('plu', 20)->nullable()->index();
            $table->string('description', 190)->index();
            $table->text('long_description')->nullable();
            $table->string('category', 80)->nullable()->index();
            $table->string('origin', 120)->nullable();
            $table->string('fao_zone', 40)->nullable();
            $table->string('production_method', 80)->nullable();
            $table->string('caliber', 60)->nullable();
            $table->string('unit_of_measure', 10)->default('KG');
            $table->decimal('vat_rate', 5, 2)->default(10.00);
            $table->decimal('default_kg_per_package', 10, 3)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
