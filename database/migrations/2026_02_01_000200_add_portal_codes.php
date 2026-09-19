<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Codici usati dal portale del fornitore.
 *
 * Non coincidono con i codici interni: nel file «Assegnazione per portale»
 * il punto vendita è un numero cliente (566518) e l'articolo un numero
 * prodotto (497109), diversi da PV001 e ART10001.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('portal_code', 20)->nullable()->after('code')->index();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('portal_code', 20)->nullable()->after('article_code')->index();
        });

        Schema::table('opportunities', function (Blueprint $table) {
            // Snapshot come gli altri dati articolo: l'export storico non cambia
            // se l'anagrafica viene aggiornata.
            $table->string('portal_product_code', 20)->nullable()->after('article_code');
        });
    }

    public function down(): void
    {
        Schema::table('stores', fn (Blueprint $table) => $table->dropColumn('portal_code'));
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('portal_code'));
        Schema::table('opportunities', fn (Blueprint $table) => $table->dropColumn('portal_product_code'));
    }
};
