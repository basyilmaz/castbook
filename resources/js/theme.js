const applyTheme = (theme) => {
    const body = document.body;

    body.dataset.bsTheme = theme;

    if (theme === 'dark') {
        body.classList.add('bg-dark', 'text-white');
        body.classList.remove('bg-light');
    } else {
        body.classList.remove('bg-dark', 'text-white');
        body.classList.add('bg-light');
    }
};

export function initTheme() {
    if (typeof document === 'undefined') {
        return;
    }

    const body = document.body;
    const mode = body.dataset.themeMode || 'auto';

    if (mode === 'dark') {
        applyTheme('dark');
        return;
    }

    if (mode === 'light') {
        applyTheme('light');
        return;
    }

    const media = window.matchMedia('(prefers-color-scheme: dark)');
    const update = () => applyTheme(media.matches ? 'dark' : 'light');

    update();

    if (typeof media.addEventListener === 'function') {
        media.addEventListener('change', update);
    } else if (typeof media.addListener === 'function') {
        media.addListener(update);
    }
}

const initUi = () => {
    initTheme();
};

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initUi, { once: true });
    } else {
        initUi();
    }
}

export default initTheme;
