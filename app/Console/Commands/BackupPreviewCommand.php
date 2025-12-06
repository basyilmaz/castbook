<?php

namespace App\Console\Commands;

use App\Services\BackupEncryptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class BackupPreviewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:preview
        {file : Ã–n izlenecek yedek dosyasÄ± yolu}
        {--password= : Åifreli yedekler iÃ§in parola}
        {--format=table : Ã‡Ä±ktÄ± formatÄ± (table, json)}
        {--json : Ã‡Ä±ktÄ±yÄ± JSON formatÄ±nda yazdÄ±rÄ±r (eski seÃ§enek)}
        {--log= : Ã‡Ä±ktÄ±yÄ± belirtilen dosyaya da yaz}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Yedek dosyasÄ±nÄ± ÅŸifre Ã§Ã¶zerek analiz eder ve kayÄ±t sayÄ±larÄ±nÄ± listeler.';

    public function __construct(private readonly BackupEncryptionService $encryptionService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->argument('file');
        $password = $this->option('password');

        if (! file_exists($path)) {
            $this->error("Dosya bulunamadÄ±: {$path}");

            return SymfonyCommand::FAILURE;
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            $this->error('Dosya okunamadÄ±.');

            return SymfonyCommand::FAILURE;
        }

        $decoded = json_decode($contents, true);

        if (! is_array($decoded)) {
            $this->error('JSON formatÄ± geÃ§ersiz.');

            return SymfonyCommand::FAILURE;
        }

        if (($decoded['meta']['encrypted'] ?? false) === true) {
            if (empty($password)) {
                $this->error('Åifreli yedek iÃ§in --password seÃ§eneÄŸini kullanmalÄ±sÄ±nÄ±z.');

                return SymfonyCommand::FAILURE;
            }

            try {
                $decoded = $this->encryptionService->decrypt($decoded, $password);
            } catch (\Throwable $exception) {
                $this->error('Åifre Ã§Ã¶zme baÅŸarÄ±sÄ±z: ' . $exception->getMessage());

                return SymfonyCommand::FAILURE;
            }
        }

        if (! isset($decoded['data']) || ! is_array($decoded['data'])) {
            $this->error('Yedek yapÄ±sÄ± beklenen formatta deÄŸil.');

            return SymfonyCommand::FAILURE;
        }

        $counts = collect($decoded['data'])->map(fn ($items) => is_array($items) ? count($items) : 0);
        $payload = [
            'meta' => $decoded['meta'] ?? [],
            'counts' => $counts->toArray(),
        ];

        $format = strtolower((string) ($this->option('format') ?: 'table'));

        if ($this->option('json')) {
            $format = 'json';
        }

        if (! in_array($format, ['table', 'json'], true)) {
            $this->error('GeÃ§ersiz format. table veya json kullanÄ±n.');

            return SymfonyCommand::FAILURE;
        }

        $logPath = $this->option('log');

        if ($format === 'json') {
            $output = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $this->line($output);
            $this->writeLog($logPath, $output . PHP_EOL);

            return SymfonyCommand::SUCCESS;
        }

        $this->table(
            ['Tablo', 'KayÄ±t SayÄ±sÄ±'],
            $counts->map(fn ($count, $table) => [ucfirst($table), $count])->all()
        );

        if ($logPath) {
            $tableLines = collect($counts)->map(fn ($count, $table) => sprintf('%s: %d', ucfirst($table), $count));
            $content = implode(PHP_EOL, $tableLines->all()) . PHP_EOL;
            $this->writeLog($logPath, $content);
            $this->info("Ã‡Ä±ktÄ± {$logPath} dosyasÄ±na yazÄ±ldÄ±.");
        }

        $this->info('Ã–n izleme tamamlandÄ±.');

        return SymfonyCommand::SUCCESS;
    }

    private function writeLog(?string $path, string $contents): void
    {
        if (empty($path)) {
            return;
        }

        try {
            file_put_contents($path, $contents, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $exception) {
            $this->warn('Log dosyasÄ±na yazÄ±lamadÄ±: ' . $exception->getMessage());
        }
    }
}
