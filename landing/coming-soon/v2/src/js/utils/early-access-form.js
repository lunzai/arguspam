/* =========================
Early Access Form Handler
=========================== */

// Configuration
const API_ENDPOINT = 'https://u5z4o5zzq0.execute-api.ap-southeast-1.amazonaws.com/prod/signup';

// Initialize form handler when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#early-access-form');

  if (form) {
    initEarlyAccessForm(form);
  }
});

/**
 * Initialize early access form
 * @param {HTMLFormElement} form - The form element
 */
function initEarlyAccessForm(form) {
  const emailInput = form.querySelector('input[type="email"]');
  const submitButton = form.querySelector('button[type="submit"]');
  const messageContainer = createMessageContainer(form);

  // Add form submit event listener
  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const email = emailInput.value.trim();

    // Client-side validation
    if (!isValidEmail(email)) {
      showMessage(messageContainer, 'Please enter a valid email address', 'error');
      return;
    }

    // Disable form during submission
    setFormLoading(emailInput, submitButton, true);
    hideMessage(messageContainer);

    try {
      // Submit to API
      const response = await fetch(API_ENDPOINT, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ email }),
      });

      if (response.status === 200) {
        // Success
        showMessage(
          messageContainer,
          'Success! You\'ve been added to the early access list. We\'ll notify you when ArgusPAM beta launches.',
          'success'
        );
        emailInput.value = ''; // Clear the input
      } else {
        // Error (400 or other)
        showMessage(
          messageContainer,
          'Oops! Something went wrong. Please try again later.',
          'error'
        );
      }
    } catch (error) {
      // Network error or other exception
      console.error('Early access form error:', error);
      showMessage(
        messageContainer,
        'Unable to connect. Please check your internet connection and try again.',
        'error'
      );
    } finally {
      // Re-enable form
      setFormLoading(emailInput, submitButton, false);
    }
  });

  // Add real-time email validation
  emailInput.addEventListener('blur', () => {
    const email = emailInput.value.trim();
    if (email && !isValidEmail(email)) {
      showMessage(messageContainer, 'Please enter a valid email address', 'error');
    } else {
      hideMessage(messageContainer);
    }
  });

  // Clear error message when user starts typing
  emailInput.addEventListener('input', () => {
    if (messageContainer.classList.contains('error')) {
      hideMessage(messageContainer);
    }
  });
}

/**
 * Create message container element
 * @param {HTMLFormElement} form - The form element
 * @returns {HTMLElement} - The message container
 */
function createMessageContainer(form) {
  const container = document.createElement('div');
  container.id = 'early-access-message';
  container.className = 'early-access-message hidden';
  container.setAttribute('role', 'alert');
  container.setAttribute('aria-live', 'polite');

  // Insert after the form
  form.parentNode.insertBefore(container, form.nextSibling);

  return container;
}

/**
 * Show message to user
 * @param {HTMLElement} container - Message container element
 * @param {string} message - Message text
 * @param {string} type - Message type ('success' or 'error')
 */
function showMessage(container, message, type) {
  container.textContent = message;
  container.className = `early-access-message ${type}`;

  // Scroll to message if it's below the fold
  setTimeout(() => {
    const rect = container.getBoundingClientRect();
    if (rect.bottom > window.innerHeight) {
      container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  }, 100);
}

/**
 * Hide message
 * @param {HTMLElement} container - Message container element
 */
function hideMessage(container) {
  container.className = 'early-access-message hidden';
}

/**
 * Set form loading state
 * @param {HTMLInputElement} input - Email input element
 * @param {HTMLButtonElement} button - Submit button element
 * @param {boolean} loading - Loading state
 */
function setFormLoading(input, button, loading) {
  if (loading) {
    input.disabled = true;
    button.disabled = true;
    button.innerHTML = `
      <svg class="animate-spin h-5 w-5 inline-block mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <span>Submitting...</span>
    `;
  } else {
    input.disabled = false;
    button.disabled = false;
    button.innerHTML = '<span>Notify Me</span>';
  }
}

/**
 * Validate email format
 * @param {string} email - Email address to validate
 * @returns {boolean} - True if valid
 */
function isValidEmail(email) {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
}
