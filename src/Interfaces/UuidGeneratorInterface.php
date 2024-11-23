<?php

namespace Micromus\KafkaBusOutbox\Interfaces;

use Ramsey\Uuid\UuidInterface;

interface UuidGeneratorInterface
{
    public function generate(): UuidInterface;
}
