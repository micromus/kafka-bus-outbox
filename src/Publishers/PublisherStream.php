<?php

namespace Micromus\KafkaBusOutbox\Publishers;

class PublisherStream
{
    protected bool $forceStop = false;

    public function __construct(
        protected Publisher $publisher,
        protected int $timeToSleep = 60
    ) {
    }

    public function handle(): void
    {
        do {
            $this->publisher->publish();

            sleep($this->timeToSleep);
        }
        while (!$this->forceStop);
    }

    public function forceStop(): void
    {
        $this->forceStop = true;
    }
}
