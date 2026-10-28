<?php

namespace App\Filament\Support;

use App\Services\RichContent;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor\Actions\CustomBlockAction;
use Filament\Forms\Components\RichEditor\RichContentCustomBlock;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;

/**
 * "Video YouTube" block for rich editors, inserted with its own toolbar button: editors paste a YouTube link; the
 * site shows the player (see RichContent::render), the editor shows the video's thumbnail. A YouTube link pasted
 * alone on a line also becomes a player on the site, without this block.
 */
class YoutubeBlock extends RichContentCustomBlock
{
    public static function getId(): string
    {
        return RichContent::YOUTUBE_BLOCK;
    }

    public static function getLabel(): string
    {
        return 'Video YouTube';
    }

    public static function getIcon(): Heroicon
    {
        return Heroicon::OutlinedPlayCircle;
    }

    /**
     * Toolbar button that opens this block's form directly (Filament's "custom blocks" panel needs an extra step).
     */
    public static function tool(): RichEditorTool
    {
        return RichEditorTool::make(self::getId())
            ->label('Video YouTube')
            ->icon(Heroicon::OutlinedPlayCircle)
            ->action(CustomBlockAction::NAME, "{ id: '".self::getId()."', mode: 'insert' }");
    }

    public static function configureEditorAction(Action $action): Action
    {
        return $action
            ->modalHeading('Chèn video YouTube')
            ->modalDescription('Dán link video YouTube (youtube.com/watch?v=…, youtu.be/…, YouTube Shorts). Video hiện ngay trong nội dung, co giãn theo màn hình.')
            ->schema([
                TextInput::make('url')
                    ->label('Link video')
                    ->required()
                    ->url()
                    ->rule(fn () => function (string $attribute, mixed $value, \Closure $fail): void {
                        if (RichContent::youtubeId((string) $value) === null) {
                            $fail('Link không phải video YouTube.');
                        }
                    }),
                TextInput::make('title')->label('Tiêu đề video (tuỳ chọn)')->helperText('Dùng cho người dùng trình đọc màn hình và Google.'),
            ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function getPreviewLabel(array $config): string
    {
        return 'Video YouTube'.(filled($config['title'] ?? null) ? ': '.$config['title'] : '');
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public static function toPreviewHtml(array $config): ?string
    {
        $id = RichContent::youtubeId((string) ($config['url'] ?? ''));

        return $id === null ? null : '<img src="https://i.ytimg.com/vi/'.e($id).'/hqdefault.jpg" alt="" style="width:100%;max-width:480px;aspect-ratio:16/9;object-fit:cover;border-radius:8px">';
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $data
     */
    public static function toHtml(array $config, array $data): ?string
    {
        return RichContent::youtubeEmbed((string) ($config['url'] ?? ''), $config['title'] ?? null);
    }
}
