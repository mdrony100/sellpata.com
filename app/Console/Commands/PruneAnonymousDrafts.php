<?php

namespace App\Console\Commands;

use App\Models\AnonymousIdentity;
use App\Models\Draft;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Removes anonymous drafts that nobody claimed, plus their private uploads.
 * Verified (claimed) drafts are never touched here.
 */
class PruneAnonymousDrafts extends Command
{
    protected $signature = 'drafts:prune {--days= : Days of inactivity before an unclaimed draft is removed} {--dry-run}';

    protected $description = 'Delete expired anonymous drafts, their media and stale visitor identities';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('builder.retention_days', 30));
        $cutoff = now()->subDays($days);
        $dryRun = (bool) $this->option('dry-run');

        $drafts = Draft::query()
            ->whereNull('user_id')
            ->where('updated_at', '<', $cutoff)
            ->get();

        $this->info(($dryRun ? '[dry run] ' : '').$drafts->count()." unclaimed draft(s) older than {$days} days");

        foreach ($drafts as $draft) {
            if (! $dryRun) {
                Storage::disk('local')->deleteDirectory("drafts/{$draft->id}");
                $draft->media()->delete();
                $draft->delete();
            }
        }

        $identities = AnonymousIdentity::query()
            ->where('expires_at', '<', now())
            ->whereDoesntHave('drafts')
            ->get();

        $this->info(($dryRun ? '[dry run] ' : '').$identities->count().' stale visitor identity(ies)');

        if (! $dryRun) {
            AnonymousIdentity::whereIn('id', $identities->pluck('id'))->delete();
        }

        return self::SUCCESS;
    }
}
