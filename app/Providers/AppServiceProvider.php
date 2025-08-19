<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Embeddings
use App\Services\Embeddings\EmbeddingService;
use App\Services\Embeddings\OpenAIEmbeddingService;
use App\Services\Embeddings\HFEmbeddingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind del servicio de embeddings según el provider configurado
        $this->app->bind(EmbeddingService::class, function () {
            $provider = config('embeddings.provider', 'huggingface');

            return match ($provider) {
                'openai'      => new OpenAIEmbeddingService(),
                'huggingface' => new HFEmbeddingService(),
                default       => new HFEmbeddingService(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        
    }
}
