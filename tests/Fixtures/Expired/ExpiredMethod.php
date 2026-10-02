<?php

namespace Tests\Fixtures\Expired;

use DuncanMcClean\BestBefore\BestBefore as RemoveBy;

class ExpiredMethod
{
    #[RemoveBy('2000-01-01')]
    public function legacyPayload(): array
    {
        return [];
    }
}
