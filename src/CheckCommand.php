<?php

namespace DuncanMcClean\BestBefore;

use DateTimeImmutable;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

final class CheckCommand extends SingleCommandApplication
{
    protected function configure(): void
    {
        $this
            ->setName('best-before')
            ->setDescription('Fails when any code marked with the #[BestBefore] attribute is past its best before date.')
            ->addArgument('paths', InputArgument::IS_ARRAY, 'The files or directories to scan.', ['.']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $scanner = new Scanner;
        $today = new DateTimeImmutable('today');
        $expiringCode = $scanner->scan($input->getArgument('paths'));

        foreach ($scanner->unparseableFiles() as $file => $error) {
            $output->writeln("<comment>Skipped {$file}: {$error}</comment>");
        }

        $invalid = array_filter($expiringCode, fn (ExpiringCode $code) => $code->hasInvalidDate());
        $expired = array_filter($expiringCode, fn (ExpiringCode $code) => $code->hasExpired($today));

        foreach ($expired as $code) {
            $output->writeln("<fg=red>✗</> <options=bold>{$code->name}</> was best before {$code->date}");
            $this->writeDetails($output, $code);
        }

        foreach ($invalid as $code) {
            $output->writeln("<fg=red>✗</> <options=bold>{$code->name}</> has an invalid date. Dates must be string literals in Y-m-d format.");
            $this->writeDetails($output, $code);
        }

        $failures = count($expired) + count($invalid);

        if ($failures === 0) {
            $output->writeln('<info>Nothing is past its best before date.</info>');

            return self::SUCCESS;
        }

        $output->writeln("<error>Found {$failures} piece(s) of code past their best before date.</error>");

        return self::FAILURE;
    }

    private function writeDetails(OutputInterface $output, ExpiringCode $code): void
    {
        if ($code->description !== null) {
            $output->writeln("  {$code->description}");
        }

        $output->writeln("  <fg=gray>{$code->file}:{$code->line}</>");
        $output->writeln('');
    }
}
