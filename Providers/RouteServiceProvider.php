<?php

namespace Salla\Gamification\Providers;

use Salla\Core\Base\BaseRouteServiceProvider;

class RouteServiceProvider extends BaseRouteServiceProvider
{
    public $module = 'gamification';

    protected $namespace = 'Salla\Gamification\Http\Controllers';
}
