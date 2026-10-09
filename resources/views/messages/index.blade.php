@extends('layouts.app')
@section('title', 'Messages')
@section('content')
<div class="page-heading"><div class="eyebrow">YOUR CONVERSATIONS</div><h1>Messages</h1><p>Ask about a find, arrange pickup, and stay in touch.</p></div>
<div class="panel inbox-panel">
    @forelse($conversations as $conversation)
        @php($otherUser = auth()->id() === $conversation->buyer_id ? $conversation->seller : $conversation->buyer)
        <a class="conversation-row" href="{{ route('messages.show', $conversation) }}">
            <div class="avatar">{{ mb_substr($otherUser->name, 0, 1) }}</div>
            <div class="conversation-info"><strong>{{ $otherUser->name }}</strong><span>{{ $conversation->listing_title }}</span><p>{{ $conversation->latestMessage ? Illuminate\Support\Str::limit($conversation->latestMessage->body, 100) : 'Start the conversation.' }}</p></div>
            <div class="conversation-meta"><time datetime="{{ $conversation->updated_at->toIso8601String() }}">{{ $conversation->updated_at->diffForHumans() }}</time>@if($conversation->unread_count)<span class="pill">{{ $conversation->unread_count }} unread</span>@endif</div>
        </a>
    @empty
        <div class="empty-state"><h3>No conversations yet</h3><p>Open a listing and choose Message seller to get started.</p><a class="button" href="{{ route('home') }}">Explore listings</a></div>
    @endforelse
    @include('marketplace.pagination', ['paginator' => $conversations])
</div>
@endsection
