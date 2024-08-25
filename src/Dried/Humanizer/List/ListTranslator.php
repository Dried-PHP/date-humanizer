<?php

declare(strict_types=1);

namespace Dried\Humanizer\List;

use Dried\Contracts\Translation\DateTranslationsGetter;
use RuntimeException;

final readonly class ListTranslator implements ListStringifier
{
    public function __construct(
        private DateTranslationsGetter $translations,
    ) {
    }

    public function stringify(array $list): string
    {
        $glues = $this->translations->getTranslations()['list'] ?? ' ';

        if (\is_string($glues)) {
            return implode($glues, $list);
        }

        if (\is_callable($glues)) {
            return $glues($list);
        }

        if (!\is_array($glues)) {
            throw new RuntimeException('Translation for "list" should be callable, string or array.');
        }

        if (\count($list) < 2) {
            return implode('', $list);
        }

        [$default, $last] = array_pad($glues, 2, null);
        $default ??= ' ';
        $last ??= $default;
        $end = array_pop($list);

        return implode($default, $list) . $last . $end;
    }
}
