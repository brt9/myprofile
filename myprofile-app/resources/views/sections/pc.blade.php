{{-- resources/views/sections/pc.blade.php --}}
<section id="setup" class="max-w-7xl mx-auto px-4 py-16">
    <header class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Setup</p>
        <h2 class="mt-1 text-3xl font-bold text-slate-900">Minha Máquina</h2>
        <p class="mt-2 max-w-2xl text-slate-600">
            Configuração focada em desenvolvimento (Laravel/PHP, Vite/Tailwind) e jogos em 1080p/1440p.
        </p>
    </header>

    @php
        // Resumo rápido
        $chips = [
            ['label' => 'CPU', 'value' => 'Core i5-14600K'],
            ['label' => 'AIO', 'value' => 'iCUE LINK H100i LCD (240mm)'],
            ['label' => 'GPU', 'value' => 'RTX 4060 Ti 8GB'],
            ['label' => 'RAM', 'value' => '64GB (4×16) DDR5-6200 CL36'],
            ['label' => 'Armazenamento', 'value' => 'NVMe 1TB (Kingston NV2)'],
        ];

        // Especificações (sem links)
        $pc = [
            [
                'grupo' => 'Processador',
                'modelo' => 'Intel Core i5-14600K (14ª geração)',
                'detalhes' => '14 núcleos • 20 threads • Turbo até 5.3 GHz • LGA1700',
                'icone' => '🧠',
                'tag' => 'CPU',
            ],
            [
                'grupo' => 'Refrigeração líquida',
                'modelo' => 'Corsair iCUE LINK H100i LCD RGB (240mm)',
                'detalhes' => 'Radiador 240 mm • tela LCD • RGB • compatível Intel/AMD',
                'icone' => '💧',
                'tag' => 'AIO',
            ],
            [
                'grupo' => 'Placa-mãe',
                'modelo' => 'ASUS ROG Strix B760-F Gaming Wi-Fi (DDR5)',
                'detalhes' => 'ATX • LGA1700 • DDR5 • Wi-Fi',
                'icone' => '🖧',
                'tag' => 'Motherboard',
            ],
            [
                'grupo' => 'Memória',
                'modelo' => 'Corsair Dominator Platinum RGB 64GB (4×16)',
                'detalhes' => 'DDR5 • 6200 MHz • CL36',
                'icone' => '💾',
                'tag' => 'RAM',
            ],
            [
                'grupo' => 'Armazenamento',
                'modelo' => 'Kingston NV2 1TB (M.2 2280 NVMe)',
                'detalhes' => 'PCIe 4.0 • ~3500 MB/s leitura • ~2100 MB/s gravação',
                'icone' => '🗄️',
                'tag' => 'NVMe',
            ],
            [
                'grupo' => 'Placa de vídeo',
                'modelo' => 'ASUS Dual GeForce RTX 4060 Ti OC 8GB',
                'detalhes' => 'GDDR6 • DLSS • Ray Tracing',
                'icone' => '🎮',
                'tag' => 'GPU',
            ],
            [
                'grupo' => 'Gabinete',
                'modelo' => 'Corsair iCUE 4000X RGB (Mid Tower)',
                'detalhes' => 'Vidro temperado frontal e lateral • 3 fans RGB',
                'icone' => '🖥️',
                'tag' => 'Case',
            ],
        ];
    @endphp

    {{-- Resumo rápido --}}
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($chips as $c)
            <div class="rounded-2xl border bg-white p-4">
                <p class="text-xs uppercase tracking-wider text-slate-500">{{ $c['label'] }}</p>
                <p class="mt-1 text-lg font-semibold">{{ $c['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Telemetria ao vivo (com histórico) --}}
    <div x-data="telemetryBox()" x-init="init()" class="mt-6 rounded-2xl border bg-slate-900 text-white p-5">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs uppercase tracking-wider text-slate-400">Telemetria em tempo real</p>
                <h3 class="mt-1 text-xl font-semibold">Temperaturas / Carga</h3>
            </div>
            <span class="rounded-full px-2.5 py-0.5 text-xs"
                :class="stale ? 'bg-amber-500/20 text-amber-200' : 'bg-emerald-500/20 text-emerald-200'"
                x-text="stale ? 'offline' : 'online'"></span>
        </div>

        <!-- Cards: renderiza só os campos que existem (após carry-over) -->
        <template
            x-if="has('cpu_temp') || has('cpu_load') || has('gpu_temp') || has('gpu_load') || has('pump_rpm') || has('coolant_temp')">
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <template x-if="has('cpu_temp') || has('cpu_load')">
                    <div class="rounded-xl bg-white/10 p-4">
                        <p class="text-xs text-slate-300">CPU</p>
                        <p class="mt-1 text-2xl font-semibold" x-show="has('cpu_temp')"
                            x-text="fmt1(curr.cpu_temp) + '°C'"></p>
                        <p class="text-xs text-slate-400" x-show="has('cpu_load')"
                            x-text="fmt1(curr.cpu_load) + '% carga'"></p>
                    </div>
                </template>

                <template x-if="has('gpu_temp') || has('gpu_load')">
                    <div class="rounded-xl bg-white/10 p-4">
                        <p class="text-xs text-slate-300">GPU</p>
                        <p class="mt-1 text-2xl font-semibold" x-show="has('gpu_temp')"
                            x-text="fmt1(curr.gpu_temp) + '°C'"></p>
                        <p class="text-xs text-slate-400" x-show="has('gpu_load')"
                            x-text="fmt1(curr.gpu_load) + '% carga'"></p>
                    </div>
                </template>

                <template x-if="has('pump_rpm')">
                    <div class="rounded-xl bg-white/10 p-4">
                        <p class="text-xs text-slate-300">Bomba AIO</p>
                        <p class="mt-1 text-2xl font-semibold" x-text="fmt0(curr.pump_rpm) + ' RPM'"></p>
                    </div>
                </template>

                <template x-if="has('coolant_temp')">
                    <div class="rounded-xl bg-white/10 p-4">
                        <p class="text-xs text-slate-300">Coolant</p>
                        <p class="mt-1 text-2xl font-semibold" x-text="fmt1(curr.coolant_temp) + '°C'"></p>
                    </div>
                </template>
            </div>
        </template>

        <p class="mt-3 text-xs text-slate-400" x-show="curr?.updated_at">
            Atualizado <span x-text="new Date(curr.updated_at).toLocaleString()"></span>
        </p>

        <!-- Gráfico de histórico -->
        <div class="mt-4 rounded-xl bg-white p-4">
            <canvas id="telemetryChart" height="140"></canvas>
        </div>

        <!-- Debug opcional -->
        <div class="mt-4 rounded-xl bg-black/30 p-3 text-xs">
            <div class="mb-2 flex items-center gap-2">
                <button @click="load(true)" class="rounded border border-white/20 px-2 py-1">Forçar atualização</button>
                <span class="text-slate-400">URL: <code>{{ route('telemetry.show', [], false) }}</code></span>
            </div>
            <pre class="whitespace-pre-wrap" x-text="pretty(curr)"></pre>
        </div>
    </div>

    <!-- Chart.js (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>

    <script>
        function telemetryBox() {
            const HISTORY_KEY = 'telemetry_history_v2'; // v2 pra evitar conflito com versões antigas
            const MAX_POINTS = 120; // ~20 min se POLL_MS=10s

            return {
                // estado
                curr: null, // amostra atual (com carry-over)
                stale: true,
                err: false,
                timer: null,
                chart: null,
                seriesOrder: ['cpu_temp', 'gpu_temp', 'cpu_load', 'gpu_load', 'pump_rpm', 'coolant_temp'],
                history: [], // [{ts, cpu_temp?, gpu_temp?, ...}]

                // utils
                n(x) {
                    const v = Number(x);
                    return Number.isFinite(v) ? v : null;
                },
                fmt1(x) {
                    const v = this.n(x);
                    return v == null ? '—' : v.toFixed(1);
                },
                fmt0(x) {
                    const v = this.n(x);
                    return v == null ? '—' : Math.round(v);
                },
                pretty(o) {
                    try {
                        return JSON.stringify(o, null, 2)
                    } catch {
                        return String(o)
                    }
                },
                has(key) {
                    return this.curr && this.curr[key] != null;
                },

                // persiste/restaura histórico
                saveHistory() {
                    try {
                        localStorage.setItem(HISTORY_KEY, JSON.stringify(this.history));
                    } catch {}
                },
                loadHistory() {
                    try {
                        const raw = localStorage.getItem(HISTORY_KEY);
                        if (raw) this.history = JSON.parse(raw) || [];
                    } catch {
                        this.history = [];
                    }
                },

                // cria/atualiza o gráfico
                ensureChart() {
                    if (!window.Chart) return; // Chart.js ainda carregando
                    const ctx = document.getElementById('telemetryChart').getContext('2d');

                    // datasets apenas para séries que existem em algum ponto do histórico
                    const labels = this.history.map(p => new Date(p.ts).toLocaleTimeString());
                    const buildData = (key) => this.history.map(p => this.n(p[key]));

                    const availableKeys = this.seriesOrder.filter(k =>
                        this.history.some(p => this.n(p[k]) != null)
                    );

                    const datasetFor = (key) => ({
                        label: ({
                            cpu_temp: 'CPU °C',
                            gpu_temp: 'GPU °C',
                            cpu_load: 'CPU %',
                            gpu_load: 'GPU %',
                            pump_rpm: 'Bomba RPM',
                            coolant_temp: 'Coolant °C'
                        })[key] || key,
                        data: buildData(key),
                        tension: 0.25,
                        pointRadius: 0,
                        borderWidth: 2,
                        spanGaps: true, // ignora buracos
                    });

                    if (!this.chart) {
                        this.chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels,
                                datasets: availableKeys.map(datasetFor),
                            },
                            options: {
                                animation: false,
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        labels: {
                                            boxWidth: 10
                                        }
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false
                                    }
                                },
                                scales: {
                                    x: {
                                        display: true,
                                        ticks: {
                                            maxTicksLimit: 8
                                        }
                                    },
                                    y: {
                                        beginAtZero: false
                                    }
                                }
                            }
                        });
                    } else {
                        this.chart.data.labels = labels;

                        // reconstroi datasets pra refletir quais chaves existem
                        this.chart.data.datasets = availableKeys.map(datasetFor);
                        this.chart.update('none');
                    }
                },

                // normaliza amostra com carry-over do último ponto
                carryOver(newSample) {
                    const last = this.history.length ? this.history[this.history.length - 1] : null;
                    const keys = ['cpu_temp', 'gpu_temp', 'cpu_load', 'gpu_load', 'pump_rpm', 'coolant_temp'];
                    const out = {
                        ts: Date.now()
                    };
                    for (const k of keys) {
                        const v = newSample[k];
                        out[k] = (v == null) ?
                            (last ? last[k] ?? null : null) :
                            this.n(v);
                    }
                    // timestamp/online
                    out.updated_at = newSample.updated_at || (new Date()).toISOString();
                    out.stale = !!newSample.stale;
                    return out;
                },

                // carrega do backend
                async load(verbose = false) {
                    try {
                        const url = '{{ route('telemetry.show', [], false) }}';
                        const r = await fetch(url, {
                            cache: 'no-store'
                        });
                        if (!r.ok) throw new Error(`HTTP ${r.status}`);
                        const j = await r.json();
                        if (verbose) console.log('[telemetry] GET', url, j);

                        // aplica carry-over para não exibir null
                        const sample = this.carryOver(j);

                        // guarda no histórico
                        this.history.push(sample);
                        if (this.history.length > MAX_POINTS) this.history.splice(0, this.history.length - MAX_POINTS);
                        this.saveHistory();

                        // estado atual e flags
                        this.curr = sample;
                        this.stale = !!j.stale;
                        this.err = false;

                        // atualiza gráfico
                        this.ensureChart();

                    } catch (e) {
                        console.error('[telemetry] erro:', e);
                        this.err = true;
                        this.stale = true;

                        // mesmo com erro, mantém último ponto (se existir) na tela
                        if (this.history.length && !this.curr) {
                            this.curr = this.history[this.history.length - 1];
                        }
                    }
                },

                init() {
                    // histórico prévio
                    this.loadHistory();
                    if (this.history.length) {
                        this.curr = this.history[this.history.length - 1];
                        this.stale = !!this.curr.stale;
                        // tenta desenhar um gráfico com o que já tem
                        this.$nextTick(() => this.ensureChart());
                    }

                    // primeira carga + polling
                    this.load(true);
                    this.timer = setInterval(() => this.load(false), 10000);

                    // foco/aba visível = força refresh
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) this.load(true);
                    });
                }
            }
        }
    </script>



    {{-- Especificações --}}
    <div class="mt-6 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @foreach ($pc as $p)
            <article class="group rounded-2xl border bg-white p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-500">{{ $p['grupo'] }}</p>
                        <h3 class="mt-1 font-semibold leading-snug">{{ $p['modelo'] }}</h3>
                        <p class="mt-1 text-sm text-slate-600">{{ $p['detalhes'] }}</p>
                    </div>
                    <span class="shrink-0 grid h-10 w-10 place-items-center rounded-xl border bg-slate-50">
                        <span class="text-lg" aria-hidden="true">{{ $p['icone'] }}</span>
                    </span>
                </div>
                <div class="mt-4">
                    <span class="inline-flex items-center rounded-full border bg-slate-50 px-2.5 py-0.5 text-xs">
                        {{ $p['tag'] }}
                    </span>
                </div>
            </article>
        @endforeach
    </div>
</section>
