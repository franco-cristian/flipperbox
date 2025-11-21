<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    private string $apiKey;

    private string $model;

    private string $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->model = config('services.groq.model');
    }

    /**
     * Genera una respuesta basada en la pregunta y el contexto encontrado.
     */
    public function answer(string $question, string $context, array $reservationInfo = []): string
    {
        if (empty($this->apiKey)) {
            return 'Lo siento, hay un problema con mi configuración (Falta API Key).';
        }

        // Construcción del Prompt del Sistema - respuestas naturales
        $systemPrompt = <<<EOT
        Eres 'FlipperBot', el asistente virtual experto del taller mecánico "Flipper Servicios".
        
        Tu objetivo es ayudar a los clientes respondiendo preguntas sobre productos, precios, stock y reservaciones.

        INFORMACIÓN SOBRE SERVICIOS:
        - Service en general
        - Mecánica en general  
        - Cambio de aceite
        - Tren delantero
        - Frenos
        - Auxilios

        REGLAS ESTRICTAS DE FORMATO:
        1. Usa un tono profesional, amable y conversacional.
        2. NUNCA uses formato markdown como **asteriscos** o # encabezados.
        3. Para precios, siempre di "pesos" en lugar del símbolo $. Ejemplo: "571.73 pesos" en lugar de "$571.73".
        4. Para listas, usa viñetas naturales con • o números, pero evita el formato markdown.
        5. Sé conciso pero útil, como si estuvieras conversando con un cliente.
        6. Si la información NO está en el CONTEXTO, di amablemente que no tienes esa información específica.
        7. NUNCA inventes datos sobre precios, stock o disponibilidad.
        8. Responde siempre en Español.
        9. Evita caracteres especiales que suenen mal en audio como asteriscos, almohadillas, etc.
        10. Las respuestas deben ser fáciles de leer y escuchar.

        INSTRUCCIONES ESPECÍFICAS:
        - Para productos: Menciona nombre, precio en pesos, stock disponible y una breve descripción.
        - Para servicios: Explica claramente qué hacen.
        - Para reservas: Proporciona información de disponibilidad si está disponible.

        CONTEXTO DE PRODUCTOS Y STOCK:
        {$context}

        EOT;

        // Agregar información de reservaciones si está disponible
        if (! empty($reservationInfo)) {
            $systemPrompt .= "\nINFORMACIÓN DE RESERVACIONES DISPONIBLE:\n";
            foreach ($reservationInfo as $info) {
                $systemPrompt .= "- {$info}\n";
            }

            $systemPrompt .= "\nLos clientes pueden hacer reservas a través del sistema de reservas en nuestro sitio web. Deben seleccionar su vehículo y la fecha deseada.\n";
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $question],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 600,
                ]);

            if ($response->successful()) {
                $answer = $response->json('choices.0.message.content') ?? 'No pude generar una respuesta.';

                // Post-procesamiento para limpiar cualquier markdown residual
                $answer = $this->cleanResponse($answer);

                return $answer;
            }

            Log::error('Groq API Error: '.$response->body());

            return 'Tuve un problema técnico al procesar tu respuesta. Por favor intenta de nuevo.';

        } catch (\Exception $e) {
            Log::error('ChatbotService Exception: '.$e->getMessage());

            return 'Ocurrió un error de conexión. Por favor, intenta de nuevo.';
        }
    }

    /**
     * Limpia la respuesta de cualquier formato markdown residual
     */
    private function cleanResponse(string $response): string
    {
        // Eliminar markdown común
        $clean = preg_replace('/\*\*(.*?)\*\*/', '$1', $response); // **texto** → texto
        $clean = preg_replace('/\*(.*?)\*/', '$1', $clean); // *texto* → texto
        $clean = preg_replace('/#+\s?(.*)/', '$1', $clean); // # título → título
        $clean = preg_replace('/`(.*?)`/', '$1', $clean); // `código` → código

        // Reemplazar símbolos de dólar por "pesos"
        $clean = preg_replace('/\$\s?(\d+\.?\d*)/', '$1 pesos', $clean);

        // Limpiar espacios múltiples
        $clean = preg_replace('/\s+/', ' ', $clean);

        return trim($clean);
    }
}
