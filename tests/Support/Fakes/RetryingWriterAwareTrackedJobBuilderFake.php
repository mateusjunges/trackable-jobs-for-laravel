<?php declare(strict_types=1);

namespace Junges\TrackableJobs\Tests\Support\Fakes;

use Illuminate\Database\Eloquent\ModelNotFoundException;

final class RetryingWriterAwareTrackedJobBuilderFake
{
    public bool $useWritePdoCalled = false;

    public int $findOrFailCalls = 0;

    public function useWritePdo(): self
    {
        $this->useWritePdoCalled = true;

        return $this;
    }

    public function findOrFail(string|int|null $id): RetryingWriterAwareTrackedJobFake
    {
        $this->findOrFailCalls++;

        if ($this->findOrFailCalls <= RetryingWriterAwareTrackedJobFake::$failuresBeforeSuccess) {
            throw (new ModelNotFoundException())->setModel(RetryingWriterAwareTrackedJobFake::class, [$id]);
        }

        return new RetryingWriterAwareTrackedJobFake();
    }
}
