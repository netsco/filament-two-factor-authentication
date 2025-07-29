@script
<script>
    Livewire.on('passkeyPropertiesValidated', async function (eventData) {
        try {
            const passkeyOptions = eventData[0].passkeyOptions;

            const passkey = await startRegistration({ optionsJSON: passkeyOptions });

            @this.call('storePasskey', JSON.stringify(passkey));
        } catch (error) {
            console.error('Passkey registration failed:', error);
            new FilamentNotification()
                .title(title)
                .danger()
                .body(error)
                .send();

            @this.call('handlePasskeyError', error.message || 'An unknown error occurred during passkey registration');
        }
    });
</script>
@endscript
