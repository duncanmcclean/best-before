<?php

namespace Tests\Fixtures\Expired;

use DuncanMcClean\BestBefore\BestBefore;

#[BestBefore(date: '2000-01-01', description: 'Remove once the backfill has run.')]
class ExpiredClass {}
