<?php

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('イベント一覧')] class extends Component {
    /**
     * 開催日が近い順にイベントを取り出す。
     *
     * @return Collection<int, Event>
     */
    #[Computed]
    public function events(): Collection
    {
        return Event::query()->orderBy('starts_at')->get();
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">イベント一覧</flux:heading>
        @auth
            <flux:button :href="route('events.create')" variant="primary" icon="plus" wire:navigate>イベントを登録</flux:button>
        @endauth
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">
            <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
        </flux:callout>
    @endif

    @if ($this->events->isEmpty())
        <flux:text>イベントはまだありません。</flux:text>
    @else
        <flux:table>
            <flux:table.columns>
                <flux:table.column>イベント</flux:table.column>
                <flux:table.column>開催日時</flux:table.column>
                <flux:table.column>会場</flux:table.column>
                <flux:table.column>主催</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach ($this->events as $event)
                    <flux:table.row wire:key="event-{{ $event->id }}">
                        <flux:table.cell variant="strong">{{ $event->title }}</flux:table.cell>
                        <flux:table.cell>{{ $event->starts_at->isoFormat('M月D日(ddd) HH:mm') }} 〜 {{ $event->ends_at->isoFormat('HH:mm') }}</flux:table.cell>
                        <flux:table.cell>{{ $event->venue }}</flux:table.cell>
                        <flux:table.cell>{{ $event->user->name }}</flux:table.cell>
                        <flux:table.cell>
                            @if ($event->isOwnedBy(auth()->user()))
                                <flux:button :href="route('events.edit', $event)" size="sm" icon="pencil-square" wire:navigate>編集</flux:button>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    @endif
</div>
