<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Tests\Support\Fakes;

final class WriterAwareTrackedJobBuilderFake
{
    public bool $useWritePdoCalled = false;

    public string|int|null $lastFindOrFailId = null;

    public function useWritePdo(): self
    {
        $this->useWritePdoCalled = true;

        return $this;
    }

    public function findOrFail(string|int|null $id): WriterAwareTrackedJobFake
    {
        $this->lastFindOrFailId = $id;

        return new WriterAwareTrackedJobFake();
    }
}
