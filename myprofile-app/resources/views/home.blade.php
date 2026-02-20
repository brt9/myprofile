{{-- resources/views/home.blade.php --}}
@extends('layouts.app')

@section('content')
    {{-- HERO --}}
    <section id="home" class="max-w-7xl mx-auto px-4 pb-4">
        <section id="inicio" class="max-w-7xl mx-auto px-4 py-16">
            <div class="grid items-center gap-10 md:grid-cols-2">
                <div>
                    <p class="text-sm uppercase tracking-wider text-slate-500">PT-BR</p>
                    <h1 class="mt-2 text-4xl sm:text-5xl font-extrabold leading-tight">
                        Olá, eu sou <span class="text-slate-900">{{ $name ?? 'Pedro Felipe' }}</span>
                    </h1>
                    <p class="mt-4 text-slate-600">
                        {{ $headline ?? 'Desenvolvedor Full Stack e nas horas vagas gamer.' }}
                    </p>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="#sobre"
                            class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-white hover:opacity-90">
                            Sobre mim
                        </a>
                        <a href="#portfolio"
                            class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 hover:bg-slate-50">
                            Ver projetos
                        </a>
                    </div>
                </div>

                <div class="relative">
                    <div
                        class="flex aspect-[4/3] items-center justify-center rounded-2xl border bg-gradient-to-br from-slate-50 to-slate-100">
                        <span class="text-sm text-slate-500">Sua foto / ilustração</span>
                    </div>
                </div>
            </div>
        </section>


        @include('sections.pc')
        @include('sections.about')
        @include('sections.weather')
        @include('sections.steam')
    </section>
@endsection
