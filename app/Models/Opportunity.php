<?php

namespace App\Models;

use App\Enums\AvailabilityType;
use App\Enums\OpportunityStatus;
use App\Enums\ResponseStatus;
use App\Enums\Role;
use App\Support\PricingCalculator;
use Database\Factories\OpportunityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Opportunity extends Model
{
    /** @use HasFactory<OpportunityFactory> */
    use HasFactory;

    protected $fillable = [
        'reference', 'product_id',
        'article_code', 'plu', 'description', 'long_description', 'category',
        'origin', 'fao_zone', 'production_method', 'caliber',
        'title', 'commercial_description', 'technical_notes', 'logistics_notes',
        'order_unit', 'kg_per_package', 'price_unit', 'purchase_price', 'sale_price_gross',
        'vat_rate', 'markup_percent', 'margin_percent',
        'pricing_overridden', 'pricing_override_reason', 'pricing_overridden_by',
        'min_lot', 'order_multiple', 'quick_quantities',
        'availability_type', 'total_packages', 'committed_packages',
        'opens_at', 'closes_at', 'delivery_date', 'status',
        'requires_refusal_reason', 'media_exception', 'media_exception_reason',
        'review_notes', 'close_reason', 'cancel_reason',
        'created_by', 'reviewed_by', 'closed_by', 'cancelled_by',
        'submitted_at', 'approved_at', 'published_at', 'closed_at', 'cancelled_at', 'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OpportunityStatus::class,
            'availability_type' => AvailabilityType::class,
            'quick_quantities' => 'array',
            'kg_per_package' => 'decimal:3',
            'purchase_price' => 'decimal:4',
            'sale_price_gross' => 'decimal:4',
            'vat_rate' => 'decimal:2',
            'markup_percent' => 'decimal:2',
            'margin_percent' => 'decimal:2',
            'pricing_overridden' => 'boolean',
            'requires_refusal_reason' => 'boolean',
            'media_exception' => 'boolean',
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'delivery_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'published_at' => 'datetime',
            'closed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    // ------------------------------------------------------------------ relazioni

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(OpportunityMedia::class)->orderBy('sort_order');
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'opportunity_stores')->withTimestamps();
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(OpportunityReview::class)->latest();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ------------------------------------------------------------------ scope

    /** Restringe la query a ciò che l'utente ha diritto di vedere (anti-IDOR a livello di query). */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->role === Role::CAPO_REPARTO) {
            return $query
                ->whereIn('status', array_column(OpportunityStatus::visibleToStores(), 'value'))
                ->whereHas('stores', fn ($q) => $q->where('stores.id', $user->store_id));
        }

        return $query;
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', OpportunityStatus::APERTA);
    }

    public function scopeForStore(Builder $query, int $storeId): Builder
    {
        return $query->whereHas('stores', fn ($q) => $q->where('stores.id', $storeId));
    }

    // ------------------------------------------------------------------ stato

    /** Vero solo se lo stato è APERTA e la finestra temporale del server è valida. */
    public function isAcceptingResponses(?\DateTimeInterface $now = null): bool
    {
        $now = $now ? Carbon::instance($now) : now();

        return $this->status === OpportunityStatus::APERTA
            && $this->opens_at->lessThanOrEqualTo($now)
            && $this->closes_at->greaterThan($now);
    }

    public function isExpired(?\DateTimeInterface $now = null): bool
    {
        return $this->closes_at->lessThanOrEqualTo($now ? Carbon::instance($now) : now());
    }

    public function isLimited(): bool
    {
        return $this->availability_type === AvailabilityType::LIMITATA;
    }

    /** Colli ancora disponibili; null se la disponibilità è aperta. */
    public function remainingPackages(): ?int
    {
        if (! $this->isLimited()) {
            return null;
        }

        return max(0, (int) $this->total_packages - (int) $this->committed_packages);
    }

    public function isSoldOut(): bool
    {
        return $this->isLimited() && $this->remainingPackages() === 0;
    }

    public function quickQuantities(): array
    {
        return $this->quick_quantities ?: config('pescheria.quick_quantities');
    }

    // ------------------------------------------------------------------ prezzi

    public function netSalePrice(): float
    {
        return PricingCalculator::netSalePrice((float) $this->sale_price_gross, (float) $this->vat_rate);
    }

    /** Ricalcola ricarico e margine, salvo override autorizzato e tracciato. */
    public function recalculatePricing(): void
    {
        if ($this->pricing_overridden) {
            return;
        }

        $values = PricingCalculator::all(
            (float) $this->purchase_price,
            (float) $this->sale_price_gross,
            (float) $this->vat_rate,
        );

        $this->markup_percent = $values['markup'];
        $this->margin_percent = $values['margin'];
    }

    // ------------------------------------------------------------------ totali

    public function totalPackagesOrdered(): int
    {
        return (int) $this->responses()
            ->where('status', ResponseStatus::INVIATA_ACQUISTO)
            ->sum('packages');
    }

    public function totalKgOrdered(): float
    {
        return (float) $this->responses()
            ->where('status', ResponseStatus::INVIATA_ACQUISTO)
            ->sum('kg');
    }

    /** @return array{destinatari:int, inviate:int, acquisti:int, rifiuti:int, bozze:int, mancanti:int, percentuale:float} */
    public function completionStats(): array
    {
        $recipients = $this->stores()->count();
        $responses = $this->responses()->get();

        $purchases = $responses->where('status', ResponseStatus::INVIATA_ACQUISTO)->count();
        $refusals = $responses->where('status', ResponseStatus::INVIATA_RIFIUTO)->count();
        $drafts = $responses->where('status', ResponseStatus::BOZZA)->count();
        $submitted = $purchases + $refusals;

        return [
            'destinatari' => $recipients,
            'inviate' => $submitted,
            'acquisti' => $purchases,
            'rifiuti' => $refusals,
            'bozze' => $drafts,
            'mancanti' => max(0, $recipients - $submitted),
            'percentuale' => $recipients > 0 ? round($submitted / $recipients * 100, 1) : 0.0,
        ];
    }

    public function hasMedia(): bool
    {
        return $this->media()->exists();
    }

    /** Numero progressivo leggibile: OPP-2026-0001 */
    public static function nextReference(): string
    {
        $year = now(config('app.display_timezone'))->year;
        $prefix = "OPP-{$year}-";

        $last = static::where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->value('reference');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
