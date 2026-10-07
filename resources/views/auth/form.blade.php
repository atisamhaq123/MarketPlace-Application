@extends('layouts.app')
@section('title', $register ? 'Create an account' : 'Log in')
@section('content')
<div class="auth-wrap"><aside><div class="eyebrow">GOOD THINGS START HERE</div><h1>A community<br>of possibilities<span>.</span></h1><p>Find a little treasure. Share a little joy.<br>Make yourself at home.</p><div class="auth-art tone-home"><x-product-art icon="home"/></div></aside>
<section class="panel auth-panel"><h2>{{ $register ? 'Join the neighborhood.' : 'Welcome back.' }}</h2><p>{{ $register ? 'Create your account to start buying and selling.' : 'Log in to manage listings and contact sellers.' }}</p>
<form action="{{ $register ? route('register') : route('login') }}" method="POST">@csrf
@if($register)<label>Your name<input name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name"></label>@endif
<label>Email address<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"></label>
<label>Password<input type="password" name="password" required @if($register) minlength="8" autocomplete="new-password" @else autocomplete="current-password" @endif></label>
@if($register)<label>Confirm password<input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></label>@else<label class="checkbox-label"><input type="checkbox" name="remember" value="1"> Keep me logged in</label>@endif
<button class="button full">{{ $register ? 'Create account' : 'Log in' }} →</button>
</form><div class="auth-switch">{{ $register ? 'Already part of the community?' : 'New to the neighborhood?' }} <a href="{{ $register ? route('login') : route('register') }}">{{ $register ? 'Log in' : 'Create an account' }}</a></div>
</section></div>
@endsection