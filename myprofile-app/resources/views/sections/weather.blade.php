    {{-- Clima / Tempo --}}
        <section id="clima" class="py-16">
            <h2 class="text-2xl font-bold">Tempo agora</h2>

            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                @foreach ([$weatherNatal ?? null, $weatherVisitor ?? null] as $w)
                    @if ($w)
                        <div class="rounded-2xl border bg-slate-900 text-white p-6 relative overflow-hidden">
                            <div class="flex items-start justify-between">
                                <div class="flex items-start gap-4">
                                    <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white/10 text-2xl">
                                        {{ $w['emoji'] ?? '⛅' }}
                                    </div>
                                    <div>
                                        <div class="text-5xl font-bold leading-none">
                                            {{ isset($w['temp']) ? number_format($w['temp'], 0) : '—' }}°
                                        </div>
                                        <div class="text-sm text-slate-300">
                                            {{ $w['condition'] ?? '—' }}
                                        </div>
                                        @if (!empty($w['updated_at']))
                                            <div class="mt-1 text-xs text-slate-400">
                                                Atualizado
                                                {{ \Carbon\Carbon::parse($w['updated_at'])->locale('pt_BR')->diffForHumans() }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-lg font-medium">{{ $w['label'] ?? '—' }}</p>
                                    <p class="text-xs text-slate-400">Brasil</p>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                <div class="rounded-xl bg-blue-900/40 p-3">
                                    <p class="text-xs text-blue-200">Vento</p>
                                    <p class="font-semibold">{{ $w['wind_kmh'] ?? '—' }} km/h</p>
                                </div>
                                <div class="rounded-xl bg-teal-900/40 p-3">
                                    <p class="text-xs text-teal-200">Umidade</p>
                                    <p class="font-semibold">{{ $w['humidity'] ?? '—' }}%</p>
                                </div>
                                <div class="rounded-xl bg-amber-900/40 p-3">
                                    <p class="text-xs text-amber-200">Sensação</p>
                                    <p class="font-semibold">
                                        {{ isset($w['feels_like']) ? number_format($w['feels_like'], 0) : '—' }}°
                                    </p>
                                </div>
                                <div class="rounded-xl bg-violet-900/40 p-3">
                                    <p class="text-xs text-violet-200">Pressão</p>
                                    <p class="font-semibold">{{ $w['pressure'] ?? '—' }} mb</p>
                                </div>
                                <div class="rounded-xl bg-yellow-900/40 p-3">
                                    <p class="text-xs text-yellow-200">Índice UV</p>
                                    <p class="font-semibold">{{ $w['uv'] ?? '—' }}</p>
                                </div>
                                <div class="rounded-xl bg-slate-800 p-3">
                                    <p class="text-xs text-slate-300">Nebulosidade</p>
                                    <p class="font-semibold">{{ $w['clouds'] ?? '—' }}%</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>