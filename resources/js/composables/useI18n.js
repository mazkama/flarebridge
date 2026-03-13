import { ref, computed } from 'vue';
import { translations } from '../translations';

const currentLocale = ref(localStorage.getItem('flare_locale') || 'en');

export function useI18n() {
    const t = (path, replacements = {}) => {
        const keys = path.split('.');
        let translation = translations[currentLocale.value];

        for (const key of keys) {
            if (translation[key] !== undefined) {
                translation = translation[key];
            } else {
                return path; // Fallback to path string
            }
        }

        if (typeof translation === 'string') {
            Object.keys(replacements).forEach(key => {
                translation = translation.replace(`{${key}}`, replacements[key]);
            });
            return translation;
        }

        return path;
    };

    const setLocale = (locale) => {
        if (translations[locale]) {
            currentLocale.value = locale;
            localStorage.setItem('flare_locale', locale);
        }
    };

    return {
        t,
        setLocale,
        locale: computed(() => currentLocale.value),
        allLocales: Object.keys(translations)
    };
}
