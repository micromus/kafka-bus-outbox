<?php

namespace Micromus\KafkaBusOutbox\Producers;

use Micromus\KafkaBusOutbox\Interfaces\ProducerMessageRepositoryInterface;
use Micromus\KafkaBusOutbox\Interfaces\Producers\OutboxProducerInterface;
use Micromus\KafkaBusOutbox\Interfaces\Producers\OutboxProducerStreamInterface;

class OutboxProducerStream implements OutboxProducerStreamInterface
{
    protected bool $forceStop = false;

    public function __construct(
        protected OutboxProducerInterface $producer,
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

            $this->producer
                ->publish($messages);
        }
        while (!$this->forceStop);
    }
}
