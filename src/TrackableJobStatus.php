<?php

namespace Teknasyon\LaravelTrackableJobs;


/**
 * Class TrackableJobStatus
 * @package Teknasyon\LaravelTrackableJobs
 * @author Ilyas Serter <ilyasserter@teknasyon.com>
 */
class TrackableJobStatus
{
    const PENDING = 0;
    const PROCESSING = 777;
    const OK = 1;
    const FAILED = -1;

    protected $status;
    protected $createdAt;
    protected $updatedAt;
    protected $startTime;
    protected $endTime;

    public function __construct($status = self::PENDING)
    {
        $this->status = $status;
        $this->createdAt = time();
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return int
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param int $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param mixed $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param mixed $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }


    public function isDone()
    {
        return in_array($this->status,[TrackableJobStatus::OK, TrackableJobStatus::FAILED]);
    }

}