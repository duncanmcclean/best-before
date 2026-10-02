<?php

namespace DuncanMcClean\BestBefore;

use DateTimeImmutable;

final readonly class ExpiringCode
{
    public function __construct(
        public string $name,
        public string $file,
        public int $line,
        public ?string $date,
        public ?string $description,
    ) {}

    public function hasExpired(DateTimeImmutable $today): bool
    {
        $bestBefore = $this->bestBefore();

        return $bestBefore !== null && $bestBefore < $today->setTime(0, 0);
    }

    public function hasInvalidDate(): bool
    {
        return $this->bestBefore() === null;
    }

    private function bestBefore(): ?DateTimeImmutable
    {
        if ($this->date === null) {
            return null;
        }

        $bestBefore = DateTimeImmutable::createFromFormat('!Y-m-d', $this->date);

        if ($bestBefore === false || $bestBefore->format('Y-m-d') !== $this->date) {
            return null;
        }

        return $bestBefore;
    }
}
