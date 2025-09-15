<x-mail::message>
    {{-- Greeting --}}
    # Hello!

    Thank you for using PromptIn, your AI prompt management tool.

    {{-- Intro Lines --}}
    @foreach ($introLines as $line)
        {{ $line }}

    @endforeach

    {{-- Action Button --}}
    @isset($actionText)
            <?php
            $color = match ($level) {
                'success' => 'green',
                'error' => 'red',
                default => 'blue', // PromptIn brand color (adjust as needed)
            };
        ?>
            <x-mail::button :url="$actionUrl" :color="$color">
                {{ $actionText }}
            </x-mail::button>
    @endisset

    {{-- Outro Lines --}}
    @foreach ($outroLines as $line)
        {{ $line }}

    @endforeach

    {{-- Salutation --}}
    @lang('Best regards,')<br>
    The PromptIn Team<br>
    <a href="https://prompt-in.com">prompt-in.com</a>

    {{-- Subcopy --}}
    @isset($actionText)
        <x-slot:subcopy>
            @lang(
                "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below into your web browser:",
                [
                    'actionText' => $actionText,
                ]
            ) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
        </x-slot:subcopy>
    @endisset
    
    {{-- Footer Styling --}}
        <x-mail::subcopy>
    For support, contact us at <a href="mailto:business.promptIn@gmail.com">business.promptIn@gmail.com</a>.
</x-mail::subcopy>
</x-mail::message>