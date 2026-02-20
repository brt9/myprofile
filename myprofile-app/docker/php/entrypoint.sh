#!/bin/bash

# Aguardar um pouco para garantir que todos os volumes estejam montados
sleep 3

# Função para verificar se um processo está rodando
is_process_running() {
    pgrep -f "$1" > /dev/null 2>&1
}

# Função para matar processos anteriores do Vite
kill_vite_processes() {
    pkill -f "vite" 2>/dev/null || true
    pkill -f "npm run dev" 2>/dev/null || true
}

echo "=== Iniciando configuração do container ==="

# ===== DEPENDÊNCIAS PHP =====
if [ ! -d "/var/www/vendor" ] || [ -z "$(ls -A /var/www/vendor 2>/dev/null)" ]; then
    echo "📦 Instalando dependências PHP..."
    composer install --no-interaction --optimize-autoloader
else
    echo "✅ Dependências PHP já instaladas. Otimizando autoloader..."
    composer dump-autoload --optimize --no-interaction
fi

# ===== DEPENDÊNCIAS NODE.JS =====
if [ -f "/var/www/package.json" ]; then
    echo "🔍 Verificando dependências Node.js..."
    
    # Verificar se node_modules precisa ser instalado
    NEEDS_INSTALL=false
    
    if [ ! -d "/var/www/node_modules" ] || [ -z "$(ls -A /var/www/node_modules 2>/dev/null)" ]; then
        NEEDS_INSTALL=true
    elif [ "/var/www/package.json" -nt "/var/www/node_modules/.package-lock.json" ] 2>/dev/null; then
        echo "📝 package.json foi modificado, reinstalando dependências..."
        NEEDS_INSTALL=true
    fi
    
    if [ "$NEEDS_INSTALL" = true ]; then
        echo "📦 Instalando dependências Node.js..."
        # Limpar cache do npm para evitar problemas entre Windows/Linux
        npm cache clean --force 2>/dev/null || true
        npm install --no-audit --no-fund --prefer-offline
        
        # Criar arquivo de controle
        touch /var/www/node_modules/.package-lock.json
    else
        echo "✅ Dependências Node.js já atualizadas."
    fi
    
    # ===== VITE/DESENVOLVIMENTO =====
    if [ "$APP_ENV" != "production" ] && [ "$NODE_ENV" != "production" ]; then
        # Matar processos anteriores do Vite
        kill_vite_processes
        
        # Aguardar um pouco para garantir que os processos foram finalizados
        sleep 1
        
        echo "🔥 Iniciando Vite (npm run dev)..."
        
        # Criar diretório de logs se não existir
        mkdir -p /var/www/storage/logs
        
        # Iniciar Vite em background
        nohup npm run dev -- --host 0.0.0.0 > /var/www/storage/logs/vite.log 2>&1 &
        VITE_PID=$!
        
        # Aguardar alguns segundos para verificar se o Vite iniciou corretamente
        sleep 3
        
        if is_process_running "vite"; then
            echo "✅ Vite iniciado com sucesso! PID: $VITE_PID"
            echo "📁 Logs disponíveis em: storage/logs/vite.log"
            echo "🌐 Vite provavelmente rodando em: http://localhost:5173"
        else
            echo "❌ Erro ao iniciar Vite. Verifique os logs em storage/logs/vite.log"
        fi
    else
        echo "🏭 Ambiente de produção detectado. Pulando npm run dev."
        
        # Em produção, fazer build dos assets
        if [ -f "/var/www/package.json" ]; then
            echo "🔨 Fazendo build dos assets para produção..."
            npm run build
        fi
    fi
else
    echo "⚠️  package.json não encontrado. Pulando configuração Node.js."
fi

# ===== CONFIGURAÇÃO LARAVEL =====
# Aguardar que o arquivo .env esteja disponível
if [ -f "/var/www/.env" ]; then
    echo "⚙️  Executando comandos Laravel..."
    
    # Gerar chave da aplicação se não existir
    if ! grep -q "APP_KEY=" /var/www/.env || grep -q "APP_KEY=$" /var/www/.env; then
        echo "🔑 Gerando chave da aplicação..."
        php artisan key:generate --no-interaction
    fi
    
    # Cache de configuração (apenas em produção)
    if [ "$APP_ENV" = "production" ]; then
        echo "🚀 Otimizando para produção..."
        php artisan config:cache --no-interaction
        php artisan route:cache --no-interaction  
        php artisan view:cache --no-interaction
    else
        # Limpar caches em desenvolvimento
        echo "🧹 Limpando caches de desenvolvimento..."
        php artisan config:clear --no-interaction
        php artisan route:clear --no-interaction
        php artisan view:clear --no-interaction
    fi
    
    # Criar storage links se não existirem
    if [ ! -L "/var/www/public/storage" ]; then
        echo "🔗 Criando link simbólico do storage..."
        php artisan storage:link --no-interaction
    fi
else
    echo "⚠️  Arquivo .env não encontrado. Algumas operações Laravel foram puladas."
fi

# ===== PERMISSÕES =====
echo "🔒 Ajustando permissões..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

echo "🎉 Container PHP configurado com sucesso!"
echo "=== Fim da configuração ==="

# Função para cleanup quando o container for parado
cleanup() {
    echo "🛑 Parando serviços..."
    kill_vite_processes
    exit 0
}

# Capturar sinais para fazer cleanup
trap cleanup SIGTERM SIGINT

exec "$@"