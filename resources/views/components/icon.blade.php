@props(['name'])

{{-- Google Material Symbols names, mapped from the legacy icon keys --}}
@php($icon = match ($name) {
    'dashboard' => 'space_dashboard',
    'calendar' => 'calendar_month',
    'megaphone' => 'campaign',
    'menu-book' => 'menu_book',
    'arrow-upward' => 'arrow_upward',
    'arrow-downward' => 'arrow_downward',
    'code-bracket' => 'code',
    'search' => 'search',
    'plus' => 'add',
    'pencil' => 'edit',
    'trash' => 'delete',
    'eye' => 'visibility',
    'eye-slash' => 'visibility_off',
    'cog' => 'settings',
    'logout' => 'logout',
    'chevron-right' => 'chevron_right',
    'check' => 'check_circle',
    'close' => 'close',
    default => $name,
})

<span {{ $attributes->merge(['class' => 'material-symbols-outlined select-none leading-none']) }}>{{ $icon }}</span>
