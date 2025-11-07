<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\SchedulingServiceProvider::class,
    FlipperBox\ClientPortal\Providers\ClientPortalServiceProvider::class,
    FlipperBox\Core\Providers\CoreServiceProvider::class,
    FlipperBox\Crm\Providers\CrmServiceProvider::class,
    FlipperBox\Inventory\Providers\InventoryServiceProvider::class,
    FlipperBox\MechanicPanel\Providers\MechanicPanelServiceProvider::class,
    FlipperBox\WorkManagement\Providers\WorkManagementServiceProvider::class,
];
