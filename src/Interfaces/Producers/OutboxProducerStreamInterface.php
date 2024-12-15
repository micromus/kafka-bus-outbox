<?php

namespace Micromus\KafkaBusOutbox\Interfaces\Producers;

interface OutboxProducerStreamInterface
{
    public function process(): void;

    public function forceStop(): void;
}
