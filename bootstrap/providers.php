<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\ComplianceServiceProvider::class,
    App\Providers\DiagnosticServiceProvider::class,
    ...(class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)
        ? [App\Providers\TelescopeServiceProvider::class]
        : []),
];
