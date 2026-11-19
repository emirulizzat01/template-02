function readPreference(key) {
    try {
        return localStorage.getItem(key);
    } catch {
        return null;
    }
}

function savePreference(key, value) {
    try {
        localStorage.setItem(key, value);
    } catch {
        // The current session remains usable when browser storage is unavailable.
    }
}

export default function signalShell(storageKey) {
    return {
        collapsed: readPreference(storageKey + '-sidebar') === 'collapsed',
        dark: document.documentElement.dataset.theme === 'dark',
        toggleSidebar() {
            this.collapsed = !this.collapsed;
            savePreference(storageKey + '-sidebar', this.collapsed ? 'collapsed' : 'expanded');
        },
        toggleTheme() {
            this.dark = !this.dark;
            document.documentElement.dataset.theme = this.dark ? 'dark' : 'light';
            savePreference(storageKey + '-theme', document.documentElement.dataset.theme);
            window.dispatchEvent(new CustomEvent('signal-theme'));
        },
    };
}
