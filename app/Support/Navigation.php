<?php

namespace App\Support;

use App\Enums\OpportunityStatus;
use App\Models\Opportunity;
use App\Models\User;

/** Voci di menu per ruolo (capitolato §12). */
final class Navigation
{
    /** @return array<int, array{label:string, url:string, icon:string, active:bool, badge:int}> */
    public static function for(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return match (true) {
            // L'ordine conta: il Super Admin supera anche i controlli di Buyer e Tecnico.
            $user->isAdmin() => self::admin(),
            $user->isBuyer() => self::buyer(),
            $user->isTecnico() => self::tecnico(),
            default => self::capoReparto($user),
        };
    }

    /** Il Super Admin vede tutto, con la gestione profili in cima. */
    private static function admin(): array
    {
        $daVerificare = Opportunity::where('status', OpportunityStatus::IN_VERIFICA)->count();

        return [
            self::item('Utenti', 'tecnico.anagrafiche.utenti', '👤'),
            self::item('Punti vendita', 'tecnico.anagrafiche.punti-vendita', '🏬'),
            self::item('Prodotti', 'tecnico.anagrafiche.prodotti', '🐟'),
            self::item('Dashboard Tecnico', 'tecnico.dashboard', '▦'),
            self::item('Da verificare', 'opportunita.index', '⚑', ['preset' => 'da_verificare'], $daVerificare),
            self::item('Monitor compilazioni', 'tecnico.monitor', '▤'),
            self::item('Opportunità', 'opportunita.index', '≡'),
            self::item('Nuova opportunità', 'buyer.opportunita.create', '＋'),
            self::item('Ordini / Risposte', 'buyer.ordini', '✓'),
            self::item('Export', 'export.index', '⤓'),
            self::item('Audit', 'tecnico.audit', '🔒'),
            self::item('Stato sistema', 'tecnico.stato', '⚙'),
        ];
    }

    private static function buyer(): array
    {
        return [
            self::item('Dashboard', 'buyer.dashboard', '▦'),
            self::item('Opportunità', 'opportunita.index', '≡'),
            self::item('Nuova opportunità', 'buyer.opportunita.create', '＋'),
            self::item('Ordini / Risposte', 'buyer.ordini', '✓'),
            self::item('Export', 'export.index', '⤓'),
            self::item('Storico', 'storico', '🕘'),
        ];
    }

    private static function tecnico(): array
    {
        $daVerificare = Opportunity::where('status', OpportunityStatus::IN_VERIFICA)->count();

        return [
            self::item('Dashboard', 'tecnico.dashboard', '▦'),
            self::item('Da verificare', 'opportunita.index', '⚑', ['preset' => 'da_verificare'], $daVerificare),
            self::item('Monitor compilazioni', 'tecnico.monitor', '▤'),
            self::item('Opportunità', 'opportunita.index', '≡'),
            self::item('Anagrafiche', 'tecnico.anagrafiche.punti-vendita', '🗂'),
            self::item('Export', 'export.index', '⤓'),
            self::item('Audit', 'tecnico.audit', '🔒'),
        ];
    }

    private static function capoReparto(User $user): array
    {
        return [
            self::item('Da rispondere', 'cr.opportunita.index', '⚑', ['vista' => 'da_completare']),
            self::item('Bozze', 'cr.opportunita.index', '✎', ['vista' => 'bozze']),
            self::item('Inviate', 'cr.opportunita.index', '✓', ['vista' => 'inviate']),
            self::item('Storico', 'cr.opportunita.index', '🕘', ['vista' => 'storico']),
        ];
    }

    private static function item(string $label, string $route, string $icon, array $params = [], int $badge = 0): array
    {
        $url = route($route, $params);

        return [
            'label' => $label,
            'url' => $url,
            'icon' => $icon,
            'active' => request()->fullUrl() === $url,
            'badge' => $badge,
        ];
    }
}
