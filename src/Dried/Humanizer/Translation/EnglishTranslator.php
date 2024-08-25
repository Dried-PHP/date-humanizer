<?php

declare(strict_types=1);

namespace Dried\Humanizer\Translation;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class EnglishTranslator implements TranslatorInterface
{
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        $locale ??= 'en';

        if ($locale !== 'en' && $locale !== 'en_US') {
            throw new InvalidArgumentException(
                'This translator is only a fallback to translate dates in English (US), for any other language, you may consider using symfony/translation',
            );
        }

        return strtr($this->getVariant($id, $parameters), $parameters);
    }

    public function getLocale(): string
    {
        return 'en_US';
    }

    private function getVariant(string $id, array $parameters): string
    {
        $count = $parameters['%count%'] ?? $parameters[':count'] ?? null;

        if ($count === null) {
            return $id;
        }

        $count = (float) $count;
        $skippedMatch = 0;
        $singularVariant = null;

        foreach (explode('|', $id) as $index => $variant) {
            if (preg_match('/^\{(inf|infinity|\d+(?:\.\d+)?)}/i', $variant, $matches)) {
                if ($this->parseFloat($matches[1]) === $count) {
                    return substr($variant, \strlen($matches[0]));
                }

                $skippedMatch++;

                continue;
            }

            if (preg_match('/^(\[|])(inf|infinity|\d+(?:\.\d+)?),(inf|infinity|\d+(?:\.\d+)?)(\[|])/i', $variant, $matches)) {
                $min = $this->parseFloat($matches[2]);
                $max = $this->parseFloat($matches[3]);

                if ($matches[1] === '[' ? ($count < $min) : ($count <= $min)) {
                    $skippedMatch++;

                    continue;
                }

                if ($matches[4] === '[' ? ($count >= $max) : ($count > $max)) {
                    $skippedMatch++;

                    continue;
                }

                return substr($variant, \strlen($matches[0]));
            }

            $expectSingular = (($index - $skippedMatch) === 0);
            $isSingular = ($count === 1.0);

            if ($isSingular === $expectSingular) {
                return $variant;
            }

            if ($expectSingular) {
                $singularVariant = $variant;
            }
        }

        return $singularVariant ?? throw new RuntimeException(
            'Unable to choose a translation for "' . $id . '" with locale for value ' . $count,
        );
    }

    private function parseFloat(string $number): float
    {
        return match (strtolower($number)) {
            'inf', 'infinity' => INF,
            default => (float) $number,
        };
    }
}
