{{-- === SOBRE / PERFIL ====================================================== --}}
<section id="sobre" class="max-w-7xl mx-auto px-4 pb-4">
    @php
        // ====== Defaults (você pode mover isso para um ViewModel/Controller depois) ======
        $avatar = $avatar ?? 'https://avatar.iran.liara.run/public';
        $name = $name ?? 'Pedro Felipe';
        $role = $role ?? 'Desenvolvedor Full Stack';
        $location = $location ?? 'Natal - RN, Brasil';
        $company = $company ?? 'CAERN — Companhia de Águas e Esgoto do RN';
        $email = $email ?? ($social['email'] ?? null);

        // Resumo numérico (edite os valores no controller qdo tiver dados reais)
        $highlights = $highlights ?? [
            ['label' => 'Anos de experiência', 'value' => 5],
            ['label' => 'Projetos', 'value' => 20],
            ['label' => 'Tecnologias', 'value' => 12],
            ['label' => 'Certificações', 'value' => 4],
        ];

        // Habilidades principais
        $skills = $skills ?? [
            'Laravel',
            'PHP 8+',
            'MySQL',
            'Redis',
            'Filas',
            'Mail',
            'Blade',
            'Alpine.js',
            'Tailwind',
            'Vite',
            'Git',
            'Docker',
        ];

        // Experiência profissional (timeline)
        /** Estrutura esperada:
         *  [
         *    ['company'=>'','role'=>'','period'=>'','items'=>['','']],
         *  ]
         */
        $experience = $experience ?? [
            [
                'company' => $company,
                'role' => 'Desenvolvedor Full Stack',
                'period' => '2022 — atual',
                'items' => [
                    'Desenvolvimento de APIs REST com Laravel.',
                    'Integrações com Redis, filas e e-mail.',
                    'Code review, padrões SOLID e testes.',
                ],
            ],
            [
                'company' => 'Freelancer',
                'role' => 'Desenvolvedor PHP / JS',
                'period' => '2020 — 2022',
                'items' => ['Migração de projetos para PHP 8 e Laravel.', 'Otimização de consultas MySQL e caching.'],
            ],
        ];

        // Formação acadêmica
        /** Estrutura:
         *  [
         *    ['school'=>'','degree'=>'','period'=>'','notes'=>['','']]
         *  ]
         */
        $education = $education ?? [
            [
                'school' => 'Instituição Exemplo',
                'degree' => 'Tecnologia em Análise e Desenvolvimento de Sistemas',
                'period' => '2018 — 2021',
                'notes' => ['Ênfase em desenvolvimento web', 'Projeto final em Laravel'],
            ],
        ];

        // Certificações / Cursos relevantes
        /** Estrutura:
         *  [
         *    ['name'=>'','issuer'=>'','year'=>'','url'=>null]
         *  ]
         */
        $certifications = $certifications ?? [
            ['name' => 'Laravel Avançado', 'issuer' => 'Curso/Plataforma', 'year' => '2023', 'url' => null],
            ['name' => 'PHP: Boas Práticas', 'issuer' => 'Curso/Plataforma', 'year' => '2022', 'url' => null],
            ['name' => 'Docker para Devs', 'issuer' => 'Curso/Plataforma', 'year' => '2022', 'url' => null],
            ['name' => 'MySQL Performance Tuning', 'issuer' => 'Curso/Plataforma', 'year' => '2021', 'url' => null],
        ];
    @endphp

    {{-- Card de perfil / cabeçalho --}}
    <div class="rounded-2xl border bg-white/70 p-6 shadow-sm">
        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-4">
                <img src="{{ $avatar }}" alt="Foto de perfil" class="h-16 w-16 rounded-2xl object-cover">
                <div>
                    <h3 class="text-xl font-semibold">{{ $name }}</h3>
                    <p class="text-sm text-slate-600">
                        {{ $role }} <span class="mx-1">•</span> {{ $location }}
                    </p>

                    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                        @if ($email)
                            <a href="mailto:{{ $email }}" class="text-slate-600 hover:text-slate-900">✉️
                                {{ $email }}</a>
                        @endif

                        @if (!empty($social['github']))
                            <a href="{{ $social['github'] }}" target="_blank"
                                class="text-slate-600 hover:text-slate-900">🐙 GitHub</a>
                        @endif

                        @if (!empty($social['linkedin']))
                            <a href="{{ $social['linkedin'] }}" target="_blank"
                                class="text-slate-600 hover:text-slate-900">in LinkedIn</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                @if (!empty($cvUrl))
                    <a href="{{ $cvUrl }}" target="_blank"
                        class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">
                        Baixar CV
                    </a>
                @endif
                <a href="#contato"
                    class="inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-sm text-white hover:opacity-90">
                    Entrar em contato
                </a>
            </div>
        </div>

        {{-- Resumo numérico --}}
        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($highlights as $h)
                <div class="rounded-xl border bg-white p-4">
                    <p class="text-2xl font-semibold">{{ $h['value'] }}</p>
                    <p class="text-sm text-slate-600">{{ $h['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- SOBRE MIM --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border bg-white p-6 lg:col-span-2">
            <h4 class="text-lg font-semibold">Sobre mim</h4>
            <p class="mt-3 max-w-3xl text-slate-700 leading-relaxed">
                {{ $about ??
                    'Sou desenvolvedor focado em Laravel, PHP e JavaScript. Gosto de escrever código simples e sustentável, com design de domínio claro, testes e atenção a performance. Tenho interesse por DX, boas práticas (SOLID), automação e produtos com ótima experiência.' }}
            </p>

            {{-- Habilidades --}}
            <div class="mt-6">
                <p class="text-sm font-medium text-slate-700">Habilidades</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span class="rounded-full border bg-slate-50 px-3 py-1 text-xs">{{ $skill }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Cartão lateral com “agora”/empresa (opcional) --}}
        <div class="rounded-2xl border bg-white p-6">
            <h4 class="text-lg font-semibold">Atualmente</h4>
            <p class="mt-3 text-sm text-slate-700">
                {{ $company }}<br>
                <span class="text-slate-500">Time de desenvolvimento • {{ $location }}</span>
            </p>

            @if (!empty($social['github']))
                <a href="{{ $social['github'] }}" target="_blank"
                    class="mt-4 inline-flex rounded-lg border px-3 py-1.5 text-sm hover:bg-slate-50">
                    Ver GitHub
                </a>
            @endif
        </div>
    </div>

    {{-- EXPERIÊNCIA PROFISSIONAL (timeline) --}}
    <div class="mt-10 rounded-2xl border bg-white p-6">
        <div class="flex items-center justify-between">
            <h4 class="text-lg font-semibold">Experiência Profissional</h4>
            {{-- espaço pra um “ver CV” ou “LinkedIn” se quiser --}}
        </div>

        <ol class="mt-6 relative border-s-2 border-slate-200">
            @foreach ($experience as $xp)
                <li class="ms-5 pb-8 last:pb-0">
                    <span
                        class="absolute -start-2.5 mt-1 h-4 w-4 rounded-full border-2 border-white bg-slate-400"></span>
                    <div class="rounded-xl border bg-white p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-lg font-semibold">{{ $xp['role'] }}</p>
                            <span class="text-sm text-slate-600">{{ $xp['period'] }}</span>
                        </div>
                        <p class="text-sm text-slate-600">{{ $xp['company'] }}</p>

                        @if (!empty($xp['items']))
                            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-slate-700">
                                @foreach ($xp['items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </div>

    {{-- FORMAÇÃO ACADÊMICA --}}
    <div class="mt-10 rounded-2xl border bg-white p-6">
        <h4 class="text-lg font-semibold">Formação</h4>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @foreach ($education as $ed)
                <article class="rounded-xl border bg-white p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-medium">{{ $ed['school'] }}</p>
                            <p class="text-sm text-slate-600">{{ $ed['degree'] }}</p>
                        </div>
                        <span class="text-xs text-slate-500">{{ $ed['period'] }}</span>
                    </div>

                    @if (!empty($ed['notes']))
                        <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-slate-700">
                            @foreach ($ed['notes'] as $note)
                                <li>{{ $note }}</li>
                            @endforeach
                        </ul>
                    @endif
                </article>
            @endforeach
        </div>
    </div>

    {{-- CERTIFICAÇÕES / CURSOS --}}
    <div class="mt-10 rounded-2xl border bg-white p-6">
        <h4 class="text-lg font-semibold">Certificações & Cursos</h4>

        @if (!empty($certifications))
            <ul class="mt-4 grid gap-3 md:grid-cols-2">
                @foreach ($certifications as $c)
                    <li class="rounded-xl border bg-white p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium">
                                    @if (!empty($c['url']))
                                        <a href="{{ $c['url'] }}" target="_blank" class="hover:underline">
                                            {{ $c['name'] }}
                                        </a>
                                    @else
                                        {{ $c['name'] }}
                                    @endif
                                </p>
                                <p class="text-sm text-slate-600">{{ $c['issuer'] }}</p>
                            </div>
                            @if (!empty($c['year']))
                                <span class="text-xs text-slate-500">{{ $c['year'] }}</span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="mt-3 text-sm text-slate-600">Adicione suas certificações quando quiser.</p>
        @endif
    </div>
</section>
