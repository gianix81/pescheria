<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'article_code', 'plu', 'description', 'long_description', 'category',
        'origin', 'fao_zone', 'production_method', 'caliber',
        'unit_of_measure', 'vat_rate', 'default_kg_per_package', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'vat_rate' => 'decimal:2',
            'default_kg_per_package' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Ricerca per codice articolo, PLU o descrizione (creazione rapida). */
    public function scopeSearch($query, ?string $term)
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('article_code', 'like', "%{$term}%")
                ->orWhere('plu', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }
}
