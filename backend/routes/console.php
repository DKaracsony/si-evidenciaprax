<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('info', function () {
    $this->comment("Projekt vyvíjali: David Karacsony, Attila Mancal, Peter Opal");
})->purpose('information about authors');
