<?php

namespace App\Console\Commands;

use App\Services\LegacyVerifier;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('legacy:verify
    {--urls= : Text file with real old URLs (one per line, e.g. extracted from the old server access log)}
    {--limit=20 : How many problems to print per check}')]
#[Description('Reconcile the old site with the new one: records, files, content links and old URLs. Exits non-zero if anything is missing.')]
class VerifyLegacyImport extends Command
{
    /**
     * Content intentionally not carried over, listed so the report is complete.
     *
     * @var array<string, string>
     */
    private const NOT_MIGRATED = [
        'users, groups, rules' => 'Tài khoản admin cũ (mật khẩu MD5 không an toàn) — tạo tài khoản mới bằng make:filament-user.',
        'slides' => 'Banner trình chiếu trang chủ cũ — thiết kế mới không dùng slider; các liên kết của slide vẫn được kiểm tra chuyển hướng.',
        'blocks' => 'Khối văn bản cũ (2 khối là nội dung mẫu của công ty khác, 1 khối là số điện thoại đã có trong Cài đặt).',
        'menus, menu_items' => 'Menu cũ — menu mới cố định theo thiết kế; các liên kết menu cũ vẫn được kiểm tra chuyển hướng.',
        'settings' => 'Tên/khẩu hiệu cũ — thay bằng Cài đặt website.',
    ];

    public function handle(LegacyVerifier $verifier): int
    {
        $urls = [];

        if ($file = $this->option('urls')) {
            if (! is_readable($file)) {
                $this->error("Cannot read {$file}");

                return self::FAILURE;
            }

            $urls = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        }

        $this->info('Đang đối chiếu website cũ với website mới…');
        $results = $verifier->run($urls);
        $limit = (int) $this->option('limit');
        $failed = 0;

        $this->table(['Hạng mục', 'Đã kiểm tra', 'Lỗi', ''], collect($results)->map(function (array $result, string $check) use (&$failed) {
            $count = count($result['problems']);
            $failed += $count > 0 ? 1 : 0;

            return [$check, $result['checked'], $count, $count === 0 ? '<fg=green>✔ đầy đủ</>' : '<fg=red>✘ cần xử lý</>'];
        })->values()->all());

        foreach ($results as $check => $result) {
            if ($result['problems'] !== []) {
                $this->newLine();
                $this->line("<fg=red;options=bold>{$check}</>");
                foreach (array_slice($result['problems'], 0, $limit) as $problem) {
                    $this->line("  • {$problem}");
                }
                if (count($result['problems']) > $limit) {
                    $this->line('  … và '.(count($result['problems']) - $limit).' lỗi khác (--limit để xem thêm).');
                }
            }

            foreach ($result['notes'] as $note) {
                $this->line("<fg=gray>  ℹ {$check}: {$note}</>");
            }
        }

        $this->newLine();
        $this->line('<options=bold>Không chuyển có chủ đích:</>');
        foreach (self::NOT_MIGRATED as $table => $reason) {
            $this->line("  - {$table}: {$reason}");
        }

        $this->newLine();

        if ($failed > 0) {
            $this->error("{$failed} hạng mục chưa đầy đủ.");

            return self::FAILURE;
        }

        $this->info('Toàn bộ nội dung website cũ đã có trên website mới.');

        return self::SUCCESS;
    }
}
