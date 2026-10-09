<?php

use App\Http\Middleware\AdminSidebarMenu;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckUserLogin;
use App\Http\Middleware\EcomApi;
use App\Http\Middleware\EnsureCustomerAppAccess;
use App\Http\Middleware\EnsureSaleRepAccess;
use App\Http\Middleware\IsInstalled;
use App\Http\Middleware\Language;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SeparateAppSession;
use App\Http\Middleware\SetSessionData;
use App\Http\Middleware\Superadmin;
use App\Http\Middleware\Timezone;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\TrustProxies;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\AuthenticateSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->use([
            TrustProxies::class,
            HandleCors::class,
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
        ]);

        // Separate Admin / Sale / Customer sessions on the same device
        $middleware->replace(
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
            \App\Http\Middleware\VerifyCsrfToken::class
        );

        $middleware->web(
            prepend: [
                SeparateAppSession::class,
            ],
            remove: [
                \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            ],
            append: [
                \App\Http\Middleware\VerifyCsrfToken::class,
            ],
            replace: [
                \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class => \App\Http\Middleware\VerifyCsrfToken::class,
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class => \App\Http\Middleware\VerifyCsrfToken::class,
            ],
        );

        $middleware->preventRequestForgery(except: [
            '/api/ecom/customers',
            '/api/ecom/orders',
            '/webhook/*',
            'shop',
            'shop/*',
            '*/shop',
            '*/shop/*',
            'cart',
            'cart/*',
            '*/cart',
            '*/cart/*',
            'checkout',
            '*/checkout',
            'contact',
            'subscribe',
            'forgot-password',
            'reset-password',
            'reset-password/*',
            'account/apply',
            'stock-alert',
            'brand-request',
            'wishlist',
            'review',
            'quick-order',
        ], originOnly: false, allowSameSite: true);

        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.basic' => AuthenticateWithBasicAuth::class,
            'auth.session' => AuthenticateSession::class,
            'cache.headers' => SetCacheHeaders::class,
            'can' => Authorize::class,
            'guest' => RedirectIfAuthenticated::class,
            'password.confirm' => RequirePassword::class,
            'signed' => ValidateSignature::class,
            'throttle' => ThrottleRequests::class,
            'verified' => EnsureEmailIsVerified::class,
            'language' => Language::class,
            'timezone' => Timezone::class,
            'SetSessionData' => SetSessionData::class,
            'setData' => IsInstalled::class,
            'authh' => IsInstalled::class,
            'EcomApi' => EcomApi::class,
            'AdminSidebarMenu' => AdminSidebarMenu::class,
            'superadmin' => Superadmin::class,
            'CheckUserLogin' => CheckUserLogin::class,
            'sale.rep' => EnsureSaleRepAccess::class,
            'customer.app' => EnsureCustomerAppAccess::class,
            'sale.guard' => \App\Http\Middleware\UseSaleGuard::class,
            'delivery.app' => \App\Http\Middleware\EnsureDeliveryAppAccess::class,
            'delivery.guard' => \App\Http\Middleware\UseDeliveryGuard::class,
            'mobile.auth' => \App\Http\Middleware\MobileApiAuth::class,
            'mobile.json' => \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Native mobile apps API (/api/v1/*): one JSON error envelope
        // { "success": false, "message": "...", "errors": {...}|null }
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'success' => false,
                    'message' => $e->validator->errors()->first() ?: $e->getMessage(),
                    'errors' => $e->errors(),
                ], $e->status);
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                $status = 401;
                $message = 'Unauthenticated. Please log in again.';
            } elseif ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $status = 404;
                $message = 'Record not found.';
            } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = $e->getPrevious() instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                    ? 'Record not found.'
                    : $e->getMessage();
                $message = $message ?: match ($status) {
                    403 => 'Unauthorized action.',
                    404 => 'Not found.',
                    405 => 'Method not allowed.',
                    429 => 'Too many requests.',
                    default => 'Request failed.',
                };
            } else {
                report($e);
                $status = 500;
                $message = config('app.debug') ? $e->getMessage() : 'Something went wrong. Please try again.';
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => null,
            ], $status);
        });
    })->create();
