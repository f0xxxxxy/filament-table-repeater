@php
    use Filament\Forms\Components\Actions\Action;
    use Filament\Support\Enums\Alignment;
    use Filament\Support\Enums\MaxWidth;

    $containers = $getChildComponentContainers();

    $addAction = $getAction($getAddActionName());
    $cloneAction = $getAction($getCloneActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $moveDownAction = $getAction($getMoveDownActionName());
    $moveUpAction = $getAction($getMoveUpActionName());
    $reorderAction = $getAction($getReorderActionName());
    $isReorderableWithButtons = $isReorderableWithButtons();
    $extraItemActions = $getExtraItemActions();
    $visibleExtraItemActions = [];

    $headers = $getHeaders();
    $breakPoint = $getBreakPoint();
    $hasContainers = count($containers) > 0;
    $emptyLabel = $getEmptyLabel();

    $statePath = $getStatePath();

    foreach ($containers as $uuid => $row) {
        $visibleExtraItemActions = array_filter(
            $extraItemActions,
            fn (Action $action): bool => $action(['item' => $uuid])->isVisible(),
        );
    }

    $hasActions = $reorderAction->isVisible()
        || $cloneAction->isVisible()
        || $deleteAction->isVisible()
        || $moveUpAction->isVisible()
        || $moveDownAction->isVisible()
        || filled($visibleExtraItemActions);
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{}"
        {{ $attributes->merge($getExtraAttributes())->class([
            'looper-component space-y-6 relative',
            match ($breakPoint) {
                'sm', MaxWidth::Small => 'break-point-sm',
                'lg', MaxWidth::Large => 'break-point-lg',
                'xl', MaxWidth::ExtraLarge => 'break-point-xl',
                '2xl', MaxWidth::TwoExtraLarge => 'break-point-2xl',
                default => 'break-point-md',
            }
        ]) }}
    >
        @if (count($containers) || $emptyLabel !== false)
            <div class="looper-container rounded-xl relative ring-1 ring-gray-950/5 dark:ring-white/20">
                <table class="w-full">
                    <thead @class([
                        'looper-header-hidden sr-only' => empty($headers),
                        'looper-header rounded-t-xl overflow-hidden border-b border-gray-950/5 dark:border-white/20' => filled($headers),
                    ])>
                    <tr class="text-xs md:divide-x md:divide-gray-950/5 dark:md:divide-white/20">
                        @foreach ($headers as $key => $header)
                            <th
                                @class([
                                    'looper-header-column p-2 font-medium first:rounded-tl-xl last:rounded-tr-xl bg-gray-100 dark:text-gray-300 dark:bg-gray-900/60',
                                    match($header->getAlignment()) {
                                      'center', Alignment::Center => 'text-center',
                                      'right', 'end', Alignment::Right, Alignment::End => 'text-end',
                                      default => 'text-start'
                                    }
                                ])
                                style="width: {{ $header->getWidth() }}"
                            >
                                {{ $header->getLabel() }}
                                @if ($header->isRequired())
                                    <span class="whitespace-nowrap">
                                        <sup class="font-medium text-danger-700 dark:text-danger-400">*</sup>
                                    </span>
                                @endif
                            </th>
                        @endforeach
                        @if ($hasActions && count($containers))
                            <th class="looper-header-column w-px last:rounded-tr-xl p-2 bg-gray-100 dark:bg-gray-900/60">
                                <span class="sr-only">
                                    {{ trans('looper::components.repeater.row_actions.label') }}
                                </span>
                            </th>
                        @endif
                    </tr>
                    </thead>
                    <tbody
                        x-sortable
                        wire:end.stop="{{ 'mountFormComponentAction(\'' . $statePath . '\', \'reorder\', { items: $event.target.sortable.toArray() })' }}"
                        class="looper-rows-wrapper divide-y divide-gray-950/5 dark:divide-white/20"
                    >
                    @if (count($containers))
                        @foreach ($containers as $uuid => $row)
                            <tr
                                wire:key="{{ $this->getId() }}.{{ $row->getStatePath() }}.{{ $field::class }}.item"
                                x-sortable-item="{{ $uuid }}"
                                class="looper-row md:divide-x md:divide-gray-950/5 dark:md:divide-white/20"
                            >
                                @foreach($row->getComponents() as $cell)
                                    @if(! $cell instanceof \Filament\Forms\Components\Hidden && ! $cell->isHidden())
                                        <td
                                            @class([
                                                'looper-column p-2',
                                                'has-hidden-label' => $cell->isLabelHidden(),
                                            ])
                                            style="width: {{ $cell->getMaxWidth() ?? 'auto' }}"
                                        >
                                            {{ $cell }}
                                        </td>
                                    @else
                                        {{ $cell }}
                                    @endif
                                @endforeach

                                @if ($hasActions)
                                    <td class="looper-column p-2 w-px">
                                        <ul class="flex items-center gap-x-3 lg:justify-center px-2">
                                            @foreach ($visibleExtraItemActions as $extraItemAction)
                                                <li>
                                                    {{ $extraItemAction(['item' => $uuid]) }}
                                                </li>
                                            @endforeach

                                            @if ($reorderAction->isVisible())
                                                <li x-sortable-handle class="shrink-0">
                                                    {{ $reorderAction }}
                                                </li>
                                            @endif

                                            @if ($isReorderableWithButtons)
                                                @if (! $loop->first)
                                                    <li>
                                                        {{ $moveUpAction(['item' => $uuid]) }}
                                                    </li>
                                                @endif

                                                @if (! $loop->last)
                                                    <li>
                                                        {{ $moveDownAction(['item' => $uuid]) }}
                                                    </li>
                                                @endif
                                            @endif

                                            @if ($cloneAction->isVisible())
                                                <li>
                                                    {{ $cloneAction(['item' => $uuid]) }}
                                                </li>
                                            @endif

                                            @if ($deleteAction->isVisible())
                                                <li>
                                                    {{ $deleteAction(['item' => $uuid]) }}
                                                </li>
                                            @endif
                                        </ul>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr class="looper-row looper-empty-row md:divide-x md:divide-gray-950/5 dark:md:divide-divide-white/20">
                            <td colspan="{{ count($headers) + intval($hasActions) }}"
                                class="looper-column looper-empty-column p-4 w-px text-center italic">
                                {{ $emptyLabel ?: trans('looper::components.repeater.empty.label') }}
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
            </div>
        @endif

        @if ($addAction->isVisible())
            <div class="relative flex justify-center">
                {{ $addAction }}
            </div>
        @endif
    </div>
</x-dynamic-component>