<?php

namespace App\Console\Commands;

use App\Services\Platform\CustomDomainService;
use Illuminate\Console\Command;

class ListVerifiedDomains extends Command
{
    protected $signature = 'domains:verified';

    protected $description = 'Print verified custom domains, one per line';

    public function handle(): int
    {
        foreach (app(CustomDomainService::class)->verifiedDomainNames() as $domain) {
            $this->output->writeln($domain);
        }

        return self::SUCCESS;
    }
}
