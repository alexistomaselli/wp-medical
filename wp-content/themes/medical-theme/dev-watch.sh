#!/bin/bash
# Script de desarrollo completo con Browser Sync + Vite

echo "🔥 Iniciando desarrollo con Hot Reload REAL..."
echo ""
echo "📌 WordPress: http://localhost:8080"
echo "🌐 Browser Sync: http://localhost:3001 ← USA ESTE"
echo ""
echo "✨ El navegador se recargará automáticamente al guardar archivos PHP o SCSS"
echo "⚠️  IMPORTANTE: Abrí http://localhost:3001 en tu navegador (no 8080)"
echo ""
echo "Presiona Ctrl+C para detener"
echo ""

# Esperar a que Browser Sync esté listo
sleep 2

# Ejecutar Vite en background
npm run dev &
VITE_PID=$!

# Ejecutar Browser Sync con proxy a WordPress
npx browser-sync start \
  --proxy "localhost:8080" \
  --port 3001 \
  --files "**/*.php, **/*.scss, style.css" \
  --no-open \
  --no-ui \
  --no-ghost-mode &
BROWSERSYNC_PID=$!

# Watcher que copia el CSS cuando cambia para que Browser Sync lo detecte
while true; do
    if [ -f dist/assets/main.css ]; then
        cp dist/assets/main.css style.css 2>/dev/null
    fi
    sleep 1
done &
WATCHER_PID=$!

# Cleanup al salir
trap "kill $VITE_PID $BROWSERSYNC_PID $WATCHER_PID 2>/dev/null" EXIT

# Esperar indefinidamente
wait
