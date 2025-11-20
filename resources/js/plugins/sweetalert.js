/**
 * SweetAlert2 Global Configuration
 * CANZIM FinTrack - Custom Theme
 *
 * This file configures SweetAlert2 with CANZIM branding and styling.
 * All modals, confirmations, alerts, and notifications use this configuration.
 *
 * @see https://sweetalert2.github.io/
 */

import Swal from "sweetalert2";

/**
 * CANZIM Theme Configuration
 * Primary Blue: #1E40AF
 * Secondary Gray: #6B7280
 */
const canzimTheme = {
    // Confirm button styling
    confirmButtonColor: "#1E40AF",
    confirmButtonText: "Confirm",

    // Cancel button styling
    cancelButtonColor: "#6B7280",
    cancelButtonText: "Cancel",

    // Border radius
    customClass: {
        popup: "rounded-xl",
        confirmButton: "rounded-lg px-6 py-2.5 font-medium",
        cancelButton: "rounded-lg px-6 py-2.5 font-medium",
        input: "rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500",
    },

    // Animation settings
    showClass: {
        popup: "animate__animated animate__slideInDown animate__faster",
    },
    hideClass: {
        popup: "animate__animated animate__fadeOut animate__faster",
    },

    // Button styling
    buttonsStyling: false,

    // Animation duration
    timer: null,
    timerProgressBar: false,
};

/**
 * Create configured Swal instance with CANZIM theme
 */
export const canzimSwal = Swal.mixin(canzimTheme);

/**
 * Toast notification configuration
 * Position: bottom-right
 * Duration: 3 seconds
 */
export const Toast = Swal.mixin({
    toast: true,
    position: "bottom-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener("mouseenter", Swal.stopTimer);
        toast.addEventListener("mouseleave", Swal.resumeTimer);
    },
    customClass: {
        popup: "rounded-lg shadow-lg",
    },
});

/**
 * Confirm Action Helper
 *
 * @param {string} title - Confirmation dialog title
 * @param {string} text - Confirmation dialog message
 * @param {string} confirmText - Confirm button text (can include HTML)
 * @param {string} cancelText - Cancel button text (can include HTML)
 * @returns {Promise<boolean>} - True if confirmed
 */
export const confirmAction = async (
    title,
    text,
    confirmText = "Confirm",
    cancelText = "Cancel",
) => {
    const result = await canzimSwal.fire({
        title,
        text,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true,
        focusCancel: true,
    });

    return result.isConfirmed;
};

/**
 * Success Alert Helper
 *
 * @param {string} title - Success message title
 * @param {string} text - Success message text
 */
export const showSuccess = (title = "Success!", text = "") => {
    return canzimSwal.fire({
        icon: "success",
        title,
        text,
        confirmButtonText: "OK",
    });
};

/**
 * Error Alert Helper
 *
 * @param {string} title - Error message title
 * @param {string} text - Error message text
 */
export const showError = (title = "Error!", text = "Something went wrong.") => {
    return canzimSwal.fire({
        icon: "error",
        title,
        text,
        confirmButtonText: "OK",
    });
};

/**
 * Warning Alert Helper
 *
 * @param {string} title - Warning message title
 * @param {string} text - Warning message text
 */
export const showWarning = (title = "Warning!", text = "") => {
    return canzimSwal.fire({
        icon: "warning",
        title,
        text,
        confirmButtonText: "OK",
    });
};

/**
 * Info Alert Helper
 *
 * @param {string} title - Info message title
 * @param {string} text - Info message text
 */
export const showInfo = (title = "Information", text = "") => {
    return canzimSwal.fire({
        icon: "info",
        title,
        text,
        confirmButtonText: "OK",
    });
};

/**
 * Loading Indicator Helper
 * Shows a loading spinner while async operations are in progress
 *
 * @param {string} title - Loading message
 */
export const showLoading = (title = "Processing...") => {
    return Swal.fire({
        title,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        },
    });
};

/**
 * Session Timeout Warning
 * Shows warning before auto-logout
 *
 * @param {number} seconds - Seconds until timeout
 * @returns {Promise<boolean>} - True if user wants to continue
 */
export const sessionTimeoutWarning = async (seconds = 60) => {
    const result = await canzimSwal.fire({
        title: "Session Expiring",
        text: `Your session will expire in ${seconds} seconds due to inactivity.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Stay Logged In",
        cancelButtonText: "Logout Now",
        reverseButtons: true,
        timer: seconds * 1000,
        timerProgressBar: true,
    });

    return result.isConfirmed;
};

/**
 * Logout Confirmation
 * Confirms user wants to logout
 *
 * @returns {Promise<boolean>} - True if confirmed
 */
export const confirmLogout = async () => {
    return await confirmAction(
        "Confirm Logout",
        "Are you sure you want to logout?",
        '<i class="fas fa-sign-out-alt mr-2"></i>Logout',
        '<i class="fas fa-times mr-2"></i>Cancel',
    );
};

/**
 * Show Confirm Dialog (Alias for confirmAction)
 * Kept for backward compatibility
 *
 * @param {string} title - Confirmation dialog title
 * @param {string} text - Confirmation dialog message
 * @param {string} confirmText - Confirm button text
 * @param {string} cancelText - Cancel button text
 * @returns {Promise<boolean>} - True if confirmed
 */
export const showConfirm = confirmAction;

// Export default Swal instance
export default canzimSwal;
