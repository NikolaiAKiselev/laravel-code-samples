<?php

namespace App\Http\Resources;

class WholesaleProductResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'body' => $this->body,
            'variants' => $this->whenLoaded('variants', WholesaleProductVariantResource::collection($this->variants)),
            'image' => $this->image,
            'options' => $this->productOptions,
        ];
    }
}
