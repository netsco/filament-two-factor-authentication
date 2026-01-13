@script
<script>
    $wire.on('passkeyPropertiesValidated', async (eventData) => {
        try {
            const passkeyOptions = eventData[0].passkeyOptions;
            const passkey = await startRegistration({ optionsJSON: passkeyOptions });
            console.log('Passkey created, calling storePasskey...');
            await $wire.storePasskey(JSON.stringify(passkey));
            console.log('storePasskey completed');
        } catch (error) {
            console.error('Passkey registration failed:', error);
            new FilamentNotification()
                .title('Registration error')
                .danger()
                .body(error.message)
                .send();
            $wire.dispatch('passkeyRegistrationFailed');
        }
    });
</script>
@endscript
