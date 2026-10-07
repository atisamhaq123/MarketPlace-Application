<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Discover local finds and give your pre-loved things a new home on MarketPlace.">
    <title>@yield('title', 'Discover local finds') · MarketPlace</title>
    <link rel="stylesheet" href="{{ asset('css/marketplace.css') }}">
    <script src="{{ asset('js/marketplace.js') }}" defer></script>
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<div class="announcement">A little less new. A lot more possibility. <span>Buy and sell in your community ↗</span></div>
<header class="header">
    <div class="container nav">
        <a class="brand" href="{{ route('home') }}"><span class="brand-icon">m<span>·</span></span>marketplace<span class="brand-dot">.</span></a>
        <nav aria-label="Main navigation">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Explore</a>
            @auth
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">My listings</a>
                <form action="{{ route('logout') }}" method="POST">@csrf<button class="text-button">Log out</button></form>
            @else
                <a href="{{ route('login') }}">Log in</a>
            @endauth
            <a class="button small" href="{{ route('listings.create') }}"><span>＋</span> Sell something</a>
        </nav>
    </div>
</header>
<main id="main" class="container">
    @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
    @if($errors->any())
        <div class="alert error" role="alert"><strong>Please check the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @yield('content')
</main>
<footer class="footer container">
    <div><a class="brand" href="{{ route('home') }}">marketplace.</a><p>Good things deserve a second story.</p></div>
    <div>Made for your neighborhood.<br><span>© {{ date('Y') }} MarketPlace</span></div>
</footer>
</body>
</html>