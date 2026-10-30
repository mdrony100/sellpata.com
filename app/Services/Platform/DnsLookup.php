<?php

namespace App\Services\Platform;

interface DnsLookup
{
    /** @return list<string> IPv4 addresses published at $host. */
    public function aRecords(string $host): array;

    /** @return list<string> CNAME targets published at $host. */
    public function cnameTargets(string $host): array;
}
