<?php

namespace App\Services\Platform;

class PhpDnsLookup implements DnsLookup
{
    public function aRecords(string $host): array
    {
        $records = @dns_get_record($host, DNS_A);

        if (! is_array($records)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (array $row) => isset($row['ip']) ? (string) $row['ip'] : null,
            $records,
        )));
    }

    public function cnameTargets(string $host): array
    {
        $records = @dns_get_record($host, DNS_CNAME);

        if (! is_array($records)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (array $row) => isset($row['target']) ? (string) $row['target'] : null,
            $records,
        )));
    }
}
