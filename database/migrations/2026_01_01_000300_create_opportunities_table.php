<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();

            // Anagrafica di origine: se il prodotto viene rimosso lo storico resta leggibile
            // grazie allo snapshot dei campi sottostanti.
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            // --- snapshot anagrafica articolo ---------------------------------
            $table->string('article_code', 40)->index();
            $table->string('plu', 20)->nullable();
            $table->string('description', 190);
            $table->text('long_description')->nullable();
            $table->string('category', 80)->nullable();
            $table->string('origin', 120)->nullable();
            $table->string('fao_zone', 40)->nullable();
            $table->string('production_method', 80)->nullable();
            $table->string('caliber', 60)->nullable();

            // --- contenuti ----------------------------------------------------
            $table->string('title', 160);
            $table->text('commercial_description')->nullable();
            $table->text('technical_notes')->nullable();
            $table->text('logistics_notes')->nullable();

            // --- confezionamento e prezzi -------------------------------------
            $table->string('order_unit', 20)->default('COLLO');
            $table->decimal('kg_per_package', 10, 3);
            $table->string('price_unit', 20)->default('EUR/KG');
            $table->decimal('purchase_price', 10, 4);
            $table->decimal('sale_price_gross', 10, 4);
            $table->decimal('vat_rate', 5, 2);
            $table->decimal('markup_percent', 8, 2)->nullable();   // ricarico sul costo
            $table->decimal('margin_percent', 8, 2)->nullable();   // margine commerciale
            $table->boolean('pricing_overridden')->default(false);
            $table->text('pricing_override_reason')->nullable();
            $table->foreignId('pricing_overridden_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedInteger('min_lot')->default(1);
            $table->unsignedInteger('order_multiple')->default(1);
            $table->json('quick_quantities')->nullable();

            // --- disponibilità -------------------------------------------------
            $table->enum('availability_type', ['APERTA', 'LIMITATA'])->default('APERTA');
            $table->unsignedInteger('total_packages')->nullable();      // solo se LIMITATA
            $table->unsignedInteger('committed_packages')->default(0);  // impegnato dalle risposte inviate

            // --- finestra ordini ------------------------------------------------
            $table->dateTime('opens_at');
            $table->dateTime('closes_at');
            $table->date('delivery_date');

            // --- workflow --------------------------------------------------------
            $table->enum('status', [
                'BOZZA', 'IN_VERIFICA', 'DA_CORREGGERE', 'PROGRAMMATA',
                'APERTA', 'SCADUTA', 'CHIUSA', 'ANNULLATA', 'ARCHIVIATA',
            ])->default('BOZZA');

            $table->boolean('requires_refusal_reason')->default(false);
            $table->boolean('media_exception')->default(false);
            $table->text('media_exception_reason')->nullable();
            $table->text('review_notes')->nullable();       // ultima motivazione del Tecnico
            $table->text('close_reason')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'closes_at']);
            $table->index(['status', 'opens_at']);
            $table->index('delivery_date');
            $table->index('closes_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
