<?php

namespace Botble\Hotel\Models;

use Botble\Base\Casts\SafeContent;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends BaseModel
{
    protected $table = 'ht_hotels';

    protected $fillable = [
        'name',
        'description',
        'content',
        'address',
        'phone',
        'email',
        'image',
        'status',
    ];

    protected $casts = [
        'status' => BaseStatusEnum::class,
        'name' => SafeContent::class,
        'description' => SafeContent::class,
        'content' => SafeContent::class,
        'address' => SafeContent::class,
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'hotel_id');
    }
}
