<?php

declare(strict_types=1);

namespace Dried\Humanizer\Translation;

use Dried\Contracts\Translation\DateTranslationsGetter;

final readonly class ArrayDateTranslations implements DateTranslationsGetter
{
    private function __construct(private array $translations)
    {
    }

    /** @return array<string, Closure|array|string|int> */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
