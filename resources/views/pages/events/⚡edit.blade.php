<?php

use App\Models\Event;
use App\Models\TicketType;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('イベントを編集')] class extends Component {
    public Event $event;

    #[Validate('required|string|max:100')]
    public string $title = '';

    #[Validate('required|string|max:2000')]
    public string $description = '';

    #[Validate('required|string|max:100')]
    public string $venue = '';

    #[Validate('required|date')]
    public string $starts_at = '';

    #[Validate('required|date|after:starts_at')]
    public string $ends_at = '';

    public string $ticketName = '';

    public string $ticketPrice = '';

    public string $ticketCapacity = '';

    public function mount(Event $event): void
    {
        $this->authorize('update', $event);

        $this->event = $event;
        $this->title = $event->title;
        $this->description = $event->description;
        $this->venue = $event->venue;
        $this->starts_at = $event->starts_at->format('Y-m-d\TH:i');
        $this->ends_at = $event->ends_at->format('Y-m-d\TH:i');
    }

    /**
     * このイベントの券種（登録順）。
     *
     * @return Collection<int, TicketType>
     */
    #[Computed]
    public function ticketTypes(): Collection
    {
        return $this->event->ticketTypes()->orderBy('id')->get();
    }

    public function save(): void
    {
        $this->authorize('update', $this->event);

        $this->event->update($this->validate());

        session()->flash('status', 'イベントを更新しました。');

        $this->redirectRoute('events.index', navigate: true);
    }

    public function addTicketType(): void
    {
        $this->authorize('update', $this->event);

        $validated = $this->validate([
            'ticketName' => 'required|string|max:50',
            'ticketPrice' => 'required|integer|min:0|max:1000000',
            'ticketCapacity' => 'required|integer|min:1|max:100000',
        ], attributes: [
            'ticketName' => '券種名',
            'ticketPrice' => '価格',
            'ticketCapacity' => '定員',
        ]);

        $this->event->ticketTypes()->create([
            'name' => $validated['ticketName'],
            'price' => (int) $validated['ticketPrice'],
            'capacity' => (int) $validated['ticketCapacity'],
        ]);

        $this->reset('ticketName', 'ticketPrice', 'ticketCapacity');
        unset($this->ticketTypes);

        Flux::toast(variant: 'success', text: '券種を追加しました。');
    }

    public function deleteTicketType(int $ticketTypeId): void
    {
        $this->authorize('update', $this->event);

        $this->event->ticketTypes()->findOrFail($ticketTypeId)->delete();

        unset($this->ticketTypes);

        Flux::toast(variant: 'success', text: '券種を削除しました。');
    }

    public function duplicate(): void
    {
        $this->authorize('update', $this->event);

        $suffix = '（複製）';
        $title = str_ends_with($this->event->title, $suffix)
            ? $this->event->title
            : mb_substr($this->event->title, 0, 100 - mb_strlen($suffix)).$suffix;

        $copy = Event::create([
            'user_id' => $this->event->user_id,
            'title' => $title,
            'description' => $this->event->description,
            'venue' => $this->event->venue,
            'starts_at' => $this->event->starts_at->copy()->addWeek(),
            'ends_at' => $this->event->ends_at->copy()->addWeek(),
        ]);

        session()->flash('status', 'イベントを複製しました。');

        $this->redirectRoute('events.edit', $copy, navigate: true);
    }

    public function delete(): void
    {
        $this->authorize('update', $this->event);

        $this->event->delete();

        session()->flash('status', 'イベントを削除しました。');

        $this->redirectRoute('events.index', navigate: true);
    }
};
?>

<div class="mx-auto max-w-2xl space-y-6">
    <flux:heading size="xl">イベントを編集</flux:heading>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">
            <flux:callout.heading>{{ session('status') }}</flux:callout.heading>
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="title" label="タイトル" />
        <flux:textarea wire:model="description" label="説明" rows="6" />
        <flux:input wire:model="venue" label="会場" />

        <div class="grid gap-6 sm:grid-cols-2">
            <flux:input wire:model="starts_at" label="開始日時" type="datetime-local" />
            <flux:input wire:model="ends_at" label="終了日時" type="datetime-local" />
        </div>

        <div class="flex justify-between">
            <flux:button wire:click="delete" wire:confirm="このイベントを削除しますか？" variant="danger" icon="trash">削除</flux:button>
            <div class="flex gap-3">
                <flux:button :href="route('events.index')" variant="ghost" wire:navigate>キャンセル</flux:button>
                <flux:button type="submit" variant="primary">更新する</flux:button>
            </div>
        </div>
    </form>

    <flux:separator />

    <section class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="lg">イベントを複製</flux:heading>
            <flux:text>保存済みの内容で、開催日を1週間後にしたイベントを作ります。券種はコピーしません。</flux:text>
        </div>
        <flux:button wire:click="duplicate" wire:confirm="保存していない変更は複製されません。このイベントを複製しますか？" icon="document-duplicate">イベントを複製</flux:button>
    </section>

    <flux:separator />

    <section class="space-y-4">
        <flux:heading size="lg">券種</flux:heading>

        @if ($this->ticketTypes->isEmpty())
            <flux:text>券種はまだありません。</flux:text>
        @else
            <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        <tr>
                            <th class="px-4 py-2 font-medium">券種名</th>
                            <th class="px-4 py-2 text-right font-medium">価格</th>
                            <th class="px-4 py-2 text-right font-medium">定員</th>
                            <th class="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($this->ticketTypes as $ticketType)
                            <tr wire:key="ticket-type-{{ $ticketType->id }}">
                                <td class="px-4 py-2">{{ $ticketType->name }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($ticketType->price) }}円</td>
                                <td class="px-4 py-2 text-right">{{ number_format($ticketType->capacity) }}人</td>
                                <td class="px-4 py-2 text-right">
                                    <flux:button wire:click="deleteTicketType({{ $ticketType->id }})" wire:confirm="この券種を削除しますか？" size="sm" variant="danger" icon="trash">削除</flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <form wire:submit="addTicketType" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-3">
                <flux:input wire:model="ticketName" label="券種名" placeholder="一般" />
                <flux:input wire:model="ticketPrice" label="価格（円）" type="number" min="0" placeholder="3000" />
                <flux:input wire:model="ticketCapacity" label="定員（人）" type="number" min="1" placeholder="50" />
            </div>

            <div class="flex justify-end">
                <flux:button type="submit" icon="plus">券種を追加</flux:button>
            </div>
        </form>
    </section>
</div>
