<?php

use App\Models\Event;
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

    public function mount(Event $event): void
    {
        abort_unless($event->isOwnedBy(auth()->user()), 403);

        $this->event = $event;
        $this->title = $event->title;
        $this->description = $event->description;
        $this->venue = $event->venue;
        $this->starts_at = $event->starts_at->format('Y-m-d\TH:i');
        $this->ends_at = $event->ends_at->format('Y-m-d\TH:i');
    }

    public function save(): void
    {

        abort_unless($this->event->isOwnedBy(auth()->user()), 403);

        $this->event->update($this->validate());

        session()->flash('status', 'イベントを更新しました。');

        $this->redirectRoute('events.index', navigate: true);
    }

    public function delete(): void
    {

        abort_unless($this->event->isOwnedBy(auth()->user()), 403);
        
        $this->event->delete();

        session()->flash('status', 'イベントを削除しました。');

        $this->redirectRoute('events.index', navigate: true);
    }
}; 
?>

<div class="mx-auto max-w-2xl space-y-6">
    <flux:heading size="xl">イベントを編集</flux:heading>

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
</div>
