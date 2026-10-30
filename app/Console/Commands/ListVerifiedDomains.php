<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\Platform\CustomDomainService;
use Illuminate\Console\Command;

class ListVerifiedDomains extends Command
{
    protected $signature = 'domains:verified';

    protected $description = 'Print verified custom domains, one per line';

    public function handle(): int
    {
        $domains = Store::query()
            ->where('custom_domain_status', CustomDomainService::VERIFIED)
            ->whereNotNull('custom_domain')
            ->orderBy('custom_domain')
            ->pluck('custom_domain');

        foreach ($domains as $domain) {
            $this->output->writeln($domain);
        }

        return self::SUCCESS;
    }
}
