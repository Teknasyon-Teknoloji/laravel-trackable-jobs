<?php

namespace Teknasyon\LaravelTrackableJobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;

/**
 * Class TrackableJob
 * @author Ilyas Serter <ilyasserter@teknasyon.com>
 */
abstract class TrackableJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public $trackingId;

    /**
     *
     * @ilyas I could make this private, but I'm not your daddy. You should know what not to do ;)
     *
     * @var JobTrackingStoreInterface
     */
    protected $store;

    /**
     * TrackableJob constructor.
     */
    public function __construct()
    {
        $this->store = new RedisJobTrackingStore();
    }

    /**
     *
     */
    public function startTracking()
    {
        $this->trackingId = $this->generateTrackingId();
        while ($this->store->has($this->trackingId)) {
            $this->trackingId = $this->generateTrackingId();
        }

        $this->store->put($this->trackingId, new TrackableJobStatus());
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

    /**
     * @return string
     */
    private function generateTrackingId()
    {
        return uniqid();
    }

}