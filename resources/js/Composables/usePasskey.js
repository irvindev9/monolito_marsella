import axios from 'axios';
import { ref } from 'vue';

function base64UrlToUint8Array(input) {
    let base64 = input.replace(/-/g, '+').replace(/_/g, '/');
    const pad = base64.length % 4;
    if (pad) {
        base64 += '='.repeat(4 - pad);
    }
    const binary = atob(base64);
    return Uint8Array.from(binary, (c) => c.charCodeAt(0));
}

function bufferToBase64Url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';
    for (let i = 0; i < bytes.byteLength; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

function parseServerOptions(options) {
    const publicKey = { ...options };
    publicKey.challenge = base64UrlToUint8Array(publicKey.challenge);

    if (publicKey.user) {
        publicKey.user = {
            ...publicKey.user,
            id: base64UrlToUint8Array(publicKey.user.id),
        };
    }

    ['excludeCredentials', 'allowCredentials'].forEach((key) => {
        if (Array.isArray(publicKey[key])) {
            publicKey[key] = publicKey[key].map((item) => ({
                ...item,
                id: base64UrlToUint8Array(item.id),
            }));
        }
    });

    return publicKey;
}

function serializeCredential(credential) {
    const payload = {
        id: credential.id,
        type: credential.type,
        rawId: bufferToBase64Url(credential.rawId),
        response: {},
    };

    ['clientDataJSON', 'attestationObject', 'authenticatorData', 'signature', 'userHandle'].forEach((key) => {
        if (key in credential.response && credential.response[key]) {
            payload.response[key] = bufferToBase64Url(credential.response[key]);
        }
    });

    return payload;
}

export function usePasskey() {
    const processing = ref(false);
    const error = ref('');

    const isSupported = () =>
        typeof window !== 'undefined' &&
        typeof window.PublicKeyCredential !== 'undefined' &&
        typeof navigator.credentials !== 'undefined';

    const registerPasskey = async (alias = '') => {
        if (!isSupported()) {
            error.value = 'Tu navegador o dispositivo no soporta Passkeys.';
            return null;
        }

        processing.value = true;
        error.value = '';

        try {
            const { data: options } = await axios.post(route('webauthn.register.options'));
            const publicKey = parseServerOptions(options);
            const credential = await navigator.credentials.create({ publicKey });

            if (!credential) {
                error.value = 'No se pudo completar el registro de la Passkey.';
                return null;
            }

            const payload = serializeCredential(credential);
            if (alias) {
                payload.alias = alias;
            }

            const { data } = await axios.post(route('webauthn.register'), payload);
            return data;
        } catch (err) {
            if (err?.name === 'NotAllowedError') {
                error.value = 'Operación cancelada o no permitida en este dispositivo.';
            } else if (err?.name === 'InvalidStateError') {
                error.value = 'Este dispositivo ya tiene una Passkey registrada para tu cuenta.';
            } else {
                error.value = err?.response?.data?.message || 'Ocurrió un error al registrar la Passkey.';
            }
            return null;
        } finally {
            processing.value = false;
        }
    };

    const loginWithPasskey = async (email = '', remember = false) => {
        if (!isSupported()) {
            error.value = 'Tu navegador o dispositivo no soporta Passkeys.';
            return null;
        }

        processing.value = true;
        error.value = '';

        try {
            const { data: options } = await axios.post(
                route('webauthn.login.options'),
                email ? { email } : {}
            );
            const publicKey = parseServerOptions(options);
            const credential = await navigator.credentials.get({ publicKey });

            if (!credential) {
                error.value = 'No se seleccionó ninguna Passkey.';
                return null;
            }

            const payload = {
                ...serializeCredential(credential),
                ...(remember ? { remember: true } : {}),
            };

            const { data } = await axios.post(route('webauthn.login'), payload);
            return data;
        } catch (err) {
            if (err?.name === 'NotAllowedError') {
                error.value = 'Inicio de sesión cancelado o sin Passkeys registradas en este dispositivo.';
            } else {
                error.value = err?.response?.data?.message || 'No se pudo iniciar sesión con Passkey.';
            }
            return null;
        } finally {
            processing.value = false;
        }
    };

    return {
        processing,
        error,
        isSupported,
        registerPasskey,
        loginWithPasskey,
    };
}
