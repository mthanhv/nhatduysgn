<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'excerpt', 'content', 'cover', 'published_at', 'is_published', 'meta_title', 'meta_description', 'legacy_id', 'translations'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasTranslations, HasUniqueSlug;

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    /**
     * Fields editors can fill in per language; the first one decides whether a record counts as translated.
     *
     * @return array<int, string>
     */
    public function translatable(): array
    {
        return ['title', 'excerpt', 'content', 'meta_title', 'meta_description'];
    }

    /**
     * Estimated reading time in minutes (about 200 words per minute, at least 1).
     *
     * @return Attribute<int, never>
     */
    protected function readingMinutes(): Attribute
    {
        return Attribute::get(fn (): int => max(1, (int) ceil(str_word_count(strip_tags((string) $this->content), 0, 'àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴÈÉẸẺẼÊỀẾỆỂỄÌÍỊỈĨÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠÙÚỤỦŨƯỪỨỰỬỮỲÝỴỶỸĐ') / 200)));
    }

    /**
     * False when the excerpt is just the opening of the content (auto-generated on import), so it is not shown twice.
     *
     * @return Attribute<bool, never>
     */
    protected function hasDistinctExcerpt(): Attribute
    {
        return Attribute::get(function (): bool {
            $excerpt = Str::squish(rtrim((string) $this->excerpt, '.… '));

            if ($excerpt === '') {
                return false;
            }

            $opening = Str::squish(html_entity_decode(strip_tags((string) $this->content)));

            return ! Str::startsWith($opening, mb_substr($excerpt, 0, 80));
        });
    }

    public function slugSource(): string
    {
        return 'title';
    }

    /**
     * Published and not scheduled for the future, newest first.
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true)
            ->where(fn (Builder $query) => $query->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }
}
