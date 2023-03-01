<?php

namespace App\Providers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(function () {
            $rule = Password::min(8);

            return $this->app->isProduction()
                ? $rule->mixedCase()->numbers()->uncompromised()
                : $rule;
        });

        UploadedFile::macro('optimizedPath', fn (string $path = null) => ltrim(
            implode(DIRECTORY_SEPARATOR, [
                $path,
                Str::substr($this->hashName(), 0, 2),
                Str::substr($this->hashName(), 2, 2),
            ]), DIRECTORY_SEPARATOR)
        );
    }
}
