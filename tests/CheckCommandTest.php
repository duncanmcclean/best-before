<?php

namespace Tests;

use DuncanMcClean\BestBefore\CheckCommand;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CheckCommandTest extends TestCase
{
    #[Test]
    public function it_passes_when_nothing_has_expired()
    {
        $tester = $this->runCommand(['paths' => [__DIR__.'/Fixtures/Valid']]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('Nothing is past its best before date.', $tester->getDisplay());
    }

    #[Test]
    public function it_fails_when_code_is_past_its_best_before_date()
    {
        $tester = $this->runCommand(['paths' => [__DIR__.'/Fixtures/Expired']]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Tests\Fixtures\Expired\ExpiredClass was best before 2000-01-01', $tester->getDisplay());
        $this->assertStringContainsString('Remove once the backfill has run.', $tester->getDisplay());
        $this->assertStringContainsString('Fixtures/Expired/ExpiredClass.php:7', $tester->getDisplay());
        $this->assertStringContainsString('Tests\Fixtures\Expired\ExpiredMethod::legacyPayload() was best before 2000-01-01', $tester->getDisplay());
        $this->assertStringContainsString('Found 2 piece(s) of code past their best before date.', $tester->getDisplay());
    }

    #[Test]
    public function it_fails_when_a_date_is_invalid()
    {
        $tester = $this->runCommand(['paths' => [__DIR__.'/Fixtures/Invalid']]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Tests\Fixtures\Invalid\InvalidDate has an invalid date.', $tester->getDisplay());
    }

    #[Test]
    public function it_warns_about_files_it_could_not_parse()
    {
        $tester = $this->runCommand(['paths' => [__DIR__.'/Fixtures/Ignored']]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('Skipped '.__DIR__.'/Fixtures/Ignored/Unparseable.php', $tester->getDisplay());
    }

    private function runCommand(array $input): CommandTester
    {
        $command = (new CheckCommand)->setAutoExit(false);

        $tester = new CommandTester($command);
        $tester->execute($input);

        return $tester;
    }
}
