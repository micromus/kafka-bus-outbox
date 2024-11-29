<?php

namespace Micromus\KafkaBusOutbox\Interfaces\Publishers;

interface PublisherStreamInterface
{
    public function process(): void;

    public function forceStop(): void;
}
