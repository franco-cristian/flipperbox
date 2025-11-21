<script setup>
import { ref, nextTick, computed, onMounted } from 'vue';
import axios from 'axios';
import { useAudioSynthesis } from '@/Composables/useAudioSynthesis';

// --- ESTADO DEL CHAT ---
const isOpen = ref(false);
const messages = ref([
    {
        id: 1,
        role: 'assistant',
        content:
            '¡Hola! Soy FlipperBot 🤖, tu asistente del taller Flipper Servicios. Puedo ayudarte con información sobre productos, precios, stock disponible y también con reservaciones. ¿En qué puedo asistirte hoy?',
        displayedContent: '',
        isTyping: false,
        fullyDisplayed: true,
    },
]);
const userInput = ref('');
const isLoading = ref(false);
const messagesContainer = ref(null);

// --- COMPOSABLE DE AUDIO ---
const { isSpeaking, isPaused, isMuted, volume, speak, pause, resume, cancel, toggleMute, setVolume } =
    useAudioSynthesis();

// --- RECONOCIMIENTO DE VOZ (STT) COMPLETO ---
const isListening = ref(false);
const speechRecognitionSupported = ref(false);
let recognition = null;

// Inicialización completa del reconocimiento de voz
const initializeSpeechRecognition = () => {
    if (typeof window === 'undefined') return;

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        console.warn('Speech Recognition no soportado en este navegador');
        return;
    }

    speechRecognitionSupported.value = true;
    recognition = new SpeechRecognition();
    recognition.lang = 'es-ES';
    recognition.continuous = false;
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onstart = () => {
        isListening.value = true;
    };

    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        userInput.value = transcript;
        // Pequeño delay para que el usuario vea que se capturó su voz
        setTimeout(() => {
            sendMessage();
        }, 500);
    };

    recognition.onend = () => {
        isListening.value = false;
    };

    recognition.onerror = (event) => {
        console.error('Error de reconocimiento de voz:', event.error);
        isListening.value = false;

        // Manejo de errores específicos
        if (event.error === 'not-allowed') {
            addAssistantMessage(
                'Permiso de micrófono denegado. Por favor, permite el acceso al micrófono en tu navegador para usar el reconocimiento de voz.'
            );
        } else if (event.error === 'no-speech') {
            addAssistantMessage('No se detectó voz. Por favor, intenta de nuevo.');
        } else if (event.error === 'audio-capture') {
            addAssistantMessage('No se pudo acceder al micrófono. Verifica que esté conectado y tenga permisos.');
        }
    };
};

// --- EFECTO TYPEWRITER COMPLETO ---
const typewriterSpeed = 20; // ms por carácter

const startTypewriterEffect = (messageId, fullContent) => {
    const messageIndex = messages.value.findIndex((msg) => msg.id === messageId);
    if (messageIndex === -1) return;

    messages.value[messageIndex].isTyping = true;
    messages.value[messageIndex].displayedContent = '';
    messages.value[messageIndex].fullyDisplayed = false;

    let index = 0;
    const typeInterval = setInterval(() => {
        if (index < fullContent.length) {
            messages.value[messageIndex].displayedContent += fullContent.charAt(index);
            index++;
            scrollToBottom();
        } else {
            clearInterval(typeInterval);
            messages.value[messageIndex].isTyping = false;
            messages.value[messageIndex].fullyDisplayed = true;
        }
    }, typewriterSpeed);
};

// --- FUNCIONES AUXILIARES COMPLETAS ---
const addAssistantMessage = (content) => {
    const messageId = Date.now() + 1;
    const newMessage = {
        id: messageId,
        role: 'assistant',
        content: content,
        displayedContent: '',
        isTyping: true,
        fullyDisplayed: false,
    };

    messages.value.push(newMessage);
    scrollToBottom();

    // ✅ AUDIO INMEDIATO - Comienza al mismo tiempo que el typewriter
    if (!isMuted.value) {
        speak(content);
    }

    // ✅ TYPEWRITER SIMULTÁNEO
    startTypewriterEffect(messageId, content);

    return messageId;
};

