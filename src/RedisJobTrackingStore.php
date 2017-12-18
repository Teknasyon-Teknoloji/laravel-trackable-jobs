<?php
/**
 * Created by PhpStorm.
 * User: ilyasserter
 * Date: 31/10/2017
 * Time: 17:56
 */

namespace Teknasyon\LaravelTrackableJobs;

use Illuminate\Support\Facades\Redis;


/**
 * Class RedisTrackingStore
 * @package Teknasyon\LaravelTrackableJobs
 * @author Ilyas Serter <ilyasserter@teknasyon.com>
 */
class RedisJobTrackingStore implements JobTrackingStore
{

    protected $redisKey = 'trackable-jobs';
    protected $lifeTime = 3600; // 1 hour

    /**
     * @return string
     */
    public function generateId()
    {
        $id = uniqid();
        while ($this->has($id)) {
            $id = uniqid();
        }

        return $id;
    }
    
    /**
     * @param $id
     * @return bool
     */
    public function has($id): bool
    {
        return $this->get($id) !== null;
    }

    /**
     * @param $id
     * @return null|TrackableJobStatus
     */
    public function get($id)
    {
        $data = Redis::get($this->redisId($id));
        $status = unserialize($data);

        if ($status instanceof TrackableJobStatus) {
            return $status;
        }

        return null;
    }

    /**
     * @param $id
     * @param TrackableJobStatus $status
     */
    public function put($id, TrackableJobStatus $status)
    {
        Redis::set($this->redisId($id), serialize($status));
        Redis::expire($this->redisId($id), $this->lifeTime);
    }

    /**
     * @param $id
     */
    public function delete($id)
    {
        Redis::del($this->redisKey . ':' . $id);
    }

    /**
     * @param $id
     * @param $status
     * @return bool
     */
    public function update($id, $status)
    {
        $record = $this->get($id);
        if ($record instanceof TrackableJobStatus) {

            if ($status == TrackableJobStatus::PROCESSING) {
                $record->setStartTime(time());
            } elseif ($status == TrackableJobStatus::OK) {
                $record->setEndTime(time());
            } elseif ($status == TrackableJobStatus::FAILED) {
                $record->setEndTime(time());
            }

            $record->setUpdatedAt(time());
            $record->setStatus($status);
            $this->put($id, $record);
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function getLifeTime()
    {
        return $this->lifeTime;
    }

    /**
     * @param int $lifeTime
     */
    public function setLifeTime($lifeTime)
    {
        $this->lifeTime = $lifeTime;
    }

    /**
     * @param $id
     * @return string
     */
    private function redisId($id)
    {
        return $this->redisKey . ':' . $id;
    }

}