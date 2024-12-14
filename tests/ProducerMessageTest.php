<?php

use Micromus\KafkaBus\Bus;
use Micromus\KafkaBus\Bus\Publishers\Router\PublisherRoutes;
use Micromus\KafkaBus\Connections\Registry\ConnectionRegistry;
use Micromus\KafkaBus\Connections\Registry\DriverRegistry;
use Micromus\KafkaBus\Consumers\ConsumerStreamFactory;
use Micromus\KafkaBus\Consumers\Messages\ConsumerMessageHandlerFactory;
use Micromus\KafkaBus\Consumers\Router\ConsumerRouterFactory;
use Micromus\KafkaBus\Pipelines\PipelineFactory;
use Micromus\KafkaBus\Producers\ProducerStreamFactory;
use Micromus\KafkaBus\Support\Resolvers\NativeResolver;
use Micromus\KafkaBus\Testing\Connections\ConnectionFaker;
use Micromus\KafkaBus\Testing\Messages\ProducerMessageFaker;
use Micromus\KafkaBus\Topics\Topic;
use Micromus\KafkaBus\Topics\TopicRegistry;
use Micromus\KafkaBus\Uuid\RandomUuidGenerator;
use Micromus\KafkaBusOutbox\OutboxKafkaConnection;
use Micromus\KafkaBusOutbox\Publishers\Producers\ProducerManager;
use Micromus\KafkaBusOutbox\Publishers\Publisher;
use Micromus\KafkaBusOutbox\Testing\ArrayProducerMessageRepository;

it('can produce message', function () {
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

    $connectionFaker = new ConnectionFaker($topicRegistry);

    $driverRegistry->add('faker', function () use ($connectionFaker) {
        return $connectionFaker;
    });

    $routes = (new PublisherRoutes())
        ->add(
            new Bus\Publishers\Router\Route(
                messageClass: ProducerMessageFaker::class,
                topicKey: 'products',
                options: new Bus\Publishers\Router\Options(['foo' => 'bar'])
            )
        );

    $bus = new Bus(
        new Bus\ThreadRegistry(
            $connectionRegistry,
            new Bus\Publishers\PublisherFactory(
                new ProducerStreamFactory(new PipelineFactory(new NativeResolver())),
                $topicRegistry,
                $routes
            ),
            new Bus\Listeners\ListenerFactory(
                new ConsumerStreamFactory(
                    new ConsumerMessageHandlerFactory(
                        new PipelineFactory(new NativeResolver()),
                        new ConsumerRouterFactory(
                            new NativeResolver(),
                            new PipelineFactory(new NativeResolver()),
                            $topicRegistry
                        )
                    )
                )
            )
        ),
        'outbox'
    );

    $bus->publish([new ProducerMessageFaker('test-message', ['foo' => 'bar'], 5)]);

    expect($connectionFaker->publishedMessages)
        ->toBeEmpty()
        ->and($producerMessageRepository->outboxProducerMessages)
        ->toHaveCount(1)
        ->and($producerMessageRepository->outboxProducerMessages[0])
        ->toHaveProperty('topicName', 'production.fact.products.1')
        ->toHaveProperty('connectionName', 'kafka')
        ->toHaveProperty('additionalOptions', ['foo' => 'bar'])
        ->and($producerMessageRepository->outboxProducerMessages[0]->message)
        ->toHaveProperty('payload', 'test-message')
        ->toHaveProperty('headers', ['foo' => 'bar'])
        ->toHaveProperty('partition', 5);

    $publisher = new Publisher($producerMessageRepository, new ProducerManager($connectionRegistry));
    $publisher->publish($producerMessageRepository->get());

    expect($producerMessageRepository->outboxProducerMessages)
        ->toBeEmpty()
        ->and($connectionFaker->publishedMessages)
        ->toHaveCount(1)
        ->and($connectionFaker->publishedMessages['production.fact.products.1'][0])
        ->toHaveProperty('payload', 'test-message')
        ->toHaveProperty('headers', ['foo' => 'bar'])
        ->toHaveProperty('partition', 5);
});
