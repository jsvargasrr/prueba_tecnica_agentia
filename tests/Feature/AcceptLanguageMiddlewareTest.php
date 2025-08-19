<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeAll(function () {
    // Ruta temporal solo para testear locale
    Route::middleware([\App\Http\Middleware\AcceptLanguage::class])
        ->get('/_test/locale', function () {
            return response()->json(['locale' => app()->getLocale()]);
        });
});

it('sets locale from Accept-Language header', function () {
    $res = $this->withHeaders(['Accept-Language' => 'en-US,en;q=0.9,es;q=0.8'])
        ->get('/_test/locale');

    $res->assertOk()->assertJson(['locale' => 'en']);
});

it('falls back to default when header missing', function () {
    config()->set('app.locale', 'es');

    $res = $this->get('/_test/locale');
    $res->assertOk()->assertJson(['locale' => 'es']);
});
