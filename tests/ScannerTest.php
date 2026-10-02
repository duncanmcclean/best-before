<?php

namespace Tests;

use DuncanMcClean\BestBefore\ExpiringCode;
use DuncanMcClean\BestBefore\Scanner;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ScannerTest extends TestCase
{
    #[Test]
    public function it_finds_classes_and_methods_with_a_best_before_date()
    {
        $expiringCode = (new Scanner)->scan([__DIR__.'/Fixtures/Valid']);

        $this->assertEquals([
            new ExpiringCode(
                name: 'Tests\Fixtures\Valid\ValidClass',
                file: __DIR__.'/Fixtures/Valid/ValidClass.php',
                line: 7,
                date: '2999-12-31',
                description: null,
            ),
            new ExpiringCode(
                name: 'Tests\Fixtures\Valid\ValidClass::handle()',
                file: __DIR__.'/Fixtures/Valid/ValidClass.php',
                line: 10,
                date: '2999-12-25',
                description: 'Only valid until Christmas.',
            ),
            new ExpiringCode(
                name: 'class@anonymous',
                file: __DIR__.'/Fixtures/Valid/ValidClass.php',
                line: 13,
                date: '2999-12-31',
                description: null,
            ),
        ], $expiringCode);
    }

    #[Test]
    public function it_resolves_aliased_attributes_and_positional_arguments()
    {
        $expiringCode = (new Scanner)->scan([__DIR__.'/Fixtures/Expired/ExpiredMethod.php']);

        $this->assertCount(1, $expiringCode);
        $this->assertSame('Tests\Fixtures\Expired\ExpiredMethod::legacyPayload()', $expiringCode[0]->name);
        $this->assertSame('2000-01-01', $expiringCode[0]->date);
    }

    #[Test]
    public function it_ignores_attributes_with_the_same_short_name_from_other_namespaces()
    {
        $scanner = new Scanner;

        $this->assertSame([], $scanner->scan([__DIR__.'/Fixtures/Ignored']));
    }

    #[Test]
    public function it_tracks_files_it_could_not_parse()
    {
        $scanner = new Scanner;
        $scanner->scan([__DIR__.'/Fixtures/Ignored']);

        $this->assertArrayHasKey(__DIR__.'/Fixtures/Ignored/Unparseable.php', $scanner->unparseableFiles());
    }
}
