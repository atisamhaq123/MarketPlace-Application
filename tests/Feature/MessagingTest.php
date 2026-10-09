<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Listing;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_must_log_in_to_access_messaging(): void
    {
        $conversation = Conversation::factory()->create();
        $this->get(route('messages.index'))->assertRedirect(route('login'));
        $this->get(route('messages.show', $conversation))->assertRedirect(route('login'));
        $this->post(route('messages.start', $conversation->listing))->assertRedirect(route('login'));
        $this->post(route('messages.reply', $conversation), ['body' => 'Hello'])->assertRedirect(route('login'));
        $this->assertDatabaseCount('messages', 0);
    }

    public function test_buyer_can_start_and_reopen_a_single_conversation_for_a_listing(): void
    {
        $listing = Listing::factory()->create();
        $buyer = User::factory()->create();
        $this->actingAs($buyer)->get(route('listings.show', $listing))->assertSee('Message seller');
        $this->post(route('messages.start', $listing), ['seller_id' => $buyer->id])->assertRedirect();
        $conversation = Conversation::firstOrFail();
        $this->assertSame($listing->user_id, $conversation->seller_id);
        $this->assertSame($buyer->id, $conversation->buyer_id);
        $this->post(route('messages.start', $listing))->assertRedirect(route('messages.show', $conversation));
        $this->assertDatabaseCount('conversations', 1);
        $this->get(route('messages.show', $conversation))->assertOk()->assertSee('Say hello');
    }

    public function test_seller_cannot_message_self_and_sold_listings_cannot_start_conversations(): void
    {
        $listing = Listing::factory()->create();
        $this->actingAs($listing->user)->get(route('listings.show', $listing))->assertDontSee('Message seller');
        $this->post(route('messages.start', $listing))->assertForbidden();
        $listing->update(['status' => 'sold']);
        $this->actingAs(User::factory()->create())->post(route('messages.start', $listing))->assertStatus(422);
        $this->assertDatabaseCount('conversations', 0);
    }

    public function test_buyer_and_seller_can_reply_without_spoofing_sender(): void
    {
        $conversation = Conversation::factory()->create();
        $this->actingAs($conversation->buyer)->post(route('messages.reply', $conversation), [
            'body' => '  Is it available?  ', 'sender_id' => $conversation->seller_id, 'read_at' => now(),
        ])->assertRedirect(route('messages.show', $conversation));
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id, 'sender_id' => $conversation->buyer_id,
            'body' => 'Is it available?', 'read_at' => null,
        ]);
        $this->actingAs($conversation->seller)->post(route('messages.reply', $conversation), ['body' => 'Yes, it is.'])->assertRedirect();
        $this->get(route('messages.show', $conversation))->assertOk()->assertSeeInOrder(['Is it available?', 'Yes, it is.']);
    }

    public function test_unrelated_users_cannot_read_reply_or_mark_messages_read(): void
    {
        $message = Message::factory()->create(['body' => 'Private pickup address']);
        $this->actingAs(User::factory()->create())->get(route('messages.show', $message->conversation))->assertForbidden();
        $this->post(route('messages.reply', $message->conversation), ['body' => 'Intruder'])->assertForbidden();
        $this->get(route('messages.index'))->assertOk()->assertDontSee('Private pickup address');
        $this->assertNull($message->fresh()->read_at);
        $this->assertDatabaseCount('messages', 1);
    }

    public function test_inbox_lists_only_participating_conversations_and_marks_incoming_messages_read(): void
    {
        $message = Message::factory()->create(['body' => 'Can I collect tomorrow?']);
        Message::factory()->create(['body' => 'Someone else private message']);
        $conversation = $message->conversation;
        $this->actingAs($conversation->seller)->get(route('messages.index'))->assertOk()
            ->assertSee('Can I collect tomorrow?')->assertSee('1 unread')->assertDontSee('Someone else private message');
        $this->get(route('messages.show', $conversation))->assertOk();
        $this->assertNotNull($message->fresh()->read_at);
        $this->get(route('messages.index'))->assertDontSee('1 unread');
        $reply = Message::factory()->for($conversation)->create(['sender_id' => $conversation->seller_id]);
        $this->get(route('messages.show', $conversation))->assertOk();
        $this->assertNull($reply->fresh()->read_at);
    }

    public function test_messages_validate_content_and_escape_html(): void
    {
        $conversation = Conversation::factory()->create();
        $this->actingAs($conversation->buyer);
        foreach (['', '   ', str_repeat('a', 2001), ['invalid']] as $body) {
            $this->post(route('messages.reply', $conversation), ['body' => $body])->assertSessionHasErrors('body');
        }
        $this->assertDatabaseCount('messages', 0);
        $this->post(route('messages.reply', $conversation), ['body' => '<script>alert("test")</script>'])->assertRedirect();
        $this->get(route('messages.show', $conversation))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert', false);
    }

    public function test_conversation_history_survives_listing_deletion_and_allows_replies(): void
    {
        $message = Message::factory()->create(['body' => 'Saved conversation']);
        $conversation = $message->conversation;
        $conversation->listing->delete();
        $this->assertNull($conversation->fresh()->listing_id);
        $this->actingAs($conversation->seller)->get(route('messages.show', $conversation))->assertOk()
            ->assertSee('This listing has been removed.')->assertSee('Saved conversation');
        $this->post(route('messages.reply', $conversation), ['body' => 'Thanks for buying'])->assertRedirect();
        $this->assertDatabaseCount('messages', 2);
    }

    public function test_message_pagination_marks_only_displayed_incoming_messages_read(): void
    {
        $conversation = Conversation::factory()->create();
        $oldest = Message::factory()->for($conversation)->create(['body' => 'Oldest message']);
        Message::factory()->count(50)->for($conversation)->create();
        $this->actingAs($conversation->seller)->get(route('messages.show', $conversation))->assertOk()->assertDontSee('Oldest message');
        $this->assertNull($oldest->fresh()->read_at);
        $this->assertSame(50, $conversation->messages()->whereNotNull('read_at')->count());
        $this->get(route('messages.show', ['conversation' => $conversation, 'page' => 2]))->assertOk()->assertSee('Oldest message');
        $this->assertNotNull($oldest->fresh()->read_at);
    }

    public function test_message_sending_is_rate_limited(): void
    {
        $conversation = Conversation::factory()->create();
        $this->actingAs($conversation->buyer);
        for ($index = 0; $index < 30; $index++) {
            $this->post(route('messages.reply', $conversation), ['body' => 'Hello'])->assertRedirect();
        }
        $this->post(route('messages.reply', $conversation), ['body' => 'Too many'])->assertStatus(429);
        $this->assertDatabaseCount('messages', 30);
    }
}
