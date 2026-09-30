<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Don intelligent') · TexTileCycle</title>
    {{-- Tailwind via CDN pour démarrer sans build. En production : Vite + Tailwind (voir README). --}}
    <script src="https://cdn.tailwindcss.com"></script>
    @yield('head')
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">

<header class="border-b border-stone-200 bg-white">
    <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-3">
        <a href="{{ route('don.home') }}" class="text-lg font-semibold text-emerald-700">♻ TexTileCycle</a>

        <nav class="flex flex-wrap items-center gap-4 text-sm">
            @auth
                @if (auth()->user()->isParticulier())
                    <a href="{{ route('donations.index') }}" class="hover:text-emerald-700">Mes dons</a>
                    <a href="{{ route('donations.create') }}" class="rounded-md bg-emerald-600 px-3 py-1.5 font-medium text-white hover:bg-emerald-700">Faire un don</a>
                @elseif (auth()->user()->isAssociation())
                    <a href="{{ route('association.dashboard') }}" class="hover:text-emerald-700">Tableau de bord</a>
                    <a href="{{ route('association.needs.index') }}" class="hover:text-emerald-700">Nos besoins</a>
                    <a href="{{ route('association.profile.edit') }}" class="hover:text-emerald-700">Profil</a>
                @elseif (auth()->user()->isAdmin())
                    <a href="{{ route('admin.associations.index') }}" class="hover:text-emerald-700">Associations</a>
                @endif

                @if (Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-stone-500 hover:text-stone-800">Déconnexion</button>
                    </form>
                @endif
            @endauth
        </nav>
    </div>
</header>

<main class="mx-auto max-w-5xl px-4 py-8">
    @if (session('status'))
        <div class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

</body>
</html>
