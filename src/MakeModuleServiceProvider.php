<?php

namespace MediaWebId\MakeModule;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use MediaWebId\MakeModule\Commands\MakeModuleCommand;

class MakeModuleServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->hasCommand(MakeModuleCommand::class);
    }
}
