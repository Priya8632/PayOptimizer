<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customization extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'filter',
    ];

    public const HIDE = 'Hide';

    public const RENAME = 'Rename';

    public const SORT = 'Sort';

    public function paymentCustomization(): HasMany
    {
        return $this->hasMany(PaymentCustomization::class, 'customization_id', 'id');
    }
}