const addUserMessage = (content) => {
    const newMessage = {
        id: Date.now(),
        role: 'user',
        content: content,
        displayedContent: content,
        isTyping: false,
        fullyDisplayed: true,
    };

    messages.value.push(newMessage);
    scrollToBottom();
};

const toggleMicrophone = () => {
    if (!speechRecognitionSupported.value || !recognition) {
        addAssistantMessage(
            'Tu navegador no soporta reconocimiento de voz. Te recomendamos usar Chrome, Edge o Safari para esta función.'
        );
        return;
    }

    if (isListening.value) {
        recognition.stop();
    } else {
        // Detener cualquier audio actual antes de escuchar
        if (isSpeaking.value || isPaused.value) {
            cancel();
        }

        try {
            recognition.start();
        } catch (error) {
            console.error('Error al iniciar reconocimiento de voz:', error);
            isListening.value = false;
            addAssistantMessage('Error al iniciar el reconocimiento de voz. Por favor, intenta de nuevo.');
        }
    }
};

// --- LÓGICA DEL CHAT COMPLETA ---
const toggleChat = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        scrollToBottom();
    }
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            const scrollHeight = messagesContainer.value.scrollHeight;
            const height = messagesContainer.value.clientHeight;
            const maxScrollTop = scrollHeight - height;
            messagesContainer.value.scrollTop = maxScrollTop > 0 ? maxScrollTop : 0;
        }
    });
};

const sendMessage = async () => {
    const trimmedInput = userInput.value.trim();
    if (!trimmedInput || isLoading.value) return;

    const question = trimmedInput;

    // 1. Agregar mensaje del usuario
    addUserMessage(question);
    userInput.value = '';
    isLoading.value = true;
    scrollToBottom();

    try {
        // 2. Llamada al Backend (RAG)
        const response = await axios.post(
            route('chatbot.ask'),
            {
                message: question,
            },
            {
                timeout: 30000,
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }
        );

        const answer = response.data.response;

        // 3. Agregar respuesta del bot (audio + typewriter simultáneos)
        addAssistantMessage(answer);
    } catch (error) {
        console.error('Error en la comunicación con el chatbot:', error);

        let errorMessage = 'Lo siento, tuve un error de conexión. Intenta de nuevo.';

        if (error.code === 'ECONNABORTED' || error.response?.status === 408) {
            errorMessage = 'La solicitud está tomando demasiado tiempo. Por favor, intenta de nuevo.';
        } else if (error.response?.status >= 500) {
            errorMessage = 'Error del servidor. Por favor, intenta más tarde.';
        } else if (error.response?.status === 429) {
            errorMessage = 'Demasiadas solicitudes. Por favor, espera un momento antes de intentar de nuevo.';
        }

        addAssistantMessage(errorMessage);
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};

// Computed para mostrar el contenido correcto
const displayMessages = computed(() => {
    return messages.value.map((msg) => ({
        ...msg,
        displayContent: msg.isTyping ? msg.displayedContent : msg.content,
    }));
});

// Manejar la tecla Enter para enviar mensaje
const handleKeyPress = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
};

// Inicializar cuando el componente se monta
onMounted(() => {
    initializeSpeechRecognition();

    // Inicializar el mensaje de bienvenida
    if (messages.value[0]) {
        messages.value[0].displayedContent = messages.value[0].content;
        messages.value[0].fullyDisplayed = true;
    }
});
</script>

