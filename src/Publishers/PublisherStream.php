<?php

namespace Micromus\KafkaBusOutbox\Publishers;

use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Interfaces\Publishers\PublisherInterface;
use Micromus\KafkaBusOutbox\Interfaces\Publishers\PublisherStreamInterface;

class PublisherStream implements PublisherStreamInterface
{
    protected bool $forceStop = false;

    public function __construct(
        protected PublisherInterface $publisher,
        protected ProducerMessageRepositoryInterface $producerMessageRepository,
        protected int $timeToSleep = 60,
        protected int $limit = 100
    ) {
    }

    public function forceStop(): void
    {
        $this->forceStop = true;
    }

    public function process(): void
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
}
