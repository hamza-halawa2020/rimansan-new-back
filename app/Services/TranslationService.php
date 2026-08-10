<?php

namespace App\Services;

class TranslationService
{
    private array $langs = ['ar', 'en'];
    private string $basePath = 'i18n/';

    public function index(): array
    {
        $translations = [];
        $version = [];

        foreach ($this->langs as $lang) {
            $filePath = $this->path($lang);
            if (file_exists($filePath)) {
                $translations[$lang] = json_decode(file_get_contents($filePath), true);
                $version[$lang] = filemtime($filePath);
            } else {
                $translations[$lang] = [];
                $version[$lang] = '0';
            }
        }

        return [
            'translations' => $translations,
            'version' => $version,
        ];
    }

    public function update(string $lang, array $translations): void
    {
        $filePath = $this->path($lang);

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        file_put_contents($filePath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function isSupported(string $lang): bool
    {
        return in_array($lang, $this->langs);
    }

    public function path(string $lang): string
    {
        return public_path($this->basePath . "{$lang}.json");
    }
}
