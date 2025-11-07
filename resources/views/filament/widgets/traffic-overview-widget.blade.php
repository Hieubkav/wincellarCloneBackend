@php
    $pollingInterval = $this->getPollingInterval();
    $filters = $this->getFilters();
@endphp

<x-filament-widgets::widget
    :attributes="
        (new \Illuminate\View\ComponentAttributeBag)
            ->merge([
                'wire:poll.' . $pollingInterval => $pollingInterval ? true : null,
            ], escape: false)
            ->class([
                'fi-wi-stats-overview',
            ])
    "
>
    @if ($filters)
        <div class="flex justify-end mb-4">
            <x-filament::input.wrapper
                inline-prefix
                wire:target="filter"
                class="fi-wi-stats-filter"
            >
                <x-filament::input.select
                    inline-prefix
                    wire:model.live="filter"
                >
                    @foreach ($filters as $value => $label)
                        <option value="{{ $value }}">
                            {{ $label }}
                        </option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    @endif
    
    {{ $this->content }}
</x-filament-widgets::widget>
