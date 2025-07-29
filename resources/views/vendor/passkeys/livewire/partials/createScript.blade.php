@script
<script>
    Livewire.on('passkeyPropertiesValidated', async function (eventData) {
        console.log('test');
        try {
            const passkeyOptions = eventData[0].passkeyOptions;

            const passkey = await startRegistration({ optionsJSON: passkeyOptions });

            @this.call('storePasskey', JSON.stringify(passkey));
        } catch (error) {
            console.error('Passkey registration failed:', error);
            let title = 'Authentication Error'
            new FilamentNotification()
                .title(title)
                .danger()
                .body(error.message)
                .send();

            @this.call('handlePasskeyError', error.message || 'An unknown error occurred during passkey registration');
        }
    });
</script>
@endscript
