<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentCustomization extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'id',
        'payment_cust_id',
        'customization_id',
        'title',
        'status',
        'condition_fields',
        'user_id',
    ];

    public function customization(): BelongsTo
    {
        return $this->belongsTo(Customization::class, 'customization_id', 'id');
    }

    public function scopeActive(Builder $query, int $user_id): void
    {
        $query->where('status', '=', 'enabled')->where('user_id', $user_id);
    }

    public function scopeHide(Builder $query, int $user_id): void
    {
        $query->where('customization_id', '=', '1')->where('user_id', $user_id);
    }

    public function scopeRename(Builder $query, int $user_id): void
    {
        $query->where('customization_id', '=', '2')->where('user_id', $user_id);
    }

    public function scopeSort(Builder $query, int $user_id): void
    {
        $query->where('customization_id', '=', '3')->where('user_id', $user_id);
    }

    public function scopeHideEnable(Builder $query, int $user_id): void
    {
        $query->where('customization_id', '=', '1')->where('user_id', $user_id)->where('status', 'enabled');
    }

    public function scopeRenameEnable(Builder $query, int $user_id): void
    {
        $query->where('customization_id', '=', '2')->where('user_id', $user_id)->where('status', 'enabled');
    }

    public function scopeSortEnable(Builder $query, int $user_id): void
    {
        $query->where('customization_id', '=', '3')->where('user_id', $user_id)->where('status', 'enabled');
    }
}
