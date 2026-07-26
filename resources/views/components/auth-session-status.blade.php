@props(['status'])

@if ($status)
    <div {{ $attributes->merge([
        'class' => 'mb-4 p-3 rounded-lg bg-green-100 text-green-700 text-sm',
    ]) }}>
        {{ $status }}
    </div>
@endif
