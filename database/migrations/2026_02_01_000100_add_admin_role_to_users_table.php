<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La colonna era un enum con i tre ruoli iniziali. Diventa una stringa:
     * aggiungere un ruolo non richiede più una migrazione che riscriva il tipo,
     * e la validazione resta dove è già (enum PHP, cast del modello, Form Request).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('CAPO_REPARTO')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['BUYER', 'TECNICO', 'CAPO_REPARTO'])->change();
        });
    }
};
