# geo-php basado en Geolocation PHP class connects to Google MAPS API

[![Latest Stable Version](http://img.shields.io/packagist/v/jeroendesloovere/geolocation-php-api.svg)](https://packagist.org/packages/eldelassombras/geo-php)
[![License](http://img.shields.io/badge/license-MIT-lightgrey.svg)](https://github.com/jeroendesloovere/geolocation-php-api/blob/master/LICENSE)
[![Build Status](http://img.shields.io/travis/jeroendesloovere/geolocation-php-api.svg)](https://travis-ci.org/jeroendesloovere/geolocation-php-api)

> This Geolocation PHP class connects to Google Maps API to find latitude/longitude or address.

## Installing

### Using Composer

When using [Composer](https://getcomposer.org) you can always load in the latest version.

``` json
{
    "require": {
        "eldelassombras/geolocation-php-api": "1.3.*"
    }
}
```
Check [in Packagist](https://packagist.org/packages/eldelassombras/geo-php).

### Usage example

**getCoordinates**

> Get latitude/longitude coordinates from address.

``` php
$street = 'Koningin Maria Hendrikaplein';
$streetNumber = '1';
$city = 'Gent';
$zip = '1';
$country = 'belgium';

$result = Geolocation::getCoordinates(
    $street,
    $streetNumber,
    $city,
    $zip,
    $country
);
```

**getAddress**

> Get address from latitude/longitude coordinates.

``` php
$latitude = 51.0363935;
$longitude = 3.7121008;

$result = Geolocation::getAddress(
    $latitude,
    $longitude
);
```

Check [the Geolocation class source](./src/Geolocation.php) or [view examples](./examples/example.php).

## License

The module is licensed under [MIT](./LICENSE). In short, this license allows you to do everything as long as the copyright statement stays present.
