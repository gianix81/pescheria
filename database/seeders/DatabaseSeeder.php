<?php

namespace Database\Seeders;

use App\Enums\AvailabilityType;
use App\Enums\OpportunityStatus;
use App\Enums\ResponseStatus;
use App\Enums\Role;
use App\Models\Opportunity;
use App\Models\Product;
use App\Models\Response;
use App\Models\Store;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Support\PricingCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Dati dimostrativi per l'ambiente locale.
 * Le credenziali sono documentate solo nel README e impongono il cambio password
 * quando l'ambiente non è locale.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $forzaCambioPassword = ! app()->environment('local', 'testing');

        User::updateOrCreate(['email' => 'admin@pescheria.local'], [
            'first_name' => 'Super', 'last_name' => 'Admin',
            'phone' => null, 'role' => Role::ADMIN,
            'is_active' => true, 'must_change_password' => $forzaCambioPassword,
            'password' => Hash::make('Pescheria2026!'),
        ]);

        $buyer = User::updateOrCreate(['email' => 'buyer@pescheria.local'], [
            'first_name' => 'Marco', 'last_name' => 'Ferrari',
            'phone' => '+39 333 1112233', 'role' => Role::BUYER,
            'is_active' => true, 'must_change_password' => $forzaCambioPassword,
            'password' => Hash::make('Pescheria2026!'),
        ]);

        $tecnico = User::updateOrCreate(['email' => 'tecnico@pescheria.local'], [
            'first_name' => 'Giulia', 'last_name' => 'Bianchi',
            'phone' => '+39 333 4445566', 'role' => Role::TECNICO,
            'is_active' => true, 'must_change_password' => $forzaCambioPassword,
            'password' => Hash::make('Pescheria2026!'),
        ]);

        // ------------------------------------------------------------ punti vendita
        $datiStore = [
            ['PV001', 'Iper Mare Nord', 'Milano', 'MI', '566518'],
            ['PV002', 'Super Laguna Centro', 'Venezia', 'VE', '566519'],
            ['PV003', 'Iper Costa Est', 'Rimini', 'RN', '566520'],
            ['PV004', 'Super Porto Sud', 'Bari', 'BA', '566521'],
            ['PV005', 'Iper Scoglio Ovest', 'Genova', 'GE', '566522'],
        ];

        $stores = collect($datiStore)->map(fn ($d) => Store::updateOrCreate(['code' => $d[0]], [
            'portal_code' => $d[4],
            'name' => $d[1],
            'address' => 'Via del Mercato 1',
            'city' => $d[2],
            'province' => $d[3],
            'email' => strtolower($d[0]).'@pescheria.local',
            'is_active' => true,
        ]));

        // ------------------------------------------------------------ capi reparto
        $nomiCr = [
            ['Luca', 'Rossi'], ['Anna', 'Verdi'], ['Paolo', 'Esposito'],
            ['Sara', 'Greco'], ['Davide', 'Conti'],
        ];

        $stores->each(function (Store $store, int $i) use ($nomiCr, $forzaCambioPassword) {
            User::updateOrCreate(['email' => 'cr'.($i + 1).'@pescheria.local'], [
                'first_name' => $nomiCr[$i][0], 'last_name' => $nomiCr[$i][1],
                'phone' => '+39 340 00000'.($i + 1),
                'role' => Role::CAPO_REPARTO, 'store_id' => $store->id,
                'is_active' => true, 'must_change_password' => $forzaCambioPassword,
                'password' => Hash::make('Pescheria2026!'),
            ]);
        });

        // ------------------------------------------------------------ prodotti
        // L'ultimo valore è il codice prodotto del portale del fornitore.
        $datiProdotti = [
            ['ART10001', '2101', 'Orata allevamento 400/600', 'Pesce', 'Italia', 6.0, '497109'],
            ['ART10002', '2102', 'Branzino pescato 300/400', 'Pesce', 'Grecia', 5.0, '497110'],
            ['ART10003', '2103', 'Cozze di Scardovari', 'Molluschi', 'Italia', 10.0, '497111'],
            ['ART10004', '2104', 'Vongole veraci', 'Molluschi', 'Italia', 4.0, '497112'],
            ['ART10005', '2105', 'Gambero rosso di Mazara', 'Crostacei', 'Italia', 3.0, '497113'],
            ['ART10006', '2106', 'Polpo doppio strato', 'Molluschi', 'Spagna', 8.0, '497114'],
            ['ART10007', '2107', 'Salmone norvegese filetto', 'Pesce', 'Norvegia', 5.0, '497115'],
            ['ART10008', '2108', 'Tonno pinna gialla trancio', 'Pesce', 'Oceano Indiano', 4.0, '497116'],
            ['ART10009', '2109', 'Sogliola atlantica', 'Pesce', 'Francia', 5.0, '497117'],
            ['ART10010', '2110', 'Calamaro nazionale', 'Molluschi', 'Italia', 6.0, '497118'],
        ];

        $prodotti = collect($datiProdotti)->map(fn ($d) => Product::updateOrCreate(['article_code' => $d[0]], [
            'portal_code' => $d[6] ?? null,
            'plu' => $d[1],
            'description' => $d[2],
            'long_description' => $d[2].' — prodotto selezionato per il reparto pescheria.',
            'category' => $d[3],
            'origin' => $d[4],
            'fao_zone' => 'FAO 37.2.1',
            'production_method' => str_contains($d[2], 'allevamento') ? 'Allevamento' : 'Pescato in mare',
            'caliber' => '400/600 g',
            'unit_of_measure' => 'KG',
            'vat_rate' => 10.00,
            'default_kg_per_package' => $d[5],
            'is_active' => true,
        ]));

        if (Opportunity::exists()) {
            return;     // seeder idempotente: le opportunità demo si creano una sola volta
        }

        // ------------------------------------------------------------ opportunità nei vari stati
        $this->creaOpportunita($prodotti[0], $buyer, $stores, OpportunityStatus::APERTA, [
            'title' => 'Orata fresca — consegna giovedì',
            'availability_type' => AvailabilityType::LIMITATA,
            'total_packages' => 40,
            'opens_at' => now()->subHours(2),
            'closes_at' => now()->addHours(8),
        ]);

        $aperta2 = $this->creaOpportunita($prodotti[2], $buyer, $stores, OpportunityStatus::APERTA, [
            'title' => 'Cozze di Scardovari — promo weekend',
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addHours(20),
        ]);

        $this->creaOpportunita($prodotti[1], $buyer, $stores, OpportunityStatus::BOZZA, [
            'title' => 'Branzino pescato — bozza da completare',
        ]);

        $this->creaOpportunita($prodotti[4], $buyer, $stores, OpportunityStatus::IN_VERIFICA, [
            'title' => 'Gambero rosso di Mazara — da verificare',
            'submitted_at' => now()->subMinutes(30),
        ]);

        $this->creaOpportunita($prodotti[5], $buyer, $stores, OpportunityStatus::DA_CORREGGERE, [
            'title' => 'Polpo doppio strato — da correggere',
            'review_notes' => 'Manca il peso per collo corretto: verificare con il fornitore.',
            'reviewed_by' => $tecnico->id,
        ]);

        $this->creaOpportunita($prodotti[6], $buyer, $stores, OpportunityStatus::PROGRAMMATA, [
            'title' => 'Salmone filetto — apertura domani',
            'opens_at' => now()->addHours(14),
            'closes_at' => now()->addHours(30),
            'approved_at' => now(),
            'reviewed_by' => $tecnico->id,
        ]);

        $scaduta = $this->creaOpportunita($prodotti[3], $buyer, $stores, OpportunityStatus::SCADUTA, [
            'title' => 'Vongole veraci — chiusa ieri',
            'opens_at' => now()->subDays(2),
            'closes_at' => now()->subDay(),
            'delivery_date' => now()->toDateString(),
        ]);

        $this->creaOpportunita($prodotti[7], $buyer, $stores, OpportunityStatus::CHIUSA, [
            'title' => 'Tonno trancio — chiusa dal Buyer',
            'opens_at' => now()->subDays(4),
            'closes_at' => now()->subDays(3),
            'delivery_date' => now()->subDays(2)->toDateString(),
            'closed_at' => now()->subDays(3),
            'closed_by' => $buyer->id,
            'close_reason' => 'Quantitativo esaurito dal fornitore.',
        ]);

        $this->creaOpportunita($prodotti[8], $buyer, $stores, OpportunityStatus::ANNULLATA, [
            'title' => 'Sogliola atlantica — annullata',
            'cancelled_at' => now()->subDay(),
            'cancelled_by' => $buyer->id,
            'cancel_reason' => 'Prodotto non conforme al controllo qualità.',
        ]);

        // ------------------------------------------------------------ risposte demo
        $this->creaRisposte($aperta2, $stores, [3, 0, 5, null, 2]);
        $this->creaRisposte($scaduta, $stores, [4, 4, 0, null, 6]);
    }

    private function creaOpportunita(Product $prodotto, User $buyer, $stores, OpportunityStatus $stato, array $extra = []): Opportunity
    {
        $acquisto = round(fake()->randomFloat(2, 4, 16), 2);
        $iva = (float) $prodotto->vat_rate;
        $vendita = round($acquisto * 1.45 * (1 + $iva / 100), 2);
        $prezzi = PricingCalculator::all($acquisto, $vendita, $iva);

        $opportunita = Opportunity::create(array_merge([
            'reference' => Opportunity::nextReference(),
            'product_id' => $prodotto->id,
            'article_code' => $prodotto->article_code,
            'portal_product_code' => $prodotto->portal_code,
            'plu' => $prodotto->plu,
            'description' => $prodotto->description,
            'long_description' => $prodotto->long_description,
            'category' => $prodotto->category,
            'origin' => $prodotto->origin,
            'fao_zone' => $prodotto->fao_zone,
            'production_method' => $prodotto->production_method,
            'caliber' => $prodotto->caliber,
            'title' => $prodotto->description,
            'commercial_description' => 'Prodotto selezionato, pezzatura uniforme e confezionamento in cassa polistirolo.',
            'technical_notes' => 'Conservare a 0/+2 °C.',
            'logistics_notes' => 'Consegna con mezzo refrigerato entro le 6:00.',
            'order_unit' => 'COLLO',
            'kg_per_package' => $prodotto->default_kg_per_package,
            'price_unit' => 'EUR/KG',
            'purchase_price' => $acquisto,
            'sale_price_gross' => $vendita,
            'vat_rate' => $iva,
            'markup_percent' => $prezzi['markup'],
            'margin_percent' => $prezzi['margin'],
            'min_lot' => 1,
            'order_multiple' => 1,
            'quick_quantities' => [1, 2, 3, 4, 5, 6],
            'availability_type' => AvailabilityType::APERTA,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addHours(6),
            'delivery_date' => now()->addDays(2)->toDateString(),
            'status' => $stato,
            'created_by' => $buyer->id,
            'published_at' => $stato === OpportunityStatus::APERTA ? now()->subHour() : null,
        ], $extra));

        $opportunita->stores()->sync($stores->pluck('id')->all());

        if (in_array($stato, OpportunityStatus::visibleToStores(), true)) {
            foreach ($stores as $store) {
                $opportunita->responses()->firstOrCreate(['store_id' => $store->id], [
                    'status' => ResponseStatus::NON_COMPILATA, 'packages' => 0, 'kg' => 0,
                ]);
            }
        }

        return $opportunita;
    }

    /** @param array<int, int|null> $colliPerStore null = nessuna risposta, 0 = rifiuto esplicito */
    private function creaRisposte(Opportunity $opportunita, $stores, array $colliPerStore): void
    {
        foreach ($stores as $i => $store) {
            $colli = $colliPerStore[$i] ?? null;

            if ($colli === null) {
                continue;       // resta NON_COMPILATA: l'assenza non è uno zero
            }

            $risposta = Response::firstOrNew([
                'opportunity_id' => $opportunita->id,
                'store_id' => $store->id,
            ]);

            $risposta->fill([
                'status' => $colli > 0 ? ResponseStatus::INVIATA_ACQUISTO : ResponseStatus::INVIATA_RIFIUTO,
                'packages' => $colli,
                'kg' => round($colli * (float) $opportunita->kg_per_package, 3),
                'committed_packages' => $opportunita->isLimited() ? $colli : 0,
                'refusal_reason' => $colli === 0 ? 'Assortimento già coperto.' : null,
                'submitted_at' => now()->subMinutes(random_int(10, 300)),
                'last_actor_id' => User::where('store_id', $store->id)->value('id'),
            ])->save();
        }

        if ($opportunita->isLimited()) {
            app(AvailabilityService::class)->recomputeCommitted($opportunita);
        }
    }
}
