<?php

// This package is installed inside a host Laravel application that always
// ships App\Http\Controllers\Controller (the default Laravel skeleton base
// controller). It doesn't exist in this package's own repo/vendor tree, so
// PHPStan needs this stub to resolve it when analysing controllers here.

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
}
