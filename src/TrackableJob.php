<?php

namespace Teknasyon\LaravelTrackableJobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Class TrackableJob
 * @author Ilyas Serter <ilyasserter@teknasyon.com>
 */
abstract class TrackableJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    protected $trackingId;

    /**
     *
     * @ilyas I could make this private, but I'm not your daddy. You should know what not to do ;)
     *
     * @var JobTrackingStore
     */
    protected $store;

    /**
     * TrackableJob constructor.
     * @param JobTrackingStore $store
     */
    public function __construct(JobTrackingStore $store)
    {
        $this->store = $store;
    }

    /**
     *
     */
    public function startTracking()
    {
        $this->trackingId = $this->store->generateId();

        $this->store->put($this->trackingId, new TrackableJobStatus());
    }

    public function getTrackingId()
    {
        return $this->trackingId;
    }

    /**
     * @return null|TrackableJobStatus
     */
    public function status()
    {
        return $this->store->get($this->trackingId);
    }

    /**
     * @return bool
     */
    public function isDone()
    {
        return $this->status()->isDone();
    }

    /**
     * @param $status
     * @return bool
     */
    public function updateStatus($status)
    {
        return $this->store->update($this->trackingId,$status);
    }

}