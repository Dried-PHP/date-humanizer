<?php

declare(strict_types=1);

namespace Dried\Humanizer\List;

final readonly class ListJoiner implements ListStringifier
{
    public function __construct(
        private string $glue = ' ',
    ) {
    }

    public function stringify(array $list): string
    {
        return implode($this->glue, $list);
    }
}
