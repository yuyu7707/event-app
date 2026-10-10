<?php

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Livewire\Livewire;

function validEventInput(array $overrides = []): array
{
    return array_merge([
        'title' => '朝焼けトレッキング',
        'description' => '早朝に山頂を目指します。',
        'venue' => '岩手山 馬返し登山口',
        'starts_at' => '2030-05-01T06:00',
        'ends_at' => '2030-05-01T10:00',
    ], $overrides);
}

// 登録

test('organizer can create an event', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::events.create')
        ->set(validEventInput())
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('events.index'));

    expect($user->events()->sole())
        ->title->toBe('朝焼けトレッキング')
        ->venue->toBe('岩手山 馬返し登山口');
});

test('guests cannot open the create page', function () {
    $this->get(route('events.create'))->assertRedirect(route('login'));
});

test('unverified users cannot open the create page', function () {
    $this->actingAs(User::factory()->unverified()->create());

    $this->get(route('events.create'))->assertRedirect(route('verification.notice'));
});

test('event requires all fields when created', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::events.create')
        ->call('save')
        ->assertHasErrors([
            'title' => 'required',
            'description' => 'required',
            'venue' => 'required',
            'starts_at' => 'required',
            'ends_at' => 'required',
        ]);

    expect(Event::count())->toBe(0);
});

test('event rejects values over the maximum length', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::events.create')
        ->set(validEventInput([
            'title' => str_repeat('あ', 101),
            'description' => str_repeat('あ', 2001),
            'venue' => str_repeat('あ', 101),
        ]))
        ->call('save')
        ->assertHasErrors(['title' => 'max', 'description' => 'max', 'venue' => 'max']);

    expect(Event::count())->toBe(0);
});

test('event accepts values exactly at the maximum length', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::events.create')
        ->set(validEventInput([
            'title' => str_repeat('あ', 100),
            'description' => str_repeat('あ', 2000),
            'venue' => str_repeat('あ', 100),
        ]))
        ->call('save')
        ->assertHasNoErrors();

    expect($user->events()->count())->toBe(1);
});

test('event end must be after the start', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::events.create')
        ->set(validEventInput(['ends_at' => '2030-05-01T06:00']))
        ->call('save')
        ->assertHasErrors(['ends_at' => 'after']);

    expect(Event::count())->toBe(0);
});

test('event end one minute after the start is accepted', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::events.create')
        ->set(validEventInput(['ends_at' => '2030-05-01T06:01']))
        ->call('save')
        ->assertHasNoErrors();

    expect(Event::count())->toBe(1);
});

test('event rejects invalid dates', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test('pages::events.create')
        ->set(validEventInput(['starts_at' => 'not-a-date']))
        ->call('save')
        ->assertHasErrors(['starts_at' => 'date']);

    expect(Event::count())->toBe(0);
});

// 編集

test('organizer can update their event', function () {
    $event = Event::factory()->create();
    $this->actingAs($event->user);

    Livewire::test('pages::events.edit', ['event' => $event])
        ->assertSet('title', $event->title)
        ->set(validEventInput(['title' => '更新後のタイトル']))
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('events.index'));

    expect($event->fresh())
        ->title->toBe('更新後のタイトル')
        ->venue->toBe('岩手山 馬返し登山口');
});

test('other users cannot update an event', function () {
    $event = Event::factory()->create();
    $originalTitle = $event->title;
    $this->actingAs($event->user);

    $component = Livewire::test('pages::events.edit', ['event' => $event]);

    $this->actingAs(User::factory()->create());

    $component->set('title', '乗っ取り')->call('save')->assertForbidden();

    expect($event->fresh()->title)->toBe($originalTitle);
});

test('unverified users cannot open the edit page', function () {
    $event = Event::factory()->create();
    $this->actingAs(User::factory()->unverified()->create());

    $this->get(route('events.edit', $event))->assertRedirect(route('verification.notice'));
});

test('event update rejects invalid input', function () {
    $event = Event::factory()->create();
    $originalTitle = $event->title;
    $this->actingAs($event->user);

    Livewire::test('pages::events.edit', ['event' => $event])
        ->set(validEventInput(['title' => '', 'ends_at' => '2030-05-01T05:00']))
        ->call('save')
        ->assertHasErrors(['title' => 'required', 'ends_at' => 'after']);

    expect($event->fresh()->title)->toBe($originalTitle);
});

// 削除

test('organizer can delete their event', function () {
    $event = Event::factory()->create();
    $this->actingAs($event->user);

    Livewire::test('pages::events.edit', ['event' => $event])
        ->call('delete')
        ->assertRedirect(route('events.index'));

    $this->assertModelMissing($event);
});

test('other users cannot delete an event', function () {
    $event = Event::factory()->create();
    $this->actingAs($event->user);

    $component = Livewire::test('pages::events.edit', ['event' => $event]);

    $this->actingAs(User::factory()->create());

    $component->call('delete')->assertForbidden();

    $this->assertModelExists($event);
});

test('guests cannot open the edit page to delete an event', function () {
    $event = Event::factory()->create();

    $this->get(route('events.edit', $event))->assertRedirect(route('login'));

    $this->assertModelExists($event);
});

test('deleting an event keeps other events', function () {
    $event = Event::factory()->create();
    $other = Event::factory()->create();
    TicketType::factory()->for($other)->create();
    $this->actingAs($event->user);

    Livewire::test('pages::events.edit', ['event' => $event])->call('delete');

    $this->assertModelExists($other);
    expect($other->ticketTypes()->count())->toBe(1);
});
