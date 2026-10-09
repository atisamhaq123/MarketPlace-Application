<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageRequest;
use App\Models\Conversation;
use App\Models\Listing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $conversations = Conversation::forParticipant($request->user())
            ->with(['buyer', 'seller', 'latestMessage'])
            ->withCount(['messages as unread_count' => fn ($query) => $query->where('sender_id', '!=', $request->user()->id)->whereNull('read_at')])
            ->orderByDesc('updated_at')->orderByDesc('id')->paginate(15);

        return view('messages.index', compact('conversations'));
    }

    public function store(Request $request, Listing $listing): RedirectResponse
    {
        abort_if($request->user()->id === $listing->user_id, 403, 'You cannot message yourself.');
        abort_unless($listing->status === 'available', 422, 'This listing is no longer available.');

        $conversation = Conversation::firstOrCreate(
            ['listing_id' => $listing->id, 'buyer_id' => $request->user()->id],
            ['seller_id' => $listing->user_id, 'listing_title' => $listing->title],
        );

        return redirect()->route('messages.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        Gate::authorize('view', $conversation);
        $conversation->load(['buyer', 'seller', 'listing']);
        $messages = $conversation->messages()->with('sender')->orderByDesc('id')->paginate(50);
        $conversation->messages()->whereIn('id', $messages->pluck('id'))
            ->where('sender_id', '!=', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);

        return view('messages.show', compact('conversation', 'messages'));
    }

    public function reply(MessageRequest $request, Conversation $conversation): RedirectResponse
    {
        DB::transaction(function () use ($request, $conversation): void {
            $conversation->messages()->create([
                'sender_id' => $request->user()->id,
                'body' => $request->validated('body'),
            ]);
            $conversation->touch();
        });

        return redirect()->route('messages.show', $conversation)->with('success', 'Message sent.');
    }
}
