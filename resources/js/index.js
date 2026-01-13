import {
    browserSupportsWebAuthn,
    browserSupportsWebAuthnAutofill,
    startAuthentication,
    startRegistration,
} from '@simplewebauthn/browser'

window.browserSupportsWebAuthn = browserSupportsWebAuthn;
window.browserSupportsWebAuthnAutofill = browserSupportsWebAuthnAutofill;
window.startAuthentication = startAuthentication;
window.startRegistration = startRegistration;
