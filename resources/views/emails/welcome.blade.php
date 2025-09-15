@component('mail::message')
# Willkommen bei PromptIn, {{ $user->name }}

Danke, dass du dich registriert hast!

Wir freuen uns, dich an Bord zu haben.

@component('mail::button', ['url' => 'https://prompt-in.com'])
Zur Webseite
@endcomponent

Danke,<br>
**PromptIn Team**
@endcomponent
