# Prueba Técnica AgentIA

API REST construida en **Laravel 10/11** que integra procesamiento de documentos PDF con ** PostgreSQL (pgvector)** para consultas semánticas.

##  Funcionalidades principales
-  **Gestión de PDFs**: subida de archivos y extracción de texto por página.  
-  **Embeddings semánticos**: generación con OpenAI o HuggingFace y almacenamiento en PostgreSQL con **pgvector**.  
-  **Consultas por similitud**: búsqueda semántica de texto usando distancia coseno.  
-  **Internacionalización (i18n)**: detección de idioma mediante `Accept-Language` en middleware global.  
-  **Autenticación JWT**: registro/login de usuarios y protección de rutas.  
- ⚙ **Procesamiento asíncrono**: construcción de embeddings en segundo plano con colas (Redis).  
-  **Swagger / OpenAPI**: documentación interactiva de la API.  

##  Valor añadido
-  **Subida de documentos por URL**.  
-  **Feedback de resultados**: rating y comentarios de las consultas.  
-  **Historial de consultas** para trazabilidad.  
-  **Seguridad**: rate limiting, validación estricta de PDFs, deduplicación por checksum.  
-  **Índices vectoriales** para acelerar búsquedas (IVFFLAT/HNSW).  

##  Stack tecnológico
- **Laravel 10/11**  
- **PostgreSQL + pgvector** (Docker)  
- **Redis** (para colas asíncronas)  
- **OpenAI / HuggingFace** (servicios de embeddings)  
- **JWT-Auth**  
- **L5-Swagger** (documentación API)  

##  Endpoints principales
- `POST /api/auth/register` → Registro de usuario  
- `POST /api/auth/login` → Login y obtención de JWT  
- `POST /api/documents/upload` → Subir PDF (JWT requerido)  
- `POST /api/documents/upload-url` → Subir PDF desde URL (JWT requerido)  
- `POST /api/query` → Consulta semántica  
- `POST /api/query/{id}/feedback` → Feedback de calidad (JWT requerido)  

## ⚙ Instalación rápida
1. Clonar repositorio  
   ```bash
   git clone https://github.com/<tu_usuario>/prueba_tecnica_agentia.git
   cd prueba_tecnica_agentia
