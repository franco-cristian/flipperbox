<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    private string $apiKey;

    private string $model = 'BAAI/bge-base-en-v1.5';

    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.huggingface.api_key');

        $this->apiUrl = "https://router.huggingface.co/hf-inference/models/{$this->model}";
    }

    /**
     * Genera el embedding para un texto dado.
     * Retorna null si falla.
     */
    public function generate(string $text): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('EmbeddingService: La API Key de Hugging Face no está configurada.');

            return null;
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->retry(3, 100)
                ->post($this->apiUrl, [
                    'inputs' => $text,
                    'options' => ['wait_for_model' => true], // Esperar si el modelo está "dormido"
                ]);

            if ($response->successful()) {
                $embedding = $response->json();

                // Validación básica de que recibimos un vector
                if (is_array($embedding) && count($embedding) > 0 && is_numeric($embedding[0])) {
                    return $embedding;
                }

                // A veces la API devuelve una lista de embeddings si el input es un array
                if (isset($embedding[0]) && is_array($embedding[0])) {
                    return $embedding[0];
                }
            }

            Log::error('EmbeddingService Error: '.$response->status().' - '.$response->body());

            return null;

        } catch (\Exception $e) {
            Log::error('EmbeddingService Exception: '.$e->getMessage());

            return null;
        }
    }
}
