<script>
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


            const response = await fetch('{{ filament()->getCurrentPanel()->route('passkeys.authentication_options') }}')

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`)
            }

            const options = await response.json()

            const startAuthenticationResponse = await startAuthentication({ optionsJSON: options })

            const form = document.getElementById('passkey-login-form')

            form.addEventListener('formdata', ({ formData }) => {
                formData.set('start_authentication_response', JSON.stringify(startAuthenticationResponse))
            })

            form.submit()

        } catch (error) {
            console.error('Passkey authentication error:', error);

            let title = 'Authentication Error'
            let message = 'An unexpected error occurred during authentication. Please try again.'

            // Handle specific WebAuthn errors
            if (error.name === 'NotAllowedError') {
                title = 'Authentication Cancelled'
                message = 'Authentication was cancelled or not allowed. Please try again.'
            } else if (error.name === 'AbortError') {
                title = 'Authentication Timeout'
                message = 'Authentication timed out. Please try again and respond to the prompt more quickly.'
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


        }
    }
</script>
