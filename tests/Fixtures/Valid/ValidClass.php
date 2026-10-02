<?php

namespace Tests\Fixtures\Valid;

use DuncanMcClean\BestBefore\BestBefore;

#[BestBefore(date: '2999-12-31')]
class ValidClass
{
    #[\DuncanMcClean\BestBefore\BestBefore(description: 'Only valid until Christmas.', date: '2999-12-25')]
    public function handle(): void
    {
        new #[BestBefore('2999-12-31')] class {};
    }
}
