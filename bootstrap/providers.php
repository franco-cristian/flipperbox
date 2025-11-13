<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\PolicyServiceProvider::class,
    App\Providers\BroadcastServiceProvider::class,
    FlipperBox\ClientScheduling\Providers\ClientSchedulingServiceProvider::class,
    FlipperBox\ClientPortal\Providers\ClientPortalServiceProvider::class,
    FlipperBox\Core\Providers\CoreServiceProvider::class,
    FlipperBox\Crm\Providers\CrmServiceProvider::class,
    FlipperBox\Inventory\Providers\InventoryServiceProvider::class,
    FlipperBox\MechanicPanel\Providers\MechanicPanelServiceProvider::class,
    FlipperBox\Scheduling\Providers\SchedulingServiceProvider::class,
    FlipperBox\WorkManagement\Providers\WorkManagementServiceProvider::class,
];
