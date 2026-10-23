<?php

namespace App\Models;

use Database\Factories\CertificateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'image', 'file', 'is_published', 'sort_order'])]
class Certificate extends Model
{
    /** @use HasFactory<CertificateFactory> */
    use HasFactory, Publishable;

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }
}
