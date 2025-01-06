<?php

namespace Micromus\KafkaBusOutbox\Interfaces\Producers;

use Micromus\KafkaBusOutbox\Exceptions\CannotPublishMessageForTopicException;

interface OutboxProducerStreamInterface
{
    /**
     * @param bool $once
     * @return void
     *
     * @throws CannotPublishMessageForTopicException
     */
    public function process(bool $once = false): void;

    public function forceStop(): void;
}
