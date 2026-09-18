<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opportunity_id')->constrained('opportunities')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();

            $table->enum('status', [
                'NON_COMPILATA', 'BOZZA', 'INVIATA_ACQUISTO',
                'INVIATA_RIFIUTO', 'RIAPERTA', 'BLOCCATA',
            ])->default('NON_COMPILATA');

            $table->unsignedInteger('packages')->default(0);
            $table->decimal('kg', 12, 3)->default(0);
            // Quota di stock attualmente impegnata da questa risposta (solo disponibilità limitata).
            $table->unsignedInteger('committed_packages')->default(0);

            $table->text('refusal_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('last_actor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('reopened_until')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reopen_reason')->nullable();

            $table->timestamps();

            // Una sola risposta corrente per coppia opportunità/punto vendita.
            $table->unique(['opportunity_id', 'store_id']);
            $table->index(['store_id', 'status']);
            $table->index(['opportunity_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
