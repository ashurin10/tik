<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Users:\n";
foreach(\App\Models\User::all() as $u) {
    echo $u->id . ' | ' . $u->name . ' | Role: ' . $u->peran . "\n";
}

echo "\nMenus:\n";
foreach(\App\Models\Menu::all() as $m) {
    echo $m->id . ' | ' . $m->name . ' | RoleAccess: ' . $m->roles->pluck('role')->implode(',') . "\n";
}
