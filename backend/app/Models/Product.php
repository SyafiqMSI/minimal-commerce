<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'image',
        'quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    protected $appends = ['image_url'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            $url = Storage::disk('public')->url($this->image);
            $url = preg_replace('#([^:])//+#', '$1/', $url);
            
            return $url;
        }
        return null;
    }

    public function isInStock(): bool
    {
        return $this->quantity > 0;
    }

    public function hasStock(int $quantity): bool
    {
        return $this->quantity >= $quantity;
    }

    public function reduceStock(int $quantity): bool
    {
        if ($this->hasStock($quantity)) {
            $this->decrement('quantity', $quantity);
            return true;
        }
        return false;
    }

    public function restoreStock(int $quantity): void
    {
        $this->increment('quantity', $quantity);
    }
}
