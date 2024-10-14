@if(config('cookie-consent.enabled'))
    <div id="cookie-consent">
        <p>We use cookies to ensure you get the best experience on our website.</p>
        <button onclick="{{ config('cookie-consent.consent_function') }}()">
            {{ config('cookie-consent.consent_button_text') }}
        </button>
    </div>
    <script>
        function consentGrantedAdStorage() {
            // Adicionar lógica para consentimento de armazenamento de anúncios
            console.log('Consent granted');
            // Exemplo: definir um cookie ou chamar uma API
            document.cookie = "{{ config('cookie-consent.cookie_name') }}=true; max-age={{ config('cookie-consent.cookie_lifetime') * 24 * 60 * 60 }}";
        }
    </script>
@endif
