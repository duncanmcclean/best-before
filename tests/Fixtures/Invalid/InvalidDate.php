<?php

namespace Tests\Fixtures\Invalid;

use DuncanMcClean\BestBefore\BestBefore;

#[BestBefore(date: '25/12/2026')]
class InvalidDate {}
