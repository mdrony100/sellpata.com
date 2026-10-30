<?php

namespace App\Services\Platform;

use App\Models\Page;
use App\Models\Store;

/**
 * Single place that answers "may this store do X on its plan?".
 * Every entry point (API, dashboard, publishing) asks here, never the UI.
 */
class Entitlements
{
    public function __construct(private Store $store) {}

    public static function for(Store $store): self
    {
        return new self($store);
    }

    public function plan()
    {
        return $this->store->plan();
    }

    public function maxPages(): int
    {
        return (int) $this->plan()->max_pages;
    }

    public function pageCount(): int
    {
        return $this->store->pages()->count();
    }

    public function pagesRemaining(): int
    {
        return max(0, $this->maxPages() - $this->pageCount());
    }

    public function canCreatePage(): bool
    {
        return $this->pagesRemaining() > 0;
    }

    public function feature(string $flag): bool
    {
        return $this->plan()->has($flag);
    }

    /** @return array<string, bool> */
    public function features(): array
    {
        return $this->plan()->features ?? [];
    }
}
