<?php

namespace App\Services\Cache;

use App\Models\Status;
use Illuminate\Support\Collection;

class InternshipStatusService extends BaseCacheService
{
    protected function key(): string
    {
        return 'internship_statuses.all';
    }

    protected function query()
    {
        return Status::all();
    }

    /** @return Collection<int, Status> */
    public function collection(): Collection
    {
        return collect(parent::all());
    }

    public function getById(int $id): ?Status
    {
        return $this->collection()->firstWhere('id', $id);
    }

    public function getByName(string $name): ?Status
    {
        return $this->collection()->firstWhere('name', $name);
    }

    public function getTransitionRule(string $fromStatusName, string $toStatusName): ?array
    {
        $allowed = config('internship_notification_rules.status_transitions', []);
        return $allowed[$fromStatusName][$toStatusName] ?? null;
    }

    public function isTransitionAllowed(string $fromStatusName, string $toStatusName): bool
    {
        return $this->getTransitionRule($fromStatusName, $toStatusName) !== null;
    }

    public function getTransitionRuleByIds(int $fromStatusId, int $toStatusId): ?array
    {
        $from = $this->getById($fromStatusId)?->name;
        $to = $this->getById($toStatusId)?->name;

        if (!$from || !$to) {
            return null;
        }

        return $this->getTransitionRule($from, $to);
    }

    public function isTransitionAllowedByIds(int $fromStatusId, int $toStatusId): bool
    {
        return $this->getTransitionRuleByIds($fromStatusId, $toStatusId) !== null;
    }
}
