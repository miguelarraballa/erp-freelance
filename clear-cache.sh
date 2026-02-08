#!/bin/bash
# Script para limpiar todas las cachés de Laravel y Livewire

echo "Limpiando cachés de Laravel y Livewire..."

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Livewire específico
rm -rf bootstrap/cache/livewire-*
rm -rf storage/framework/cache/livewire/*

echo "Cachés limpiadas. Ahora reconstruyendo optimizaciones..."

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

echo "¡Listo! Cachés limpiadas y optimizaciones reconstruidas."
