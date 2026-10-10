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

test('deleting an event also deletes its ticket types', function () {
    $ticketType = TicketType::factory()->create();

    $ticketType->event->delete();

    $this->assertModelMissing($ticketType);
});
