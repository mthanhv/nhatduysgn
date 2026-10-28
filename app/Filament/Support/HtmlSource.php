<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\RichEditor\EditorCommand;
use Filament\Forms\Components\RichEditor\RichEditorTool;
use Filament\Forms\Components\Textarea;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

/**
 * "HTML" toolbar button for every rich editor: opens the editor's content as HTML source, and writes the edited
 * source back. The editor keeps only markup it supports (headings, lists, tables, links, images…); other tags are
 * dropped when the source is applied.
 */
class HtmlSource
{
    public const NAME = 'htmlSource';

    public static function apply(RichEditor $editor): RichEditor
    {
        return $editor
            ->tools([
                RichEditorTool::make(self::NAME)
                    ->label('Mã HTML')
                    ->icon(Heroicon::CodeBracket)
                    ->action(arguments: '{ html: $getEditor().getHTML() }'),
            ])
            ->registerActions([self::action()])
            // Default toolbars only; a custom toolbar (toolbarButtons([...groups])) lists self::NAME itself.
            ->enableToolbarButtons([self::NAME]);
    }

    private static function action(): Action
    {
        return Action::make(self::NAME)
            ->label('Mã HTML')
            ->modalHeading('Mã HTML')
            ->modalDescription('Sửa trực tiếp mã HTML của nội dung. Thẻ trình soạn thảo không hỗ trợ (script, style, iframe…) sẽ bị bỏ khi áp dụng.')
            ->modalWidth(Width::FiveExtraLarge)
            ->modalSubmitActionLabel('Áp dụng')
            ->fillForm(fn (array $arguments): array => ['html' => self::format((string) ($arguments['html'] ?? ''))])
            ->schema([
                Textarea::make('html')
                    ->hiddenLabel()
                    ->rows(22)
                    ->extraInputAttributes(['style' => 'font-family: ui-monospace, Menlo, monospace; font-size: 13px; line-height: 1.5', 'spellcheck' => 'false']),
            ])
            ->action(function (array $data, RichEditor $component): void {
                $component->runCommands([EditorCommand::make('setContent', arguments: [(string) ($data['html'] ?? ''), ['emitUpdate' => true]])]);
            });
    }

    /**
     * One block per line, so the source is readable; whitespace between block tags does not change the content.
     */
    private static function format(string $html): string
    {
        return trim(preg_replace('#(</(?:p|h[1-6]|ul|ol|li|table|thead|tbody|tr|blockquote|pre|figure|div)>|<br\s*/?>)(?!\n)#i', "$1\n", $html) ?? $html);
    }
}
