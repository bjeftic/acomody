<?php

use Illuminate\Support\Facades\File;
use Illuminate\Auth\AuthenticationException;
use App\Models\User;
use App\Services\CurrencyService;
use App\Models\ExchangeRate;

function get_model_for_table(string $table): ?string
{
    static $tableModelMap = null;

    if (is_null($tableModelMap)) {
        $tableModelMap = [];

        $fileLists = File::allFiles(app_path());

        foreach ($fileLists as $file) {
            $namespace = 'App';
            $relativePath = $file->getRelativePath();

            if (!empty($relativePath)) {
                $namespace .= '\\' . str_replace('/', '\\', $relativePath);
            }

            $filename = $file->getBasename('.php');
            $class = "$namespace\\$filename";

            if (class_exists($class) && is_subclass_of($class, \App\Models\Model::class)) {
                $modelInstance = new $class();
                $tableModelMap[$modelInstance->getTable()] = $class;
            }
        }
    }

    return $tableModelMap[$table] ?? null;
}

function user(): ?User
{
    return auth()->guard()->user();
}

function userOrFail(): User
{
    $user = user();
    if (is_null($user)) {
        throw new AuthenticationException();
    }
    return $user;
}

function id(): ?int
{
    return auth()->guard()->user()?->id;
}

function calculatePriceInSettedCurrency(?float $priceInDefaultCurrency, string $documentCurrency, string $settedCurrencyCode): ?float
{
    if (is_null($priceInDefaultCurrency)) {
        return null;
    }

    if (empty($settedCurrencyCode)) {
        $settedCurrencyCode = CurrencyService::getUserCurrency()->code;
    }

    if ($settedCurrencyCode === $documentCurrency) {
        return round($priceInDefaultCurrency, 2);
    }

    $result = ExchangeRate::convert(
        $priceInDefaultCurrency,
        $documentCurrency,
        $settedCurrencyCode
    );

    return $result;
}
