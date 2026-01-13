@props(['enableAutofill' => false])

<script>
    // AbortController for managing conditional UI authentication
    let conditionalAuthController = null;

    async function submitPasskeyAuthentication(startAuthenticationResponse) {
        const form = document.getElementById('passkey-login-form')

        form.addEventListener('formdata', ({ formData }) => {
            formData.set('start_authentication_response', JSON.stringify(startAuthenticationResponse))
        })

        form.submit()
    }

    function handlePasskeyError(error) {
        console.error('Passkey authentication error:', error);

        let title = 'Authentication Error'
        let message = 'An unexpected error occurred during authentication. Please try again.'

        // Handle specific WebAuthn errors
        if (error.name === 'NotAllowedError') {
            title = 'Authentication Cancelled'
            message = 'Authentication was cancelled or not allowed. Please try again.'
        } else if (error.name === 'AbortError') {
            // Don't show error for intentional aborts (e.g., when switching to modal auth)
            return false
        } else if (error.name === 'SecurityError') {
            title = 'Security Error'
            message = 'Security error occurred. Make sure you are on a secure connection (HTTPS).'
        } else if (error.name === 'NotSupportedError') {
            title = 'WebAuthn Not Supported'
            message = 'WebAuthn is not supported by this browser or device. Please use an alternative login method.'
        } else if (error.message && error.message.includes('timeout')) {
            title = 'Authentication Timeout'
            message = 'Authentication timed out. Please try again and respond to the prompt more quickly.'
        } else if (error.message && error.message.includes('HTTP error')) {
            title = 'Server Error'
            message = 'Unable to connect to the authentication server. Please check your connection and try again.'
        }

        new FilamentNotification()
            .title(title)
            .danger()
            .body(message)
            .send()

        return true
    }

    async function startConditionalAuthentication() {
        try {
            // Check if conditional UI (autofill) is supported
            if (!window.browserSupportsWebAuthnAutofill || !await window.browserSupportsWebAuthnAutofill()) {
                return
            }

            // Create abort controller for this conditional auth session
            conditionalAuthController = new AbortController()

            const response = await fetch('{{ filament()->getCurrentPanel()->route('passkeys.authentication_options') }}')

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`)
            }

            const options = await response.json()

            // Start conditional authentication (autofill UI)
            const startAuthenticationResponse = await startAuthentication({
                optionsJSON: options,
                useBrowserAutofill: true,
            })

            // User selected a passkey from the autofill - submit the form
            await submitPasskeyAuthentication(startAuthenticationResponse)

        } catch (error) {
            // Only handle errors that aren't intentional aborts
            if (error.name !== 'AbortError') {
                handlePasskeyError(error)
            }
        }
    }

    async function authenticateWithPasskey() {
        try {
            // Check if WebAuthn is supported
            if (!window.browserSupportsWebAuthn || !window.browserSupportsWebAuthn()) {
                new FilamentNotification()
                    .title('WebAuthn Not Supported')
                    .danger()
                    .body('WebAuthn is not supported in this browser. Please use an alternative login method.')
                    .send()
                return
            }

            // Abort any ongoing conditional authentication
            if (conditionalAuthController) {
                conditionalAuthController.abort()
                conditionalAuthController = null
            }

            const response = await fetch('{{ filament()->getCurrentPanel()->route('passkeys.authentication_options') }}')

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`)
            }

            const options = await response.json()

            // Start modal authentication (immediate prompt)
            const startAuthenticationResponse = await startAuthentication({ optionsJSON: options })

            await submitPasskeyAuthentication(startAuthenticationResponse)

        } catch (error) {
            handlePasskeyError(error)
        }
    }

    // Add autocomplete attribute to email input for passkey autofill
    function setupPasskeyAutofill() {
        // Find the email/username input field
        const emailInput = document.querySelector('input[type="email"], input[name="email"], input[autocomplete*="email"], input[autocomplete*="username"]')

        if (emailInput) {
            // Get existing autocomplete value and append webauthn
            const existingAutocomplete = emailInput.getAttribute('autocomplete') || 'username'
            if (!existingAutocomplete.includes('webauthn')) {
                emailInput.setAttribute('autocomplete', existingAutocomplete + ' webauthn')
            }
        }

        // Start conditional authentication for autofill UI
        startConditionalAuthentication()
    }

    @if($enableAutofill)
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupPasskeyAutofill)
    } else {
        setupPasskeyAutofill()
    }
    @endif
</script>
