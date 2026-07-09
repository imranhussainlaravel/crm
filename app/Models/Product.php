<?php

namespace App\Models;

use App\Enums\ProductInterest;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'type', 'material', 'size_options', 'moq', 'base_price'])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'type' => ProductInterest::class,
            'base_price' => 'decimal:2',
        ];
    }

    public function priceTiers(): HasMany
    {
        return $this->hasMany(ProductPriceTier::class)->orderBy('min_quantity');
    }

    public function unitPriceForQuantity(int $quantity): float
    {
        $tier = $this->priceTiers
            ->filter(fn (ProductPriceTier $tier) => $tier->min_quantity <= $quantity)
            ->sortByDesc('min_quantity')
            ->first();

        return (float) ($tier->unit_price ?? $this->base_price);
    }
}
