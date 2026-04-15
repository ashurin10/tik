<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$m6 = \App\Models\Menu::find(6);
if ($m6) { 
    $m6->parent_id = null; 
    $m6->save(); 
    echo "Fixed Menu 6 parent_id.\n"; 
}
