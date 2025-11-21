import { ref, onMounted } from 'vue';

const isDark = ref(false);

export function useDarkMode() {
    const toggleDarkMode = () => {
        isDark.value = !isDark.value;
        updateHtmlClass();
        localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    };

    const updateHtmlClass = () => {
        if (isDark.value) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    onMounted(() => {
        // Verificar preferencia guardada o preferencia del sistema
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            isDark.value = savedTheme === 'dark';
        } else {
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        updateHtmlClass();
    });

    return { isDark, toggleDarkMode };
}
