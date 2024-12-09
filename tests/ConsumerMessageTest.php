<?php

use Micromus\KafkaBus\Bus;
use Micromus\KafkaBus\Connections\Registry\ConnectionRegistry;
use Micromus\KafkaBus\Connections\Registry\DriverRegistry;
use Micromus\KafkaBus\Consumers\ConsumerStreamFactory;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMessageHandlerFactory;
use Micromus\KafkaBus\Consumers\Router\ConsumerRouterFactory;
use Micromus\KafkaBus\Messages\MessagePipelineFactory;
use Micromus\KafkaBus\Producers\ProducerStreamFactory;
use Micromus\KafkaBus\Support\Resolvers\NativeResolver;
use Micromus\KafkaBus\Testing\Connections\ConnectionFaker;
use Micromus\KafkaBus\Testing\Messages\VoidConsumerHandlerFaker;
use Micromus\KafkaBus\Topics\Topic;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBus\Uuid\RandomUuidGenerator;
use Micromus\KafkaBusOutbox\OutboxKafkaConnection;
use Micromus\KafkaBusOutbox\Testing\ArrayProducerMessageRepository;
use RdKafka\Message;

it('can consume messages', function () {
    $topicRegistry = (new TopicRegistry())
        ->add(new Topic('production.fact.products.1', 'products'));

    $driverRegistry = new DriverRegistry();

    $connectionRegistry = new ConnectionRegistry($driverRegistry, [
        'kafka' => ['driver' => 'faker', 'options' => []],
        'outbox' => ['driver' => 'outbox', 'options' => ['connection_for' => 'kafka']],
    ]);

    $producerMessageRepository = new ArrayProducerMessageRepository();

    $driverRegistry->add('outbox', function (array $options) use ($connectionRegistry, $producerMessageRepository) {
        return new OutboxKafkaConnection(
            producerMessageRepository: $producerMessageRepository,
            connectionRegistry: $connectionRegistry,
            uuidGenerator: new RandomUuidGenerator(),
            sourceConnectionName: $options['connection_for']
        );
    });

    $message = new Message();
    $message->payload = 'test-message';
    $message->headers = ['foo' => 'bar'];

    $connectionFaker = new ConnectionFaker($topicRegistry);
    $connectionFaker->addMessage('products', $message);

    $driverRegistry->add('faker', function () use ($connectionFaker) {
        return $connectionFaker;
    });

    $workerRegistry = (new Bus\Listeners\Workers\WorkerRegistry())
        ->add(
            new Bus\Listeners\Workers\Worker(
                'default-listener',
                (new Bus\Listeners\Workers\WorkerRoutes())
                    ->add(new Bus\Listeners\Workers\Route('products', VoidConsumerHandlerFaker::class))
            )
        );

    $bus = new Bus(
        new Bus\ThreadRegistry(
            $connectionRegistry,
            new Bus\Publishers\PublisherFactory(
                new ProducerStreamFactory(new MessagePipelineFactory(new NativeResolver())),
                $topicRegistry
            ),
            new Bus\Listeners\ListenerFactory(
                new ConsumerStreamFactory(
                    new ConsumerMessageHandlerFactory(
                        new MessagePipelineFactory(new NativeResolver()),
                        new ConsumerRouterFactory(new NativeResolver(), $topicRegistry)
                    )
                ),
                $workerRegistry
            )
        ),
        'outbox'
    );

    $bus->listener('default-listener')
        ->listen();

    expect($connectionFaker->committedMessages)
        ->toHaveCount(1)
        ->and($connectionFaker->committedMessages['production.fact.products.1'][0])
        ->toHaveProperty('payload', 'test-message')
        ->toHaveProperty('headers', ['foo' => 'bar']);
});
