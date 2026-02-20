{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="pt-BR" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'MeuPortfólio') }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Portfólio, projetos e jogos da Steam.' }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- Estilos e scripts do Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>

<body class="min-h-screen bg-white text-slate-800 antialiased">

    {{-- Link de pulo para acessibilidade --}}
    <a href="#conteudo"
        class="sr-only focus:not-sr-only focus:absolute focus:m-4 focus:rounded-lg focus:bg-white focus:px-3 focus:py-2 focus:shadow">
        Pular para o conteúdo
    </a>

    {{-- Apenas o conteúdo das páginas (Home, etc.) --}}
    <main id="conteudo">
        @yield('content')
    </main>

    {{-- Espaços opcionais para injetar modais e scripts por página --}}
    @yield('modals')
    @stack('scripts')
</body>

</html>
