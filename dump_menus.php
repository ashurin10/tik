<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach(App\Models\Menu::all() as $m) {
    echo $m->id . ' | ' . $m->name . ' | Parent: ' . $m->parent_id . ' | URL: ' . $m->url . PHP_EOL;
}
