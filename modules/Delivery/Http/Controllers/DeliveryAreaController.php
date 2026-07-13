<?php

namespace Modules\Delivery\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Delivery\Models\DeliveryLocation;
use Modules\Delivery\Models\DeliveryArea;

class DeliveryAreaController extends Controller
{
    public function create()
    {
        $locations = DeliveryLocation::orderBy('location_name')->select('id', 'name')->get();

        return view('pages.deliveries.areas.create', compact('locations'));
    }
}