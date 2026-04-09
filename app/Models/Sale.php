<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'g_number',
        'date',
        'last_change_date',
        'supplier_article',
        'tech_size',
        'barcode',
        'total_price',
        'discount_percent',
        'is_supply',
        'is_realization',
        'promo_code_discount',
        'warehouse_name',
        'country_name',
        'oblast_okrug_name',
        'region_name',
        'income_id',
        'sale_id',
        'odid',
        'spp',
        'for_pay',
        'finished_price',
        'price_with_disc',
        'nm_id',
        'subject',
        'category',
        'brand',
        'is_storno',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'last_change_date' => 'date',
            'barcode' => 'integer',
            'total_price' => 'decimal:4',
            'discount_percent' => 'integer',
            'is_supply' => 'boolean',
            'is_realization' => 'boolean',
            'promo_code_discount' => 'decimal:4',
            'income_id' => 'integer',
            'spp' => 'decimal:2',
            'for_pay' => 'decimal:4',
            'finished_price' => 'decimal:4',
            'price_with_disc' => 'decimal:4',
            'nm_id' => 'integer',
            'is_storno' => 'boolean',
        ];
    }
}
