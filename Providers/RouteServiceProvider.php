<?php

namespace Modules\Gamification\Providers;

use Salla\Core\Base\BaseRouteServiceProvider;

class RouteServiceProvider extends BaseRouteServiceProvider
{
    public $module = 'gamification';

    protected $namespace = 'Modules\Gamification\Http\Controllers';
}
