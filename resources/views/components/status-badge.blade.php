@props(['status'])

<span {{ $attributes->merge(['class' => 'text-xs font-medium '.($status === \App\Enums\PublicationStatus::Published
    ? 'text-gold-600'
    : 'text-gray-500')]) }}>
    {{ $status->label() }}
</span>
