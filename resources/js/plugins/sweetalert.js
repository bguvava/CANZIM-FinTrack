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

    // Button styling - keep true for proper SweetAlert2 button styles
    buttonsStyling: true,

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
    position: "top-end",
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
 * Shows warning before auto-logout with countdown
 *
 * @param {number} seconds - Seconds until timeout
 * @returns {Promise<string>} - 'continue' if user wants to continue, 'logout' if user chose logout, 'timeout' if timer expired
 */
export const sessionTimeoutWarning = async (seconds = 30) => {
    let timerInterval;

    const result = await canzimSwal.fire({
        title: "Session Expiring Soon",
        html: `<div class="text-gray-700">
            <p class="mb-3">Your session will expire due to inactivity.</p>
            <p class="text-2xl font-bold text-red-600" id="countdown-timer">${seconds}</p>
            <p class="text-sm text-gray-500">seconds remaining</p>
        </div>`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText:
            '<i class="fas fa-sign-in-alt mr-2"></i>Stay Logged In',
        cancelButtonText: '<i class="fas fa-sign-out-alt mr-2"></i>Logout Now',
        confirmButtonColor: "#1E40AF",
        cancelButtonColor: "#DC2626",
        reverseButtons: true,
        timer: seconds * 1000,
        timerProgressBar: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            const timerDisplay =
                Swal.getHtmlContainer().querySelector("#countdown-timer");
            timerInterval = setInterval(() => {
                const remaining = Math.ceil(Swal.getTimerLeft() / 1000);
                timerDisplay.textContent = remaining;
                if (remaining <= 10) {
                    timerDisplay.classList.add("animate-pulse");
                }
            }, 100);
        },
        willClose: () => {
            clearInterval(timerInterval);
        },
    });

    // Return different values based on user action
    if (result.isConfirmed) {
        return "continue";
    } else if (result.dismiss === Swal.DismissReason.cancel) {
        return "logout";
    } else {
        // Timer expired or other dismissal - lock the session
        return "timeout";
    }
};

/**
 * Logout Confirmation
 * Confirms user wants to logout
 *
 * @returns {Promise<boolean>} - True if confirmed
 */
export const confirmLogout = async () => {
    const result = await canzimSwal.fire({
        title: "Confirm Logout",
        text: "Are you sure you want to logout?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-sign-out-alt mr-2"></i>Logout',
        cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancel',
        confirmButtonColor: "#DC2626", // Red for logout
        cancelButtonColor: "#1E40AF", // Blue for cancel
        reverseButtons: true,
        focusCancel: true,
    });

    return result.isConfirmed;
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
