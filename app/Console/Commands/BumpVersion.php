<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BumpVersion extends Command
{
    protected $signature = 'app:bump-version {type=patch : Version type to bump (major, minor, patch)}';
    protected $description = 'Increment the application version number';

    public function handle(): int
    {
        $type = strtolower($this->argument('type'));
        
        if (!in_array($type, ['major', 'minor', 'patch'])) {
            $this->error('Invalid version type. Use: major, minor, or patch');
            return 1;
        }

        $configPath = config_path('app.php');
        $content = File::get($configPath);
        
        // Mevcut versiyonu bul
        if (!preg_match("/'version'\s*=>\s*'(\d+)\.(\d+)\.(\d+)'/", $content, $matches)) {
            $this->error('Could not find version in config/app.php');
            $this->info('Add this line to config/app.php:');
            $this->line("    'version' => '1.0.0',");
            return 1;
        }

        $major = (int) $matches[1];
        $minor = (int) $matches[2];
        $patch = (int) $matches[3];
        
        $oldVersion = "$major.$minor.$patch";

        // Versiyonu artır
        switch ($type) {
            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;
            case 'minor':
                $minor++;
                $patch = 0;
                break;
            case 'patch':
                $patch++;
                break;
        }

        $newVersion = "$major.$minor.$patch";

        // Config dosyasını güncelle
        $newContent = preg_replace(
            "/'version'\s*=>\s*'\d+\.\d+\.\d+'/",
            "'version' => '$newVersion'",
            $content
        );

        File::put($configPath, $newContent);

        $this->info("✅ Version bumped: v$oldVersion → v$newVersion");
        $this->newLine();
        $this->comment("Next steps:");
        $this->line("  git add -A && git commit -m \"chore: bump version to v$newVersion\" && git push");
        
        return 0;
    }
}
