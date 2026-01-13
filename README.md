<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Softvérové inžinierstvo - Systém evidencie odbornej praxe (PraxSI)

## Popis projektu
Projekt je webový CRM-like systém určený na evidenciu a správu odborných praxí študentov, vrátane firiem, dokumentov a stavov jednotlivých praxí.
Systém umožňuje študentom, firmám a garantom praxe komunikovať prostredníctvom jednotného rozhrania, pričom podporuje registráciu, schvaľovanie praxí a automatické generovanie dokumentov.
Aplikácia je postavená ako oddelený frontend a backend s API rozhraním a využíva databázu a e-mailové notifikácie na zabezpečenie plynulého priebehu celého procesu.

## Tím
- [Dávid Karácsony](https://github.com/DKaracsony)
- [Attila Mancal](https://github.com/AttilaMAncal)
- [Peter Opál](https://github.com/PeterOpal)

## Technológie
| vrstva       | stack                                                         |
|--------------|---------------------------------------------------------------|
| **Backend**  | <ul><li>PHP 8.3</li><li>Laravel 12</li><li>Composer</li></ul> |
| **Frontend** | <ul><li>Vue 3</li><li>Vite</li><li>SCSS</li></ul>             |
| **Databáza** | MySQL                                                         |

## Inštalácia
- pri odovzdaní projektu budú súčasťou aj `.env` súbor a technická dokumentácia
- na spustenie projektu je potrebné mať nainštalované technológie uvedené vyššie
- je potrebné spustiť nasledujúce príkazy
```bash
composer install
npm install
```
- v `.env` súbore je potrebné nastaviť správny SMTP server na odosielanie e-mailov a správne údaje pre databázu
- v lokálnom prostredí je možné projekt spustiť pomocou:
```bash
start.bat
```

