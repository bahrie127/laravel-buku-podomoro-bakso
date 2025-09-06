{{-- resources/views/components/application-logo.blade.php --}}
<img
    src="{{ asset('images/logo.svg') }}"
    alt="Application Logo"
    {{ $attributes->merge([
        'class' => 'h-9 w-auto'
    ]) }}
>
