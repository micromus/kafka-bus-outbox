<?php

namespace Micromus\KafkaBusOutbox\Interfaces\Producers;

use Micromus\KafkaBusOutbox\Exceptions\CannotPublishMessageForTopicException;

interface OutboxProducerStreamInterface
{
    /**
     * @return void
     *
     * @throws CannotPublishMessageForTopicException
     */
    public function process(): void;

    public function forceStop(): void;
}
