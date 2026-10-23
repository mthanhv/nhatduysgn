<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Nội dung cần hoàn thiện</x-slot>
        <x-slot name="afterHeader">
            <span class="nd-progress-label">{{ $done }}/{{ $total }} mục hoàn tất</span>
        </x-slot>

        <div class="nd-progress" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $done }}">
            <span style="width: {{ $total ? round($done / $total * 100) : 100 }}%"></span>
        </div>

        <ul class="nd-checklist">
            @foreach ($items as $item)
                <li @class(['is-done' => $item['count'] === 0])>
                    <a href="{{ $item['url'] }}">
                        @if ($item['count'] === 0)
                            <x-filament::icon icon="heroicon-s-check-circle" class="nd-checklist__icon" />
                        @else
                            <x-filament::icon icon="heroicon-o-exclamation-circle" class="nd-checklist__icon" />
                        @endif
                        <span class="nd-checklist__label">{{ $item['label'] }}</span>
                        @if ($item['count'] > 0 && empty($item['flag']))
                            <span class="nd-checklist__count">{{ $item['count'] }}</span>
                        @endif
                        <x-filament::icon icon="heroicon-m-chevron-right" class="nd-checklist__chevron" />
                    </a>
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
