<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use App\Models\PersonalAccessToken;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Sanctum buscará tokens en api_usuarios_dsw (mysql_usuarios)
        // en lugar de en la DB local de api_muebles
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
