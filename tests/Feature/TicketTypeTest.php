<?php

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;
use Livewire\Livewire;

test('organizer can add a ticket type to their event', function () {
    $event = Event::factory()->create();
    $this->actingAs($event->user);

    Livewire::test('pages::events.edit', ['event' => $event])
        ->set('ticketName', '一般')
        ->set('ticketPrice', '3000')
        ->set('ticketCapacity', '50')
        ->call('addTicketType')
        ->assertHasNoErrors()
        ->assertSee('3,000円');

    expect($event->ticketTypes()->sole())
        ->name->toBe('一般')
        ->price->toBe(3000)
        ->capacity->toBe(50);
});

test('edit page lists the ticket types of the event', function () {
    $event = Event::factory()->create();
    TicketType::factory()->for($event)->create(['name' => '学生', 'price' => 1500, 'capacity' => 20]);
    $this->actingAs($event->user);

    $this->get(route('events.edit', $event))
        ->assertOk()
        ->assertSee('学生')
        ->assertSee('1,500円')
        ->assertSee('20人');
});

test('other users cannot open the edit page to add ticket types', function () {
    $event = Event::factory()->create();
    $this->actingAs(User::factory()->create());

    $this->get(route('events.edit', $event))->assertForbidden();
});

test('ticket type requires a name and a capacity of at least 1', function () {
    $event = Event::factory()->create();
    $this->actingAs($event->user);

    Livewire::test('pages::events.edit', ['event' => $event])
        ->set('ticketName', '')
        ->set('ticketPrice', '1000')
        ->set('ticketCapacity', '0')
        ->call('addTicketType')
        ->assertHasErrors(['ticketName' => 'required', 'ticketCapacity' => 'min']);

    expect($event->ticketTypes()->count())->toBe(0);
});

test('ticket type rejects a negative price and a name over 50 characters', function () {
    $event = Event::factory()->create();
    $this->actingAs($event->user);

    Livewire::test('pages::events.edit', ['event' => $event])
        ->set('ticketName', str_repeat('あ', 51))
        ->set('ticketPrice', '-1')
        ->set('ticketCapacity', '10')
        ->call('addTicketType')
        ->assertHasErrors(['ticketName' => 'max', 'ticketPrice' => 'min']);

    expect($event->ticketTypes()->count())->toBe(0);
});

test('organizer can delete a ticket type', function () {
    $ticketType = TicketType::factory()->create();
    $this->actingAs($ticketType->event->user);

    Livewire::test('pages::events.edit', ['event' => $ticketType->event])
        ->call('deleteTicketType', $ticketType->id);

    $this->assertModelMissing($ticketType);
});

test('other users cannot delete a ticket type', function () {
    $ticketType = TicketType::factory()->create();
    $this->actingAs($ticketType->event->user);

    $component = Livewire::test('pages::events.edit', ['event' => $ticketType->event]);

    $this->actingAs(User::factory()->create());

    $component->call('deleteTicketType', $ticketType->id)->assertForbidden();

    $this->assertModelExists($ticketType);
});

test('other users cannot add a ticket type', function () {
    $event = Event::factory()->create();
    $this->actingAs($event->user);

    $component = Livewire::test('pages::events.edit', ['event' => $event]);

    $this->actingAs(User::factory()->create());

    $component
        ->set('ticketName', '一般')
        ->set('ticketPrice', '3000')
        ->set('ticketCapacity', '50')
        ->call('addTicketType')
        ->assertForbidden();

    expect($event->ticketTypes()->count())->toBe(0);
});

test('guests cannot open the edit page', function () {
    $event = Event::factory()->create();

    $this->get(route('events.edit', $event))->assertRedirect(route('login'));
});

test('a ticket type of another event cannot be deleted', function () {
    $event = Event::factory()->create();
    $otherTicketType = TicketType::factory()->create();
    $this->actingAs($event->user);

    Livewire::test('pages::events.edit', ['event' => $event])
        ->call('deleteTicketType', $otherTicketType->id)
        ->assertNotFound();

    $this->assertModelExists($otherTicketType);
});

test('event list shows the lowest ticket price', function () {
    $event = Event::factory()->create();
    TicketType::factory()->for($event)->create(['price' => 3000]);
    TicketType::factory()->for($event)->create(['price' => 1500]);

    $this->get(route('events.index'))->assertOk()->assertSee('1,500円〜');
});

test('event list shows 無料 when the lowest price is 0', function () {
    $event = Event::factory()->create();
    TicketType::factory()->for($event)->create(['price' => 0]);
    TicketType::factory()->for($event)->create(['price' => 2000]);

    $this->get(route('events.index'))->assertOk()->assertSee('無料')->assertDontSee('円〜');
});

test('event list shows ─ when there are no ticket types', function () {
    Event::factory()->create();

    $this->get(route('events.index'))->assertOk()->assertSee('─');
});

test('deleting an event also deletes its ticket types', function () {
    $ticketType = TicketType::factory()->create();

    $ticketType->event->delete();

    $this->assertModelMissing($ticketType);
});
