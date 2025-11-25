import { ref, onUnmounted } from 'vue';

export function useAudioSynthesis() {
    const synth = window.speechSynthesis;
    const utterance = ref(null);

    // Estado reactivo
    const isSpeaking = ref(false);
    const isMuted = ref(false);
    const volume = ref(1.0);

    // Configuración de voz
    const getSpanishVoice = () => {
        const voices = synth.getVoices();

        // Priorizar voces en español
        const spanishVoices = voices.filter((v) => v.lang.includes('es'));

        // Ordenar por preferencia
        return (
            spanishVoices.find((v) => v.lang.includes('es-ES')) ||
            spanishVoices.find((v) => v.lang.includes('es-419')) ||
            spanishVoices.find((v) => v.lang.includes('es-MX')) ||
            spanishVoices[0] ||
            voices[0]
        );
    };

    const speak = (text) => {
        if (isMuted.value || !text.trim()) return;

        // Cancelar cualquier audio previo
        cancel();

        const newUtterance = new SpeechSynthesisUtterance(text);

        newUtterance.lang = 'es-ES';
        newUtterance.rate = 1.0;
        newUtterance.pitch = 1.0;
        newUtterance.volume = volume.value;

        // Intentar asignar voz en español
        const voice = getSpanishVoice();
        if (voice) {
            newUtterance.voice = voice;
        }

        // Eventos
        newUtterance.onstart = () => {
            isSpeaking.value = true;
        };

        newUtterance.onend = () => {
            isSpeaking.value = false;
            utterance.value = null;
        };

        newUtterance.onerror = (e) => {
            console.error('TTS Error:', e);
            isSpeaking.value = false;
        };

        utterance.value = newUtterance;
        synth.speak(newUtterance);
    };

    const cancel = () => {
        synth.cancel();
        isSpeaking.value = false;
        utterance.value = null;
    };

    const toggleMute = () => {
        isMuted.value = !isMuted.value;
        if (isMuted.value) {
            cancel();
        }
    };

    const setVolume = (val) => {
        volume.value = parseFloat(val);
        // El volumen se aplicará al próximo utterance
        // No interrumpimos el audio actual
    };

    // Cargar voces cuando estén disponibles
    if (typeof window !== 'undefined') {
        if (synth.getVoices().length > 0) {
            getSpanishVoice();
        } else {
            synth.addEventListener('voiceschanged', () => {
                getSpanishVoice();
            });
        }
    }

    // Limpieza al desmontar el componente
    onUnmounted(() => {
        cancel();
    });

    return {
        isSpeaking,
        isMuted,
        volume,
        speak,
        cancel,
        toggleMute,
        setVolume,
    };
}
