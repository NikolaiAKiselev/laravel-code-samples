<?php

namespace App\Http\Controllers\Api;

use App\PriceTier;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class PriceTierController extends Controller
{
    /**
     * @return \Illuminate\Http\Response
     */
    function index()
    {
        return PriceTier::all();
    }

    /**
     * @param  Requests\StorePriceTier $request
     * @return \Illuminate\Http\Response
     */
    function store(Requests\StorePriceTier $request)
    {
        return PriceTier::create($request->input());
    }

    /**
     * @param  Requests\UpdatePriceTier $request
     * @param  PriceTier $priceTier
     * @return \Illuminate\Http\Response
     */
    function update(Requests\UpdatePriceTier $request, PriceTier $priceTier)
    {
        return tap($priceTier)->update($request->input());
    }

    /**
     * @param Requests\DeletePriceTier $request
     * @param  PriceTier $priceTier
     * @return \Illuminate\Http\Response
     */
    function destroy(Requests\DeletePriceTier $request, PriceTier $priceTier)
    {
        $priceTier->delete();
    }
}
