@echo off
start "Laravel Server" cmd /k php artisan serve
start "Queue Worker" cmd /k php artisan queue:work --sleep=1 --tries=1
start "Vite" cmd /k npm run dev
