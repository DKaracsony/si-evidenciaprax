@echo off
start "Laravel Server" cmd /k "cd /d backend && php artisan serve"
start "Queue Worker" cmd /k "cd /d backend && php artisan queue:work --sleep=1 --tries=1"
start "Vite" cmd /k "cd /d frontend && npm run dev"
