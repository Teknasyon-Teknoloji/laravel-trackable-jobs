<?php

namespace Teknasyon\LaravelTrackableJobs;

interface JobTrackingStoreInterface
{

    public function has($id): bool;

    public function get($id);

    public function put($id, TrackableJobStatus $status);

    public function update($id, $status);
}