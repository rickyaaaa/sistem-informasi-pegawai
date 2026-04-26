<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$u = App\Models\User::first();
auth()->login($u);
request()->merge(['type' => 'pdf']);
try {
    $res = app(App\Http\Controllers\PegawaiController::class)->export(request());
    echo 'Success';
} catch (\Exception $e) {
    echo $e->getMessage();
}
