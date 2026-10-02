<?php

namespace DuncanMcClean\BestBefore;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class BestBefore
{
    public function __construct(
        public string $date,
        public ?string $description = null,
    ) {}
}
