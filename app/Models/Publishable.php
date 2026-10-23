<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared `published()` / `ordered()` query scopes for content shown on the public site.
 */
trait Publishable
{
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where($this->qualifyColumn('is_published'), true);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy($this->qualifyColumn('sort_order'))->orderBy($this->qualifyColumn('id'));
    }
}
