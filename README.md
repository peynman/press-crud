# Larapress CRUD

[![Coverage Status](https://img.shields.io/codecov/c/github/peynman/larapress-crud.svg?branch=master&style=flat-square)](https://codecov.io/github/peynman/larapress-crud?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/peynman/larapress-crud.svg?style=flat-square)](https://packagist.org/packages/peynman/larapress-crud)
[![Total Downloads](https://img.shields.io/packagist/dt/peynman/larapress-crud.svg?style=flat-square)](https://packagist.org/packages/peynman/larapress-crud)
[![License](https://img.shields.io/packagist/l/peynman/larapress-crud.svg?style=flat-square)](https://packagist.org/packages/peynman/larapress-crud)

## What is it for?
Larapress CRUD is a Create/Read/Update/Delete resource management api, with:
* Easy name.verb based authorization
* Simple yet overridable pipelines
* Reusable code principles in mind
* Role-based access control

## Usage
[See usage](./USAGE.md)

## Development/Contribution Guid
* create a new laravel project
* add this project as a submodule at path packages/larapress-crud
* use phpunit, phpcs
    * ```vendor/bin/phpunit -c packages/larapress-crud/phpunit.xml```
    * ```vendor/bin/phpcs --standard=packages/larapress-crud/phpcs.xml packages/larapress-crud/```
