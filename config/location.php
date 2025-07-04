<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver
    |--------------------------------------------------------------------------
    |
    | The default driver you would like to use for location retrieval.
    |
    */

    'driver' => Stevebauman\Location\Drivers\MaxMind::class,

    /*
    |--------------------------------------------------------------------------
    | Driver Fallbacks
    |--------------------------------------------------------------------------
    |
    | The drivers you want to use to retrieve the users location
    | if the above selected driver is unavailable.
    |
    | These will be called upon in order (first to last).
    |
    */

    'fallbacks' => [
        Stevebauman\Location\Drivers\IpApi::class,
        Stevebauman\Location\Drivers\IpInfo::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | MaxMind Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration for the MaxMind driver.
    |
    | If web service is enabled, you must fill in your user ID and license key.
    |
    | If web service is disabled, it will try and retrieve the users location
    | from the MaxMind database file located in the local path below.
    |
    */

    'maxmind' => [
        'web' => [
            'enabled' => false,
            'user_id' => '',
            'license_key' => '',
            'host' => 'geoip.maxmind.com',
        ],
        'local' => [
            'path' => storage_path('app/geoip/GeoLite2-City.mmdb'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IP API Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration for the IP API driver.
    |
    */

    'ip_api' => [
        'token' => env('IP_API_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | IPInfo Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration for the IPInfo driver.
    |
    */

    'ipinfo' => [
        'token' => env('IPINFO_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Localhost Testing
    |--------------------------------------------------------------------------
    |
    | If your running your website locally and want to test different
    | IP addresses to see location detection, set 'enabled' to true.
    |
    | The testing IP address is a Google host in the United States.
    |
    */

    'testing' => [
        'enabled' => env('LOCATION_TESTING', false),
        'ip' => '66.102.0.0',
    ],

];
