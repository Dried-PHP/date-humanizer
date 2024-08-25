<?php

declare(strict_types=1);

namespace Dried\Humanizer\Translation;

use Dried\Contracts\Translation\DateTranslationsGetter;

final class FileDateTranslations implements DateTranslationsGetter
{
    private ?array $translations = null;

    public function __construct(
        public readonly string $file,
    ) {
    }

    /** @return array<string, Closure|array|string|int> */
    public function getTranslations(): array
    {
        return $this->translations ??= require $this->file;
    }
}
