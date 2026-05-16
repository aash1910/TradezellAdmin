<?php

namespace App\Constants;

class ListingOptions
{
    public const CATEGORIES = [
        'Clothing',
        'Shoes',
        'Electronics',
        'Books',
        'Furniture',
        'Jewellery',
        'Sports',
        'Toys',
        'Art',
        'Other',
    ];

    public const CURRENCIES = [
        'USD' => 'USD — US Dollar',
        'EUR' => 'EUR — Euro',
        'GBP' => 'GBP — British Pound',
        'CAD' => 'CAD — Canadian Dollar',
        'AUD' => 'AUD — Australian Dollar',
    ];

    public static function categoriesForSelect(): array
    {
        return array_combine(self::CATEGORIES, self::CATEGORIES);
    }
}
