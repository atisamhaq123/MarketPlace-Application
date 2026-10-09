@extends('layouts.app')
@section('title', 'Conversation')
@section('content')
@php($otherUser = auth()->id() === $conversation->buyer_id ? $conversation->seller : $conversation->buyer)
<div class="breadcrumbs"><a href="{{ route('messages.index') }}">Messages</a><span>/</span>{{ $otherUser->name }}</div>
<div class="page-heading heading-row"><div><div class="eyebrow">{{ auth()->id() === $conversation->buyer_id ? 'CHAT WITH SELLER' : 'CHAT WITH BUYER' }}</div><h1>{{ $otherUser->name }}</h1><p>{{ $conversation->listing_title }}</p></div>@if($conversation->listing)<a class="button secondary" href="{{ route('listings.show', $conversation->listing) }}">View listing</a>@endif</div>
<div class="panel conversation-panel">
    @if(!$conversation->listing)<div class="alert">This listing has been removed. Your conversation is still available.</div>@elseif($conversation->listing->status === 'sold')<div class="alert">This item has been sold. You can still reply here.</div>@endif
    <div class="message-thread" aria-label="Message history">
        @forelse($messages->getCollection()->reverse() as $message)
            <article class="message-bubble {{ $message->sender_id === auth()->id() ? 'message-own' : '' }}">
                <strong>{{ $message->sender_id === auth()->id() ? 'You' : $message->sender->name }}</strong>
                <p>{{ $message->body }}</p>
                <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('M j, Y · g:i a') }}</time>
            </article>
        @empty<div class="empty-state"><h3>Say hello</h3><p>Ask a question about this listing or arrange a pickup.</p></div>@endforelse
    </div>
    @include('marketplace.pagination', ['paginator' => $messages])
    <p class="small-note">Newest messages appear on the first page. <a href="{{ route('messages.show', $conversation) }}">Refresh conversation</a> to check for replies.</p>
    <form method="POST" action="{{ route('messages.reply', $conversation) }}" class="message-compose">
        @csrf
        <label for="message-body">Your message</label>
        <textarea id="message-body" name="body" rows="4" maxlength="2000" required placeholder="Hi, is this still available?" aria-describedby="message-help">{{ old('body') }}</textarea>
        <div class="heading-row"><span id="message-help" class="small-note">Up to 2,000 characters.</span><button class="button">Send message</button></div>
    </form>
</div>
@endsection
