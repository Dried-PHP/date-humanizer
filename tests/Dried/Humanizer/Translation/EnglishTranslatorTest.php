<?php

declare(strict_types=1);

namespace Tests\Dried\Humanizer\Translation;

use Dried\Humanizer\List\ListTranslator;
use Dried\Humanizer\Translation\ArrayDateTranslations;
use Dried\Humanizer\Translation\EnglishTranslator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EnglishTranslatorTest extends TestCase
{
    public function testEnglishOnly(): void
    {
        self::expectExceptionObject(new InvalidArgumentException(
            'This translator is only a fallback to translate dates in English (US), for any other language, you may consider using symfony/translation',
        ));

        $translator = new EnglishTranslator();
        $translator->trans('foo', locale: 'de');
    }

    public function testFallbackToKey(): void
    {
        $translator = new EnglishTranslator();

        self::assertSame('foo', $translator->trans('foo'));
    }

    public function testNewReplacementSyntaxHasPrecedence(): void
    {
        $translator = new EnglishTranslator();

        self::assertSame('foo', $translator->trans('foo|bar', [
            ':count' => 2,
            '%count%' => 1,
        ]));
        self::assertSame('bar', $translator->trans('foo|bar', [
            ':count' => 2,
        ]));
    }
}
