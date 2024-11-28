<?php

namespace Micromus\KafkaBusOutbox\Publishers;

use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;

class PublisherStream
{
    protected bool $forceStop = false;

    public function __construct(
        protected Publisher $publisher,
        protected ProducerMessageRepositoryInterface $producerMessageRepository,
        protected int $timeToSleep = 60,
        protected int $limit = 100
    ) {
    }

    public function handle(): void
    {
        do {
            $messages = $this->producerMessageRepository
                ->get($this->limit);

            if (count($messages) === 0) {
                sleep($this->timeToSleep);

                continue;
            }

            $this->publisher
                ->publish($messages);
        }
        while (!$this->forceStop);
    }

    public function forceStop(): void
    {
        $this->forceStop = true;
    }
}
