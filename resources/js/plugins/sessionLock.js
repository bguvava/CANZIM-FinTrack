/**
 * Session Lock Plugin
 * Registers the SessionLockScreen component globally and mounts it to the DOM
 */
import { createApp, h } from "vue";
import { createPinia } from "pinia";
import SessionLockScreen from "../components/auth/SessionLockScreen.vue";

let lockScreenApp = null;
let lockScreenContainer = null;

/**
 * Mount the session lock screen to the DOM
 * This creates a separate Vue app for the lock screen so it works independently
 */
function mountLockScreen() {
    // Don't mount multiple times
    if (lockScreenContainer) {
        return;
    }

    // Create a container for the lock screen
    lockScreenContainer = document.createElement("div");
    lockScreenContainer.id = "session-lock-container";
    document.body.appendChild(lockScreenContainer);

    // Create a mini Vue app for the lock screen
    lockScreenApp = createApp({
        render() {
            return h(SessionLockScreen);
        },
    });

    // Use the same Pinia instance as the main app
    // We need to get the existing Pinia store
    const existingPinia = window.__PINIA__;
    if (existingPinia) {
        lockScreenApp.use(existingPinia);
    } else {
        lockScreenApp.use(createPinia());
    }

    lockScreenApp.mount(lockScreenContainer);
}

/**
 * Unmount the session lock screen from the DOM
 */
function unmountLockScreen() {
    if (lockScreenApp) {
        lockScreenApp.unmount();
        lockScreenApp = null;
    }
    if (lockScreenContainer) {
        lockScreenContainer.remove();
        lockScreenContainer = null;
    }
}

/**
 * Session Lock Plugin for Vue
 */
export const SessionLockPlugin = {
    install(app) {
        // Store the Pinia instance globally for the lock screen
        window.__PINIA__ = app.config.globalProperties.$pinia;

        // Mount lock screen after the main app is mounted
        // Use nextTick to ensure Pinia is fully initialized
        setTimeout(() => {
            mountLockScreen();
        }, 100);

        // Provide unmount function if needed
        app.config.globalProperties.$unmountLockScreen = unmountLockScreen;
    },
};

export default SessionLockPlugin;
