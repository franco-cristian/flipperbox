<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
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
     * Genera una respuesta manteniendo el contexto de la conversación
     */
    public function answer(string $question, string $context, array $reservationInfo = [], ?string $conversationId = null): string
    {
        if (empty($this->apiKey)) {
            return 'Lo siento, hay un problema con mi configuración (Falta API Key).';
        }

        // Obtener historial de conversación si existe
        $conversationHistory = [];
        if ($conversationId) {
            $conversationHistory = Cache::get("chat_history_{$conversationId}", []);
        }

        // Obtener fecha actual
        $currentDate = now()->format('d/m/Y');
        $currentDay = now()->dayName;

        // Construcción del Prompt del Sistema mejorado
        $systemPrompt = $this->buildSystemPrompt($context, $reservationInfo, $currentDate, $currentDay);

        // Construir mensajes con historial
        $messages = $this->buildMessages($systemPrompt, $conversationHistory, $question);

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post($this->apiUrl, [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => 0.3,
                    'max_tokens' => 800,
                ]);

            if ($response->successful()) {
                $answer = $response->json('choices.0.message.content') ?? 'No pude generar una respuesta.';

                // Post-procesamiento para limpiar cualquier markdown residual
                $answer = $this->cleanResponse($answer);

                // Guardar historial de conversación
                if ($conversationId) {
                    $this->saveConversationHistory($conversationId, $conversationHistory, $question, $answer);
                }

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
     * Construye el prompt del sistema con contexto mejorado
     */
    private function buildSystemPrompt(string $context, array $reservationInfo, string $currentDate, string $currentDay): string
    {
        $systemPrompt = <<<EOT
Eres 'FlipperBot', el asistente virtual experto del taller mecánico "Flipper Servicios".

INFORMACIÓN CRÍTICA SOBRE FECHA:
- Fecha actual: {$currentDate}
- Día de la semana: {$currentDay}
- Siempre debes referirte a la fecha actual correctamente en tus respuestas.

OBJETIVO PRINCIPAL:
Ayudar a los clientes respondiendo preguntas sobre productos, precios, stock y reservaciones manteniendo una conversación natural y fluida.

HABILIDADES DE CONVERSACIÓN:
1. Mantén el contexto de lo que se habló anteriormente
2. Si el usuario hace preguntas ambiguas, pide clarificación amablemente
3. Reconoce cuando el usuario quiere cambiar de tema
4. Maneja transiciones suaves entre temas
5. Recuerda detalles mencionados anteriormente en la conversación

SERVICIOS DISPONIBLES:
- Service en general
- Mecánica en general  
- Cambio de aceite
- Tren delantero
- Frenos
- Auxilios

REGLAS ESTRICTAS:
1. Tono profesional, amable y conversacional
2. NUNCA uses formato markdown
3. Para precios: "571.73 pesos" NO "$571.73"
4. Sé conciso pero útil
5. Si la información NO está en el CONTEXTO, di amablemente que no tienes esa información
6. NUNCA inventes datos sobre precios, stock o disponibilidad
7. Responde siempre en Español
8. Para derivar a humano: "Claro, puedo conectarte con uno de nuestros especialistas. ¿Podrías contarme brevemente sobre qué necesitas ayuda para que pueda dirigirte a la persona adecuada?"

INSTRUCCIONES ESPECÍFICAS:
- Para productos: nombre, precio en pesos, stock, breve descripción
- Para servicios: explica claramente qué hacen
- Para reservas: usa la información de disponibilidad proporcionada
- Siempre verifica las fechas contra la fecha actual

CONTEXTO DE PRODUCTOS Y STOCK:
{$context}

EOT;

        // Agregar información de reservaciones si está disponible
        if (! empty($reservationInfo)) {
            $systemPrompt .= "\nINFORMACIÓN DE RESERVACIONES ACTUAL:\n";
            foreach ($reservationInfo as $info) {
                $systemPrompt .= "- {$info}\n";
            }
            $systemPrompt .= "\nLos clientes pueden hacer reservas a través del sistema de reservas en nuestro sitio web.\n";
        }

        return $systemPrompt;
    }

    /**
     * Construye el array de mensajes con historial de conversación
     */
    private function buildMessages(string $systemPrompt, array $conversationHistory, string $currentQuestion): array
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Agregar historial de conversación (máximo 6 mensajes para no exceder tokens)
        $recentHistory = array_slice($conversationHistory, -6);
        foreach ($recentHistory as $message) {
            $messages[] = $message;
        }

        // Agregar la pregunta actual
        $messages[] = ['role' => 'user', 'content' => $currentQuestion];

        return $messages;
    }

    /**
     * Guarda el historial de la conversación
     */
    private function saveConversationHistory(string $conversationId, array &$history, string $question, string $answer): void
    {
        // Agregar nueva interacción al historial
        $history[] = ['role' => 'user', 'content' => $question];
        $history[] = ['role' => 'assistant', 'content' => $answer];

        // Mantener máximo 10 mensajes en el historial
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }

        // Guardar en cache por 2 horas
        Cache::put("chat_history_{$conversationId}", $history, 7200);
    }

    /**
     * Limpia la respuesta de cualquier formato markdown residual y formatea precios
     */
    private function cleanResponse(string $response): string
    {
        // Eliminar markdown común
        $clean = preg_replace('/\*\*(.*?)\*\*/', '$1', $response);
        $clean = preg_replace('/\*(.*?)\*/', '$1', $clean);
        $clean = preg_replace('/#+\s?(.*)/', '$1', $clean);
        $clean = preg_replace('/`(.*?)`/', '$1', $clean);

        // Reemplazar símbolos de dólar por "pesos"
        $clean = preg_replace('/\$\s?(\d+\.?\d*)/', '$1 pesos', $clean);

        // Formatear precios para mejor legibilidad
        $clean = $this->formatPrices($clean);

        // Limpiar espacios múltiples
        $clean = preg_replace('/\s+/', ' ', $clean);

        return trim($clean);
    }

    /**
     * Formatea los precios para hacerlos más legibles
     */
    private function formatPrices(string $text): string
    {
        // Patrón para encontrar precios en formato decimal
        return preg_replace_callback('/(\d+)\.(\d+)\s*pesos/', function ($matches) {
            $parteEntera = (int) $matches[1];
            $centavos = (int) $matches[2];

            // Formatear parte entera con separadores de miles
            $parteEnteraFormateada = number_format($parteEntera, 0, '', '.');

            // Si los centavos son 00, mostrar solo la parte entera
            if ($centavos === 0) {
                return $parteEnteraFormateada.' pesos';
            }

            // Si tiene centavos, mostrar ambos
            return $parteEnteraFormateada.' pesos con '.$centavos.' centavos';
        }, $text);
    }

    /**
     * Limpia el historial de conversación
     */
    public function clearConversationHistory(string $conversationId): void
    {
        Cache::forget("chat_history_{$conversationId}");
    }
}
