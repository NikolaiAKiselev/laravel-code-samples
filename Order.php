<?php

namespace App;

use App\Helpers\Shopify\IsShopifyEntity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Order
 *
 * @property int $id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $shopify_id
 * @property string|null $cancelled_at
 * @property \Carbon\Carbon|null $shopify_created_at
 * @property string|null $shopify_source_name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ProductVariant[] $lineItems
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order notCancelled()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereShopifyCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereShopifyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereShopifySourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    use IsShopifyEntity;

    public $fillable = [
        'shopify_id',
        'cancelled_at',
        'shopify_created_at',
        'shopify_source_name',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'shopify_id';
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['shopify_created_at', 'canceled_at'];

    /**
     * Put the same products to customer's cart.
     */
    public function reorder()
    {
        foreach ($this->lineItems as $item) {
            auth()->user()->putItemToCart($item->id, $item->line_item->quantity);
        }
    }

    /**
     * Cancel order.
     * Return products.
     */
    public function cancel()
    {
        $this->update(['cancelled_at' => now()]);

        foreach ($this->lineItems as $item) {
            $item->adjustStockOnHand($item->line_item->quantity);
        }
    }

    /**
     * Cancelled orders.
     *
     * @param $query
     * @return Builder
     */
    public function scopeNotCancelled($query)
    {
        return $query->whereNull('cancelled_at');
    }

    /**
     * Add a line item to the order.
     *
     * Adjust SOH an register sale movement.
     *
     * @param ProductVariant $variant
     * @param float $quantity
     * @param float $price
     */
    public function addLineItem($variant, $quantity, $price)
    {
        $this->lineItems()->attach($variant, compact('quantity', 'price'));

        if ($this->shopify_source_name === "pos") {
            $location = Location::defaultStore();
        } else {
            $location = Location::defaultLocation();
        }

        $variant->adjustStockOnHand(-$quantity, $location);

        $variant->addMovement(
            'sale',
            $quantity,
            $location->id,
            $this->id,
            $this->shopify_created_at
        );

        return $this;
    }

    /**
     * Cart items.
     *
     * @return BelongsToMany
     */
    public function lineItems()
    {
        return $this->belongsToMany(ProductVariant::class)
            ->as('line_item')
            ->withPivot('quantity', 'shopify_id', 'price');
    }
}
