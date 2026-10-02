# Best Before

Give temporary classes and methods a best before date, then fail CI once they've gone off.

Handy for backfill commands, temporary backwards compatibility layers, feature flags, or anything else you _promise_ you'll remove later.

## Installation

```bash
composer require --dev duncanmcclean/best-before
```

## Usage

Add the `#[BestBefore]` attribute to any class or method, along with the date it should be removed by and an optional description:

```php
use DuncanMcClean\BestBefore\BestBefore;

#[BestBefore('2026-12-25', 'Only valid until Christmas.')]
class BackfillOrderTotals extends Command
{
    // ...
}

class OrderResource extends JsonResource
{
    #[BestBefore('2027-01-31', 'Keep `total` until the mobile app reads `grand_total`.')]
    private function legacyFields(): array
    {
        // ...
    }
}
```

Dates must be in `Y-m-d` format. Code goes off the day _after_ its best before date.

### Checking for expired code

Point the `best-before` binary at the directories you want to scan:

```bash
vendor/bin/best-before app src
```

If anything is past its best before date (or has an invalid date), it'll be listed and the command will exit with a non-zero status code:

```
✗ App\Console\Commands\BackfillOrderTotals was best before 2026-12-25
  Only valid until Christmas.
  app/Console/Commands/BackfillOrderTotals.php:7

Found 1 piece(s) of code past their best before date.
```

Files are parsed statically, so nothing is autoloaded or executed. The `vendor` and `node_modules` directories are always skipped.

When no paths are given, the current directory is scanned.

## GitHub Actions

You can run `best-before` as a step in your existing test workflow:

```yaml
- name: Check best before dates
  run: vendor/bin/best-before app src
```

However, code goes off whether or not anyone is pushing to the repository. To catch it on quiet weeks too, you may want a dedicated workflow that also runs on a schedule:

```yaml
# .github/workflows/best-before.yaml
name: Best Before

on:
  push:
    branches:
      - main
  pull_request:
  schedule:
    - cron: '0 9 * * 1' # Every Monday at 9am
  workflow_dispatch:

permissions: {}

concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true

jobs:
  best-before:
    name: Check best before dates
    runs-on: ubuntu-latest
    permissions:
      contents: read

    steps:
      - name: Checkout code
        uses: actions/checkout@3d3c42e5aac5ba805825da76410c181273ba90b1 # v7.0.1
        with:
          persist-credentials: false

      - name: Setup PHP
        uses: shivammathur/setup-php@f3e473d116dcccaddc5834248c87452386958240 # 2.37.2
        with:
          php-version: 8.5
          coverage: none

      - name: Install dependencies
        run: composer install --prefer-dist --no-interaction --no-progress

      - name: Check best before dates
        run: vendor/bin/best-before app src
```

When a scheduled run fails, GitHub will email whoever last changed the workflow's `cron` schedule.

## License

Best Before is open-sourced software licensed under the [MIT license](LICENSE).
