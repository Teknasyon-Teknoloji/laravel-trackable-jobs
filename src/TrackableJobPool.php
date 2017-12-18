<?php

namespace Teknasyon\LaravelTrackableJobs;

use Symfony\Component\Process\Process;


/**
 * Class Pool
 * @package Teknasyon\LaravelTrackableJobs
 * @author Ilyas Serter <ilyasserter@teknasyon.com>
 */
class TrackableJobPool
{

    protected $id;
    protected $queue = 'default';

    /**
     * @var TrackableJob[]
     */
    protected $jobs = [];

    protected $workerCount = 5;

    /**
     * @var Process[]
     */
    protected $workers = [];

    protected $maxWaitTime = 30; // seconds

    protected $dispatchTime;

    public function __construct($workerCount = 5)
    {
        $this->id = uniqid();
        $this->queue = 'pool' . $this->id;
        $this->workerCount = $workerCount;
    }

    public function run()
    {
        $this->createWorkers()
            ->dispatchJobs()
            ->waitForJobsToFinish();
    }

    public function addJob(TrackableJob $job)
    {
        $this->jobs[] = $job;
        return $this;
    }

    public function getJobs()
    {
        return $this->jobs;
    }

    public function dispatchJobs()
    {
        $this->dispatchTime = time();
        foreach ($this->jobs as $job) {
            $job->startTracking();
            dispatch($job)->onQueue($this->queue);
        }
        return $this;
    }

    public function createWorker()
    {
        $process = new Process('exec php artisan queue:work --queue=' . $this->queue . ' > /dev/null 2>&1', base_path());
        $process->start();
        $this->workers[$process->getPid()] = $process;
        return $this;
    }

    public function createWorkers()
    {
        $i = 0;
        while (count($this->workers) < $this->workerCount) {
            $this->createWorker();
            $i++;
        }
        return $this;
    }

    public function killWorkers()
    {
        foreach ($this->workers as $pid => $worker) {
            $worker->stop();
            unset($this->workers[$pid]);
        }
        return $this;
    }

    public function hasUnfinishedJobs()
    {
        foreach ($this->jobs as $job) {
            if ($job->isDone() === false) {
                return true;
            }
        }

        return false;
    }

    public function waitForJobsToFinish()
    {
        while ($this->hasUnfinishedJobs()) {
            if (time() - $this->dispatchTime > $this->maxWaitTime) {
                throw new \RuntimeException("Max wait time exceeded", -10001);
            }
            sleep(1);
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Getters & Setters
    |--------------------------------------------------------------------------
    |
    */

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @param int $workerCount
     * @return $this
     */
    public function setWorkerCount(int $workerCount)
    {
        $this->workerCount = $workerCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getWorkerCount()
    {
        return $this->workerCount;
    }

    /**
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param string $queue
     */
    public function setQueue(string $queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return int
     */
    public function getMaxWaitTime()
    {
        return $this->maxWaitTime;
    }

    /**
     * @param int $maxWaitTime
     */
    public function setMaxWaitTime(int $maxWaitTime)
    {
        $this->maxWaitTime = $maxWaitTime;
    }

    /*
    |--------------------------------------------------------------------------
    | Magic Methods
    |--------------------------------------------------------------------------
    |
    */

    public function __destruct()
    {
        $this->killWorkers();
    }
}