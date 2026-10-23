<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'logo', 'website', 'is_published', 'sort_order', 'legacy_id'])]
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, Publishable;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }
}