<template>
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end space-y-4">
        <!-- VENTANA DEL CHAT -->
        <transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95"
        >
            <div
                v-show="isOpen"
                class="w-80 md:w-96 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden h-[500px]"
            >
                <!-- CABECERA Y CONTROLES DE AUDIO -->
                <div class="bg-gradient-to-r from-cyan-600 to-blue-600 p-4">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-white font-bold flex items-center">
                            <span class="text-xl mr-2">🤖</span> FlipperBot
                        </h3>
                        <button
                            class="text-white/80 hover:text-white transition-colors duration-200 p-1 rounded-full hover:bg-white/10"
                            aria-label="Cerrar chat"
                            @click="toggleChat"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                ></path>
                            </svg>
                        </button>
                    </div>

                    <!-- PANEL DE CONTROL -->
                    <div class="bg-black/20 rounded-lg p-2 flex items-center justify-between text-white">
                        <div class="flex space-x-2">
                            <!-- Play/Resume -->
                            <button
                                v-if="isPaused"
                                class="hover:text-cyan-200 transition-colors duration-200 p-1 rounded hover:bg-white/10"
                                title="Reanudar audio"
                                :disabled="!isPaused"
                                aria-label="Reanudar audio"
                                @click="resume"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                            </button>
                            <!-- Pause -->
                            <button
                                v-else
                                class="hover:text-cyan-200 transition-colors duration-200 p-1 rounded hover:bg-white/10"
                                :class="{ 'opacity-50 cursor-not-allowed': !isSpeaking }"
                                :disabled="!isSpeaking"
                                title="Pausar audio"
                                aria-label="Pausar audio"
                                @click="pause"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                            </button>
                            <!-- Stop -->
                            <button
                                class="hover:text-red-300 transition-colors duration-200 p-1 rounded hover:bg-white/10"
                                :class="{ 'opacity-50 cursor-not-allowed': !isSpeaking && !isPaused }"
                                :disabled="!isSpeaking && !isPaused"
                                title="Detener audio"
                                aria-label="Detener audio"
                                @click="cancel"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1V8a1 1 0 00-1-1H8z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                            </button>
                            <!-- Mute -->
                            <button
                                class="hover:text-cyan-200 transition-colors duration-200 p-1 rounded hover:bg-white/10"
                                :class="{ 'text-red-400': isMuted }"
                                title="Silenciar audio"
                                aria-label="Silenciar audio"
                                @click="toggleMute"
                            >
                                <svg
                                    v-if="isMuted"
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"
                                        clip-rule="evenodd"
                                    ></path>
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"
                                    ></path>
                                </svg>
                                <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        fill-rule="evenodd"
                                        d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    ></path>
                                </svg>
                            </button>
                        </div>
                        <!-- Slider Volumen -->
                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-white/80">Vol</span>
                            <input
                                v-model="volume"
                                type="range"
                                min="0"
                                max="1"
                                step="0.1"
                                class="w-16 accent-cyan-300 h-1 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                aria-label="Volumen"
                                @input="setVolume($event.target.value)"
                            />
                        </div>
                    </div>
                </div>

                <!-- AREA DE MENSAJES COMPLETA -->
                <div
                    ref="messagesContainer"
                    class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900 scroll-smooth custom-scrollbar"
                >
                    <div
                        v-for="msg in displayMessages"
                        :key="msg.id"
                        :class="{
                            'flex justify-end': msg.role === 'user',
                            'flex justify-start': msg.role === 'assistant',
                        }"
                        class="message-item"
                    >
                        <div
                            :class="{
                                'bg-blue-600 text-white': msg.role === 'user',
                                'bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600':
                                    msg.role === 'assistant',
                            }"
                            class="max-w-[85%] rounded-2xl px-4 py-2 shadow-sm text-sm transition-all duration-200"
                        >
                            <!-- Contenido del mensaje con efecto typewriter -->
                            <div class="whitespace-pre-wrap break-words">
                                {{ msg.displayContent }}
                                <!-- Cursor parpadeante para mensajes que se están escribiendo -->
                                <span
                                    v-if="msg.isTyping && msg.role === 'assistant'"
                                    class="inline-block w-2 h-4 bg-blue-500 ml-1 animate-pulse align-middle"
                                ></span>
                            </div>
                        </div>
                    </div>

                    <!-- Loading Indicator Mejorado -->
                    <div v-if="isLoading" class="flex justify-start">
                        <div
                            class="bg-white dark:bg-gray-700 rounded-2xl px-4 py-2 border border-gray-200 dark:border-gray-600 shadow-sm"
                        >
                            <div class="flex space-x-1 h-4 items-center">
                                <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                                <div
                                    class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"
                                    style="animation-delay: 0.1s"
                                ></div>
                                <div
                                    class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"
                                    style="animation-delay: 0.2s"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- INPUT AREA COMPLETA -->
                <div
                    class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex items-center gap-2"
                >
                    <!-- BOTÓN DE MICRÓFONO MEJORADO -->
                    <button
                        class="p-2 rounded-full transition-all duration-300 relative group"
                        :class="{
                            'text-red-500 bg-red-50 dark:bg-red-900/20 animate-pulse': isListening,
                            'text-gray-500 hover:text-blue-600 hover:bg-gray-100 dark:hover:bg-gray-700': !isListening,
                        }"
                        :title="isListening ? 'Detener grabación' : 'Hablar con voz'"
                        :disabled="isLoading || !speechRecognitionSupported"
                        aria-label="Activar micrófono"
                        @click="toggleMicrophone"
                    >
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                fill-rule="evenodd"
                                d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z"
                                clip-rule="evenodd"
                            ></path>
                        </svg>
                        <!-- Indicador de grabación -->
                        <div
                            v-if="isListening"
                            class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full animate-ping"
                        ></div>
                        <div v-if="isListening" class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></div>
                    </button>

                    <input
                        v-model="userInput"
                        type="text"
                        placeholder="Escribe tu consulta sobre productos, precios o reservaciones..."
                        class="flex-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-full text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-2 px-4 transition-all duration-200 placeholder-gray-500 dark:placeholder-gray-400"
                        :disabled="isLoading"
                        maxlength="500"
                        aria-label="Escribir mensaje"
                        @keyup="handleKeyPress"
                    />

                    <button
                        class="p-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-300 transform hover:scale-105 active:scale-95"
                        :disabled="!userInput.trim() || isLoading"
                        title="Enviar mensaje"
                        aria-label="Enviar mensaje"
                        @click="sendMessage"
                    >
                        <svg class="w-5 h-5 transform rotate-90" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"
                            ></path>
                        </svg>
                    </button>
                </div>
            </div>
        </transition>

        <!-- BOTÓN FLOTANTE (TRIGGER) -->
        <button
            class="w-14 h-14 bg-gradient-to-r from-cyan-600 to-blue-600 rounded-full shadow-lg hover:shadow-cyan-500/50 hover:scale-110 transition-all duration-300 transform flex items-center justify-center text-white focus:outline-none focus:ring-2 focus:ring-cyan-400 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 group"
            :aria-expanded="isOpen"
            aria-label="Abrir chat de FlipperBot"
            @click="toggleChat"
        >
            <span v-if="!isOpen" class="text-3xl group-hover:scale-110 transition-transform">💬</span>
            <svg
                v-else
                class="w-8 h-8 group-hover:scale-110 transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>

            <!-- Notificación de nuevos mensajes -->
            <div
                v-if="!isOpen && messages.length > 1"
                class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full animate-pulse"
            ></div>
        </button>
    </div>
</template>

<style scoped>
/* Scrollbar personalizado para navegadores WebKit */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.7);
}

/* Mejoras de accesibilidad */
@media (prefers-reduced-motion: reduce) {
    .transition-all,
    .transition,
    .transform {
        transition: none !important;
    }

    .animate-pulse,
    .animate-bounce,
    .animate-ping {
        animation: none !important;
    }
}

/* Mejoras para modo oscuro */
@media (prefers-color-scheme: dark) {
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: rgba(75, 85, 99, 0.5);
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background-color: rgba(75, 85, 99, 0.7);
    }
}
</style>
