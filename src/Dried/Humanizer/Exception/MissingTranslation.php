<?php

declare(strict_types=1);

namespace Dried\Humanizer\Exception;

use RuntimeException;

final class MissingTranslation extends RuntimeException
{
    public static function forKey(string $key): self
    {
        return new self("Translation for $key is not found.");
    }
}
