<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('pam.app.web_url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
{{ __('Powered by') }} <a href="{{ config('pam.app.site_url') }}" style="color: #3869D4;">{{ config('app.name') }}</a> © {{ date('Y') }} {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
