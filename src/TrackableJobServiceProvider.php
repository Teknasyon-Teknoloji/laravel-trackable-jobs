<?php

namespace Teknasyon\LaravelTrackableJobs;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;


/**
 * Class TrackableJobServiceProvider
 * @package Teknasyon\LaravelTrackableJobs
 * @author Ilyas Serter <ilyasserter@teknasyon.com>
 */
class TrackableJobServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // publish config
        $this->publishes([
            __DIR__ . '/../config/trackable-jobs.php' => config_path('trackable-jobs.php'),
        ]);

        // register event listeners for job trackings
        Queue::before(function (JobProcessing $event) {
            $actualJob = unserialize($event->job->payload()['data']['command']);
            if ($actualJob instanceof TrackableJob) {
                $actualJob->updateStatus(TrackableJobStatus::PROCESSING);
            }
        });

        Queue::after(function (JobProcessed $event) {
            $actualJob = unserialize($event->job->payload()['data']['command']);
            if ($actualJob instanceof TrackableJob) {
                $actualJob->updateStatus(TrackableJobStatus::OK);
            }
        });

        Queue::failing(function (JobFailed $event) {
            $actualJob = unserialize($event->job->payload()['data']['command']);
            if ($actualJob instanceof TrackableJob) {
                $actualJob->updateStatus(TrackableJobStatus::FAILED);
            }
        });
    }

    public function register()
    {
        $this->app->singleton(JobTrackingStoreInterface::class, new RedisJobTrackingStore());

    }

}