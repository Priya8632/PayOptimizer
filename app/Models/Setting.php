<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'value',
        'user_id',
    ];

    public const WEIGHT_UNIT = 'weight_unit';

    public const APP_STATUS = 'app_status';

    public function scopeAppStatus(Builder $query, int $user_id): void
    {
        $query->where('slug', Setting::APP_STATUS)->where('user_id', $user_id);
    }
}
