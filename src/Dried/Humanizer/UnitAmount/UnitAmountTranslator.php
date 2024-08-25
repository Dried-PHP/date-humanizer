<?php

declare(strict_types=1);

namespace Dried\Humanizer\UnitAmount;

use Dried\Contracts\Translation\DateTranslationsGetter;
use Dried\Humanizer\Exception\MissingTranslation;
use Dried\Utils\UnitAmount;
use NumberFormatter;
use RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class UnitAmountTranslator implements UnitAmountStringifier
{
    public function __construct(
        private TranslatorInterface $translator,
        private DateTranslationsGetter $translations,
    ) {
    }

    public function stringify(UnitAmount $unitAmount, array $parameters = []): string
    {
        $translations = $this->translations->getTranslations();
        $unitName = $unitAmount->unit->value;
        $translation = $translations[$unitName] ?? throw MissingTranslation::forKey($unitName);

        if (!\is_string($translation)) {
            throw new RuntimeException(
                self::class . ' only supports string translations, ' .
                'to work with other types, implement your own ' . UnitAmountStringifier::class,
            );
        }

        $translation = str_replace([':count', '%count%'], '%formattedCount%', $translation);

        return $this->translator->trans($translation, [
            '%formattedCount%' => $this->formatNumber($unitAmount->amount),
            '%count%' => $unitAmount->amount,
            ':optional-space' => '',
            ...$parameters,
        ]);
    }

    /** @infection-ignore-all */
    private function formatNumber(float $number): string
    {
        $string = (string) $number;

        if ($string === (string) (int) $number) {
            return $string;
        }

        return $this->getNumberFormatter()->format($number);
    }

    /** @codeCoverageIgnore */
    private function getNumberFormatter(): NumberFormatter
    {
        if (!class_exists(NumberFormatter::class)) {
            throw new RuntimeException(
                NumberFormatter::class . ' is needed to format decimal number, ' .
                'you may install it via either PHP intl extension (ext-intl) or ' .
                'symfony/polyfill-intl-icu composer package (English-only).',
            );
        }

        return NumberFormatter::create($this->translator->getLocale(), NumberFormatter::PATTERN_DECIMAL);
    }
}
