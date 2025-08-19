<?php

uses(Tests\TestCase::class)->in('Feature');

beforeEach(function () {
    // Bind del servicio de embeddings al fake
    $this->app->bind(\App\Services\Embeddings\EmbeddingService::class, \Tests\Fakes\FakeEmbeddingService::class);
    config()->set('embeddings.dim', 8);

    // Evitar conexiones externas en tests
    config()->set('queue.default', 'sync');        // jobs se ejecutan inline
    config()->set('cache.default', 'file');
    config()->set('session.driver', 'array');
});
