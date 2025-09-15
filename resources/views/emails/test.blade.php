@component('mail::message')
{{-- Header mit Logo --}}
<img src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/icon128-NtUw1df0Zs1WrBGTHygFOmCVae4om9.png" alt="PromptIn Logo" style="width:75px; margin-bottom:20px;">

# Willkommen bei PromptIn

Dies ist ein Test, ob unsere E-Mail-Zustellung funktioniert.  
Bitte ignoriere diese Nachricht.

{{-- Optionaler Button --}}
@component('mail::button', ['url' => 'https://prompt-in.com'])
Zur Webseite
@endcomponent

Danke,<br>
**PromptIn Team**

{{-- Footer --}}
@slot('footer')
© 2025 PromptIn. All rights reserved.
@endslot
@endcomponent
