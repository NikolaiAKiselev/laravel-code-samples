<?php

namespace App\Observers;

use App\Location;
use App\ProductVariant;

class LocationObserver
{
    /**
     * Listen to the Price created event.
     *
     * @param Location $location
     */
    public function created(Location $location)
    {
        ProductVariant::get(['id'])->each(function ($variant) use ($location) {
            $variant->locations()->attach($location->id, ['quantity' => 0]);
        });
    }
}
