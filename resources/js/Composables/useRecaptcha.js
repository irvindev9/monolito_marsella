import { onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

let scriptPromise = null;

function loadRecaptchaScript(siteKey) {
    if (window.grecaptcha) return Promise.resolve(window.grecaptcha);
    if (scriptPromise) return scriptPromise;

    scriptPromise = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(siteKey)}`;
        script.async = true;
        script.defer = true;
        script.onload = () => resolve(window.grecaptcha);
        script.onerror = () => {
            scriptPromise = null;
            reject(new Error('Failed to load reCAPTCHA'));
        };
        document.head.appendChild(script);
    });

    return scriptPromise;
}

export function useRecaptcha() {
    const page = usePage();

    onMounted(() => {
        const siteKey = page.props.recaptchaSiteKey;
        if (siteKey) {
            loadRecaptchaScript(siteKey).catch(() => {});
        }
    });

    async function executeRecaptcha(action) {
        const siteKey = page.props.recaptchaSiteKey;
        if (!siteKey) return '';

        const grecaptcha = await loadRecaptchaScript(siteKey);
        return new Promise((resolve, reject) => {
            grecaptcha.ready(() => {
                grecaptcha.execute(siteKey, { action }).then(resolve, reject);
            });
        });
    }

    return { executeRecaptcha };
}
