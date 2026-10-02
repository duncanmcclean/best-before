<?php

namespace DuncanMcClean\BestBefore;

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

final class Scanner
{
    private Parser $parser;

    /** @var array<string, string> */
    private array $unparseableFiles = [];

    public function __construct()
    {
        $this->parser = (new ParserFactory)->createForNewestSupportedVersion();
    }

    /**
     * @param  array<string>  $paths
     * @return array<ExpiringCode>
     */
    public function scan(array $paths): array
    {
        $this->unparseableFiles = [];

        $expiringCode = [];

        foreach ($this->files($paths) as $file) {
            array_push($expiringCode, ...$this->scanFile($file));
        }

        return $expiringCode;
    }

    /**
     * @param  array<string>  $paths
     * @return iterable<string>
     */
    private function files(array $paths): iterable
    {
        $directories = array_filter($paths, is_dir(...));

        foreach (array_filter($paths, is_file(...)) as $file) {
            yield $file;
        }

        if (empty($directories)) {
            return;
        }

        $finder = Finder::create()
            ->files()
            ->name('*.php')
            ->in($directories)
            ->exclude(['vendor', 'node_modules'])
            ->sortByName();

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            yield $file->getPathname();
        }
    }

    /**
     * @return array<ExpiringCode>
     */
    private function scanFile(string $file): array
    {
        try {
            $ast = $this->parser->parse(file_get_contents($file)) ?? [];
        } catch (Error $error) {
            $this->unparseableFiles[$file] = $error->getMessage();

            return [];
        }

        $visitor = new BestBeforeVisitor($file);

        $traverser = new NodeTraverser(new NameResolver, $visitor);
        $traverser->traverse($ast);

        return $visitor->expiringCode();
    }

    /**
     * @return array<string, string>
     */
    public function unparseableFiles(): array
    {
        return $this->unparseableFiles;
    }
}
