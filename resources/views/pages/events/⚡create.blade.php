<?php


use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Title('イベントを登録')] class extends Component {
    
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


         // ログイン中のユーザーのイベントとして保存する（user_id が自動で入る）
    public function save(): void
    {
    $validated = $this->validate();

    auth()->user()->events()->create($validated);

    session()->flash('status', 'イベントを登録しました。');

    $this->redirectRoute('events.index', navigate: true);
    }
}; 
?>


<div class="mx-auto max-w-2xl space-y-6">
    <flux:heading size="xl">イベントを登録</flux:heading>


    <form wire:submit="save" class="space-y-6">
        <flux:input wire:model="title" label="タイトル" placeholder="例: 岩手山 朝焼けトレッキング" />
        <flux:textarea wire:model="description" label="説明" rows="6" />
        <flux:input wire:model="venue" label="会場" placeholder="例: 岩手山 馬返し登山口" />

        <div class="grid gap-6 sm:grid-cols-2">
            <flux:input wire:model="starts_at" label="開始日時" type="datetime-local" />
            <flux:input wire:model="ends_at" label="終了日時" type="datetime-local" />
        </div>
        <div class="flex justify-end gap-3">
            <flux:button :href="route('events.index')" variant="ghost" wire:navigate>キャンセル</flux:button>
            <flux:button type="submit" variant="primary">登録する</flux:button>
        </div>
    </form>
</div>
