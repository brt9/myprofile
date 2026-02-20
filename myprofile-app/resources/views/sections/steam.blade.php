 {{-- Steam --}}
 <section id="steam" class="py-16">
     <div class="flex items-end justify-between gap-4">
         <h2 class="text-2xl font-bold">Steam</h2>
         <a href="{{ $steamProfile ?? 'https://steamcommunity.com/profiles/76561198001819175' }}" target="_blank"
             class="text-sm text-slate-600 hover:text-slate-900">
             Ver perfil
         </a>
     </div>

     {{-- Jogando agora --}}
     @if (!empty($currentGame))
         <div class="mt-6 overflow-hidden rounded-xl border bg-white">
             <div class="flex flex-col sm:flex-row">
                 <img src="{{ $currentGame['image'] }}" alt="{{ $currentGame['name'] }}"
                     class="h-48 w-full object-cover sm:h-40 sm:w-80"
                     onerror="this.onerror=null;this.src='https://cdn.cloudflare.steamstatic.com/steam/apps/{{ $currentGame['appid'] }}/capsule_616x353.jpg';">
                 <div class="flex-1 p-4">
                     <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Jogando agora</p>
                     <h3 class="mt-1 text-xl font-semibold">{{ $currentGame['name'] }}</h3>
                     <a href="steam://run/{{ $currentGame['appid'] }}"
                         class="mt-3 inline-flex rounded-lg bg-slate-900 px-3 py-1.5 text-white hover:opacity-90">
                         Abrir no Steam
                     </a>
                 </div>
             </div>
         </div>
     @endif

     {{-- Resumo --}}
     @if (!empty($steamSummary))
         <div class="mt-6 grid gap-4 sm:grid-cols-3">
             <div class="rounded-xl border bg-white p-4">
                 <p class="text-sm text-slate-500">Jogos na biblioteca</p>
                 <p class="mt-1 text-2xl font-semibold">{{ number_format($steamSummary['game_count'] ?? 0) }}</p>
             </div>
             <div class="rounded-xl border bg-white p-4">
                 <p class="text-sm text-slate-500">Tempo total</p>
                 <p class="mt-1 text-2xl font-semibold">
                     {{ number_format(($steamSummary['total_minutes'] ?? 0) / 60, 1) }} h
                 </p>
             </div>
             <div class="rounded-xl border bg-white p-4">
                 <p class="text-sm text-slate-500">Top jogados</p>
                 <p class="mt-1 text-sm">
                     @foreach ($steamSummary['top'] ?? [] as $g)
                         <span class="inline-block mr-2 mb-1">
                             {{ $g['name'] }} ({{ number_format($g['playtime'] / 60, 0) }}h)
                         </span>
                     @endforeach
                 </p>
             </div>
         </div>
     @endif

     {{-- Jogos recentes --}}
     <div class="mt-8">
         <h3 class="text-lg font-semibold">Jogos recentes</h3>
         <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
             @forelse (($recentGames ?? []) as $g)
                 <article class="rounded-2xl border bg-white overflow-hidden">
                     <div class="aspect-video bg-slate-100">
                         <img src="{{ $g['image'] }}" alt="{{ $g['name'] }}" class="w-full h-full object-cover"
                             onerror="this.onerror=null;this.src='{{ $g['capsule'] }}';">
                     </div>
                     <div class="p-4">
                         <p class="font-medium">{{ $g['name'] }}</p>
                         <p class="text-sm text-slate-600">
                             {{ number_format(($g['playtime'] ?? 0) / 60, 1) }} h
                         </p>
                     </div>
                 </article>
             @empty
                 <div class="col-span-full rounded-xl border bg-white p-6 text-slate-600">
                     Nada recente disponível.
                 </div>
             @endforelse
         </div>
     </div>

     {{-- Conquistas do jogo em destaque --}}
     @if (!empty($featuredAchievements))
         <div class="mt-12">
             <h3 class="text-lg font-semibold">Conquistas recentes</h3>
             <ul class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                 @foreach ($featuredAchievements as $a)
                     <li class="flex items-center gap-3 rounded-xl border bg-white p-3">
                         @if (!empty($a['icon']))
                             <img src="{{ $a['icon'] }}" class="h-8 w-8 rounded" alt="">
                         @else
                             <div class="h-8 w-8 rounded bg-slate-200"></div>
                         @endif
                         <div class="min-w-0">
                             <p class="truncate font-medium">{{ $a['name'] }}</p>
                             @if (!empty($a['unlock_time']))
                                 <p class="text-xs text-slate-500">
                                     {{ \Carbon\Carbon::createFromTimestamp($a['unlock_time'])->diffForHumans() }}
                                 </p>
                             @endif
                         </div>
                     </li>
                 @endforeach
             </ul>
         </div>
     @endif
 </section>
