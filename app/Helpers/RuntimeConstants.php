<?php

namespace App\Helpers;

use App\Http\Resources\CountryResource;
use App\Http\Resources\CurrencyResource;
use App\Services\CountryService;
use App\Services\CurrencyService;
use Illuminate\Support\Arr;

class RuntimeConstants
{
    const RUNTIME_CONST_LIST = 'runtimeConstList';
    const CSRF_TOKEN = 'csrfToken';
    // const SENTRY_DSN = 'sentryDsn';
    // const SENTRY_TRACE_RATE = 'sentryTraceRate';
    // const SENTRY_ENABLED = 'sentryEnabled';
    // const PUSHER_APP_KEY = 'pusherAppKey';
    // const PUSHER_CLUSTER = 'pusherCluster';
    // const PUSHER_ENCRYPTED = 'pusherEncrypted';
    const APP_ENVIRONMENT = 'appEnvironment';
    const APP_VERSION = 'appVersion';
    const COUNTRIES = 'countries';
    const COUNTRY_CURRENCY_MAP = 'countryCurrencyMap';
    const CURRENCIES = 'currencies';
    const SELECTED_CURRENCY = 'selectedCurrency';
    const LANGUAGES = 'languages';
    const SORT_OPTIONS = 'sortOptions';
    const DAYS_OF_WEEK = 'DAYS_OF_WEEK';
    // const ANNOUNCEMENTS = 'announcements';
    // const SCOUT_DRIVER = 'scoutDriver';
    // const FEATURES = 'features';
    // const RESOURCE_ROOT = 'resourceRoot';
    // const DATA_FORMATS = 'dataFormats';
    // const DATE_FORMATS = 'dateFormats';
    // const DEFAULT_FIELD_ORDER = 'defaultFieldOrder';
    // const FEATURE_FLAGS = 'featureFlags';
    // const REGION = 'region';
    // const AVAILABLE_REGIONS = 'availableRegions';

    public static function get(array $filter = []): array
    {
        $constants = \Cache::driver('array')->rememberForever(
            CacheKeys::RUNTIME_CONSTANTS,
            function () {
                $constants = [
                    self::CSRF_TOKEN => csrf_token(),
                    // self::SENTRY_DSN => config('sentry.dsn'),
                    // self::SENTRY_TRACE_RATE => config('sentry.traces_sample_rate'),
                    // self::SENTRY_ENABLED => config('logging.sentry.enabled') ? 'true' : 'false',
                    // self::PUSHER_APP_KEY => config('broadcasting.connections.pusher.key'),
                    // self::PUSHER_CLUSTER => config('broadcasting.connections.pusher.options.cluster'),
                    // self::PUSHER_ENCRYPTED => config('broadcasting.connections.pusher.options.encrypted') ? 'true' : 'false',
                    self::APP_ENVIRONMENT => config('app.env'),
                    self::APP_VERSION => config('app.version'),
                    self::COUNTRIES => self::getAvailableCountries(),
                    self::COUNTRY_CURRENCY_MAP => self::getCountryCurrencyMap(),
                    self::CURRENCIES => self::getAvailableCurrencies(),
                    self::SELECTED_CURRENCY => self::getSelectedCurrency(),
                    self::SORT_OPTIONS => self::getUIConstants('sort_options'),
                    self::DAYS_OF_WEEK => self::getUIConstants('daysOfWeek'),
                    // Name of route should match name of index route in shared routing group
                    // self::ANNOUNCEMENTS => self::getAnnouncements($appIsShared),
                    // self::SCOUT_DRIVER => config('scout.driver'),
                    // self::FEATURES => config('features'),
                    // self::RESOURCE_ROOT => cdn_asset('app') . '/',
                    // self::DATA_FORMATS => config('constants.default_data_formats'),
                    // self::DATE_FORMATS => config('constants.date_formats'),
                    // self::DEFAULT_FIELD_ORDER => config('constants.default_field_order'),
                    // self::FEATURE_FLAGS => FlagService::getEnabledFlags(),
                    // self::REGION => config('app.region'),
                    // self::AVAILABLE_REGIONS => config('app.regions'),
                ];

                $constants[self::RUNTIME_CONST_LIST] = array_keys($constants);
                return $constants;
            }
        );

        return empty($filter) ? $constants : Arr::only($constants, $filter);
    }

    protected static function getAvailableCountries()
    {
        return CountryResource::collection(CountryService::getAvailableCountries());
    }

    protected static function getAvailableCurrencies()
    {
        return CurrencyResource::collection(CurrencyService::getAvailableCurrencies());
    }

    protected static function getSelectedCurrency()
    {
        return new CurrencyResource(CurrencyService::getUserCurrency());
    }

    protected static function getCountryCurrencyMap()
    {
        $availableCountries = CountryService::getAvailableCountries();
        $countryCurrencyMap = config('constants.country_currency_map');

        // Filter to only include countries with available currencies
        $filteredMap = [];
        foreach ($countryCurrencyMap as $countryCode => $currencyCode) {
            if ($availableCountries->contains('iso_code_2', $countryCode)) {
                $filteredMap[$countryCode] = $currencyCode;
            }
        }
        return $filteredMap;
    }

    protected static function getUIConstants($constant)
    {
        return collect(config('constants.ui_constants.' . $constant))
            ->map(fn($opt) => [
                'id' => $opt['id'],
                'name' => __($opt['name'])
            ]);
    }
}
