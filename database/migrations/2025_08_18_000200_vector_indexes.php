<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // IMPORTANTE: IVFFLAT requiere ANALYZE previo con suficientes filas.
        DB::statement("
            CREATE INDEX IF NOT EXISTS document_pages_embedding_ivfflat
            ON document_pages USING ivfflat (embedding vector_cosine_ops)
            WITH (lists = 100);
        ");
    }
    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS document_pages_embedding_ivfflat;");
        // DB::statement("DROP INDEX IF EXISTS document_pages_embedding_hnsw;");
    }
};
