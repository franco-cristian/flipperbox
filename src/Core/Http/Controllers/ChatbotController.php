<?php

namespace FlipperBox\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use App\Services\EmbeddingService;
use FlipperBox\Inventory\Models\Product;
use FlipperBox\Scheduling\Models\DailyCapacity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function __construct(
        protected EmbeddingService $embeddingService,
        protected ChatbotService $chatbotService
    ) {}

    public function handle(Request $request)
    {
        // 1. Validar entrada
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $question = $request->input('message');

        // 2. Generar Embedding de la pregunta
        $questionEmbedding = $this->embeddingService->generate($question);

        if (! $questionEmbedding) {
            return response()->json([
                'response' => 'Lo siento, no pude entender tu pregunta en este momento (Error de vectorización).',
            ]);
        }

        // 3. Búsqueda Vectorial (RAG) - Productos
        $vectorString = json_encode($questionEmbedding);

        $similarProducts = Product::query()
            ->select('name', 'description', 'price', 'current_stock')
            ->selectRaw("embedding <=> '{$vectorString}' as distance")
            ->orderBy('distance', 'asc')
            ->limit(4)
            ->get();

        // 4. Construir el Contexto para la IA
        $context = '';
        foreach ($similarProducts as $product) {
            // Formatear precio correctamente
            $precioFormateado = $this->formatPriceForContext($product->price);

            // Formatear para que sea más legible en el contexto
            $context .= "Producto: {$product->name}. Precio: {$precioFormateado}. Stock: {$product->current_stock}. Descripción: {$product->description}. ";
        }

        if ($similarProducts->isEmpty()) {
            $context = 'No se encontraron productos relevantes en el inventario.';
        }

        // 5. DETECCIÓN DE INTENCIÓN MEJORADA
        $reservationInfo = [];
        $isReservationQuestion = $this->detectReservationIntent($question);

        if ($isReservationQuestion) {
            $reservationInfo = $this->getReservationAvailability();
        }

        // 6. Obtener conversationId (usar la sesión o generar una)
        $conversationId = $this->getConversationId($request);

        // 7. Generar Respuesta con LLM (con historial de conversación)
        $answer = $this->chatbotService->answer($question, $context, $reservationInfo, $conversationId);

        return response()->json([
            'response' => $answer,
            'conversation_id' => $conversationId, // Opcional: para debugging
        ]);
    }

    /**
     * Obtiene o genera un ID de conversación único
     */
    private function getConversationId(Request $request): string
    {
        // Intentar obtener de la sesión
        if ($request->hasSession()) {
            $conversationId = $request->session()->get('chatbot_conversation_id');
            if (! $conversationId) {
                $conversationId = uniqid('chat_', true);
                $request->session()->put('chatbot_conversation_id', $conversationId);
            }

            return $conversationId;
        }

        // Si no hay sesión, usar el IP + User-Agent como fallback
        $userIp = $request->ip() ?? 'unknown';
        $userAgent = $request->userAgent() ?? 'unknown';

        return 'chat_'.md5($userIp.$userAgent);
    }

    /**
     * Endpoint para limpiar el historial de conversación
     */
    public function clearHistory(Request $request)
    {
        $conversationId = $this->getConversationId($request);
        $this->chatbotService->clearConversationHistory($conversationId);

        return response()->json([
            'success' => true,
            'message' => 'Historial de conversación limpiado',
        ]);
    }

    /**
     * Formatea el precio para el contexto del LLM
     */
    private function formatPriceForContext(float $price): string
    {
        $parteEntera = (int) floor($price);
        $centavos = (int) round(($price - $parteEntera) * 100);

        $parteEnteraFormateada = number_format($parteEntera, 0, '', '.');

        if ($centavos === 0) {
            return $parteEnteraFormateada.' pesos';
        }

        return $parteEnteraFormateada.' pesos con '.$centavos.' centavos';
    }

    /**
     * Detecta si la pregunta está relacionada con reservaciones (MEJORADO)
     */
    private function detectReservationIntent(string $question): bool
    {
        $reservationKeywords = [
            'reserva', 'reservar', 'reservación', 'cita', 'agendar', 'turno',
            'cupo', 'disponibilidad', 'fecha disponible', 'horario', 'día disponible',
            'programar', 'citación', 'booking', 'schedule', 'appointment',
            'quiero reservar', 'necesito turno', 'hay cupo', 'tienen horarios',
            'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo',
            'mañana', 'pasado mañana', 'esta semana', 'próxima semana',
        ];

        $question = mb_strtolower($question);

        foreach ($reservationKeywords as $keyword) {
            if (str_contains($question, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtiene información de disponibilidad de reservaciones
     */
    private function getReservationAvailability(): array
    {
        $info = [];

        try {
            // Obtener disponibilidad de los próximos 7 días
            $availabilities = DailyCapacity::where('date', '>=', now()->toDateString())
                ->where('date', '<=', now()->addDays(7)->toDateString())
                ->orderBy('date')
                ->get();

            foreach ($availabilities as $availability) {
                $availableSlots = $availability->total_slots - $availability->booked_slots;
                $dateFormatted = $availability->date->format('d/m/Y');
                $dayName = $availability->date->dayName;

                if ($availableSlots > 0) {
                    $info[] = "{$dayName} {$dateFormatted}: {$availableSlots} cupos disponibles";
                } else {
                    $info[] = "{$dayName} {$dateFormatted}: Sin cupos disponibles";
                }
            }

            if (empty($info)) {
                $info[] = 'No hay cupos disponibles en los próximos 7 días.';
            } else {
                // Agregar instrucciones generales
                $info[] = 'Para hacer una reserva, visita nuestra sección de reservas en el sitio web y selecciona tu vehículo y la fecha deseada.';
            }

        } catch (\Exception $e) {
            Log::error('Error obteniendo disponibilidad: '.$e->getMessage());
            $info[] = 'No pude consultar la disponibilidad en este momento. Por favor, visita la sección de reservas para ver los horarios disponibles.';
        }

        return $info;
    }
}
