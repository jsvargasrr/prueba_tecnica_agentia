<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // nombre lógico
            $table->string('original_filename');
            $table->string('mime', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->string('storage_path');                 // Storage::path
            $table->unsignedInteger('page_count')->default(0);
            $table->string('language', 8)->nullable();      // es, en, pt...
            $table->string('checksum_md5', 32)->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('document_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->unsignedInteger('page_number');
            $table->longText('content')->nullable();
            // Vector pgvector
            $table->vector('embedding', config('embeddings.dim')); // requiere pgvector/laravel
            $table->timestamps();

            $table->unique(['document_id','page_number']);
        });

        // Historial de consultas y resultados (valor añadido)
        Schema::create('queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('question');
            $table->unsignedInteger('top_k')->default(5);
            $table->string('response_lang', 8)->nullable();
            $table->timestamps();
        });

        Schema::create('query_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('query_id')->constrained('queries')->cascadeOnDelete();
            $table->foreignId('document_page_id')->constrained('document_pages')->cascadeOnDelete();
            $table->float('score'); // distancia coseno (menor = mejor)
            $table->unsignedInteger('rank');
            $table->timestamps();

            $table->unique(['query_id','document_page_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('query_results');
        Schema::dropIfExists('queries');
        Schema::dropIfExists('document_pages');
        Schema::dropIfExists('documents');
    }
};
