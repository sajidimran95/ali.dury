<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\User::where('id', 1)->first() ?: App\User::first();
auth()->login($user);

$request = Illuminate\Http\Request::create('/products', 'GET', [], [], [], ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
$session = app('session.store');
$session->put('user.business_id', $user->business_id);
$session->put('business.enable_racks', false);
$session->put('business.enable_row', false);
$session->put('business.enable_position', false);

$business = App\Business::with('currency')->find($user->business_id);
$currency = $business->currency;
$session->put('business', $business);
$session->put('currency', [
    'id' => $currency->id,
    'code' => $currency->code,
    'symbol' => $currency->symbol,
    'thousand_separator' => $currency->thousand_separator,
    'decimal_separator' => $currency->decimal_separator,
]);
$request->setLaravelSession($session);
app()->instance('request', $request);

$ctrl = app(App\Http\Controllers\ProductController::class);
$resp = $ctrl->view(1);
if ($resp === null) {
    echo "view(1) returned NULL (exception was caught, check logs)".PHP_EOL;
} else {
    $html = $resp->render();
    echo 'view(1) rendered OK, length='.strlen($html).PHP_EOL;
    echo substr($html, 0, 200).PHP_EOL;
}
