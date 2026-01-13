@script
<script>
    Livewire.on('passkeyPropertiesValidated', async function (eventData) {
        const passkeyOptions = eventData[0].passkeyOptions;

        try {
            const passkey = await startRegistration({ optionsJSON: passkeyOptions });
            @this.call('storePasskey', JSON.stringify(passkey));
        } catch (error) {
            console.error('Passkey registration failed:', error);

            let title = 'Registration Error';
            let message = 'An unexpected error occurred during passkey registration. Please try again.';

            if (error.name === 'NotAllowedError') {
                title = 'Registration Cancelled';
                message = 'Passkey registration was cancelled or not allowed. Please try again.';
            } else if (error.name === 'AbortError') {
                // Silent failure for intentional aborts
                return;
            } else if (error.name === 'SecurityError') {
                title = 'Security Error';
                message = 'Security error occurred. Make sure you are on a secure connection (HTTPS).';
            } else if (error.name === 'NotSupportedError') {
                title = 'WebAuthn Not Supported';
                message = 'WebAuthn is not supported by this browser or device.';
            } else if (error.name === 'InvalidStateError') {
                title = 'Passkey Already Registered';
                message = 'This passkey is already registered. Please use a different authenticator.';
            } else if (error.message && error.message.includes('timeout')) {
                title = 'Registration Timeout';
                message = 'Registration timed out. Please try again and respond to the prompt more quickly.';
            }

            new FilamentNotification()
                .title(title)
                .danger()
                .body(message)
                .send();
        }
    });
</script>
@endscript
