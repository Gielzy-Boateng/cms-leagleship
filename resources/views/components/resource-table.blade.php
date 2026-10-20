@props(['columns' => [], 'compact' => false])

@php($pad = $compact ? 'px-3 py-2.5' : 'px-4 py-3')

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-dashed border-yellow-300 bg-white shadow-sm']) }}>
    <div @class(['overflow-x-auto' => ! $compact])>
        <table @class(['w-full border-collapse text-left text-sm', 'min-w-[46rem]' => ! $compact])>
            <thead>
                <tr class="border-b border-dashed border-yellow-300 text-[11px] font-semibold tracking-wider text-gray-500 uppercase">
                    <th scope="col" class="{{ $pad }} w-12 font-semibold"><span class="sr-only">Image</span></th>
                    <th scope="col" class="{{ $pad }} font-semibold">Title</th>
                    @foreach ($columns as $column)
                        <th scope="col" class="{{ $pad }} whitespace-nowrap font-semibold">{{ $column }}</th>
                    @endforeach
                    <th scope="col" class="{{ $pad }} whitespace-nowrap font-semibold">Status</th>
                    <th scope="col" class="{{ $pad }} text-right font-semibold"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dashed divide-yellow-200">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
