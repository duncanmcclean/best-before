<?php

namespace Tests;

use DateTimeImmutable;
use DuncanMcClean\BestBefore\ExpiringCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ExpiringCodeTest extends TestCase
{
    #[Test]
    #[DataProvider('expiryProvider')]
    public function it_determines_whether_it_has_expired(string $today, bool $expected)
    {
        $code = $this->expiringCode(date: '2026-12-25');

        $this->assertSame($expected, $code->hasExpired(new DateTimeImmutable($today)));
    }

    public static function expiryProvider(): array
    {
        return [
            'the day before' => ['2026-12-24 23:59', false],
            'on the day' => ['2026-12-25 18:00', false],
            'the day after' => ['2026-12-26 00:00', true],
        ];
    }

    #[Test]
    #[DataProvider('invalidDateProvider')]
    public function it_determines_whether_the_date_is_invalid(?string $date, bool $expected)
    {
        $this->assertSame($expected, $this->expiringCode($date)->hasInvalidDate());
    }

    public static function invalidDateProvider(): array
    {
        return [
            'valid date' => ['2026-12-25', false],
            'missing date' => [null, true],
            'wrong format' => ['25/12/2026', true],
            'impossible date' => ['2026-02-31', true],
            'relative date' => ['next week', true],
        ];
    }

    private function expiringCode(?string $date): ExpiringCode
    {
        return new ExpiringCode(name: 'Foo', file: 'Foo.php', line: 1, date: $date, description: null);
    }
}
