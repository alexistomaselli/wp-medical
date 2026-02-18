#!/bin/bash
# Script para copiar el CSS compilado a la raíz del tema

echo "📦 Compilando SCSS..."
npm run build

echo "📋 Copiando style.css a la raíz del tema..."
cp dist/assets/main.css style.css

echo "✅ Build completado!"
