<?php

namespace Modules\Ecommerce\Services;

use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Ecommerce\Entities\EcomCouponUsage;
use Modules\Ecommerce\Entities\EcommerceSetting;
use Modules\Ecommerce\Entities\EcomOrderMeta;

class CheckoutService
{
    public function __construct(
        protected CartService $cartService,
        protected InventoryGuard $inventory,
        protected CouponService $couponService,
        protected ShippingCalculator $shippingCalculator,
        protected TaxCalculator $taxCalculator,
        protected TransactionUtil $transactionUtil,
        protected ProductUtil $productUtil
    ) {
    }

    public function placeOrder(int $business_id, int $contact_id, array $payload): array
    {
        $settings = EcommerceSetting::forBusiness($business_id);
        if (! $settings->is_enabled) {
            throw new \RuntimeException(__('ecommerce::lang.store_disabled'));
        }
        if (empty($settings->location_id)) {
            throw new \RuntimeException(__('ecommerce::lang.location_required'));
        }

        $cart = $this->cartService->getOrCreateCart($business_id);
        $cart->load(['items.product', 'items.variation']);
        if ($cart->items->isEmpty()) {
            throw new \RuntimeException(__('ecommerce::lang.cart_empty'));
        }

        $location_id = $settings->location_id;
        $state = $payload['shipping']['state'] ?? null;
        $city = $payload['shipping']['city'] ?? null;

        foreach ($cart->items as $item) {
            $this->inventory->assertAvailable($item->product_id, $item->variation_id, $location_id, (float) $item->quantity);
        }

        $subtotal = $cart->items->sum(fn ($i) => $i->quantity * $i->unit_price);
        $discount = 0;
        $coupon = null;
        $coupon_code = $payload['coupon_code'] ?? $cart->coupon_code;
        if (! empty($coupon_code)) {
            $coupon = $this->couponService->findValid($business_id, $coupon_code, $subtotal);
            $discount = $this->couponService->discountAmount($coupon, $subtotal);
        }

        $shipping = $this->shippingCalculator->calculate($business_id, $subtotal - $discount, $state);
        $tax = $this->taxCalculator->calculate($business_id, max(0, $subtotal - $discount), $state, $city);
        $final_total = max(0, $subtotal + $shipping + $tax - $discount);

        $payment_method = $payload['payment_method'] ?? 'cod';
        if ($payment_method === 'cod' && ! $settings->enable_cod) {
            throw new \RuntimeException(__('ecommerce::lang.payment_not_available'));
        }
        if ($payment_method === 'stripe' && ! $settings->enable_stripe) {
            throw new \RuntimeException(__('ecommerce::lang.payment_not_available'));
        }
        if ($payment_method === 'paypal' && ! $settings->enable_paypal) {
            throw new \RuntimeException(__('ecommerce::lang.payment_not_available'));
        }

        $products = [];
        foreach ($cart->items as $item) {
            $products[] = [
                'product_id' => $item->product_id,
                'variation_id' => $item->variation_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'unit_price_inc_tax' => $item->unit_price,
                'item_tax' => 0,
                'tax_id' => null,
                'enable_stock' => $item->product->enable_stock ?? 0,
                'product_type' => $item->product->type ?? 'single',
            ];
        }

        $user_id = \App\User::where('business_id', $business_id)->value('id') ?? 1;

        DB::beginTransaction();
        try {
            $invoice_total = [
                'total_before_tax' => $subtotal - $discount,
                'tax' => $tax,
            ];

            $input = [
                'type' => 'sell',
                'status' => 'final',
                'location_id' => $location_id,
                'contact_id' => $contact_id,
                'transaction_date' => Carbon::now()->toDateTimeString(),
                'final_total' => $final_total,
                'discount_type' => $discount > 0 ? 'fixed' : null,
                'discount_amount' => $discount,
                'sale_note' => $payload['note'] ?? 'Ecommerce order',
                'source' => 'ecommerce',
                'shipping_charges' => $shipping,
                'shipping_details' => $payload['shipping']['address_line_1'] ?? null,
                'shipping_address' => $this->formatAddress($payload['shipping'] ?? []),
                'shipping_status' => 'ordered',
                'delivered_to' => trim(($payload['shipping']['first_name'] ?? '').' '.($payload['shipping']['last_name'] ?? '')),
                'is_direct_sale' => 0,
                'is_suspend' => 0,
                'exchange_rate' => 1,
                'additional_expense_value_1' => 0,
                'additional_expense_value_2' => 0,
                'additional_expense_value_3' => 0,
                'additional_expense_value_4' => 0,
            ];

            $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id, false);
            $this->transactionUtil->createOrUpdateSellLines($transaction, $products, $location_id, false, null, [], false);

            foreach ($cart->items as $item) {
                if (! empty($item->product->enable_stock)) {
                    $this->productUtil->decreaseProductQuantity(
                        $item->product_id,
                        $item->variation_id,
                        $location_id,
                        $item->quantity
                    );
                }
            }

            $payment_status = $payment_method === 'cod' ? 'due' : 'due';
            $transaction->payment_status = $payment_status;
            $transaction->save();

            if ($payment_method !== 'cod') {
                // Stripe/PayPal ready: payment recorded when gateway confirms
                $transaction->additional_notes = trim(($transaction->additional_notes ?? '').' | Payment: '.$payment_method.' (awaiting confirmation)');
                $transaction->save();
            }

            $token = Str::random(40);
            $timeline = [
                ['status' => 'pending', 'label' => 'Order Received', 'at' => now()->toDateTimeString()],
            ];

            EcomOrderMeta::create([
                'business_id' => $business_id,
                'transaction_id' => $transaction->id,
                'tracking_token' => $token,
                'fulfillment_status' => 'pending',
                'timeline' => $timeline,
                'shipping_address' => $payload['shipping'] ?? [],
                'billing_address' => ($payload['billing_same'] ?? true) ? ($payload['shipping'] ?? []) : ($payload['billing'] ?? []),
                'coupon_id' => $coupon?->id,
                'payment_method' => $payment_method,
                'customer_email' => $payload['email'] ?? null,
                'customer_phone' => $payload['phone'] ?? null,
            ]);

            if ($coupon) {
                $coupon->increment('used_count');
                EcomCouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'contact_id' => $contact_id,
                    'transaction_id' => $transaction->id,
                ]);
            }

            $this->cartService->clear($business_id);
            DB::commit();

            return [
                'transaction' => $transaction,
                'tracking_token' => $token,
                'totals' => [
                    'subtotal' => $subtotal,
                    'shipping' => $shipping,
                    'tax' => $tax,
                    'discount' => $discount,
                    'total' => $final_total,
                ],
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function formatAddress(array $address): string
    {
        return implode(', ', array_filter([
            $address['address_line_1'] ?? null,
            $address['address_line_2'] ?? null,
            $address['city'] ?? null,
            trim(($address['state'] ?? '').' '.($address['zip_code'] ?? '')),
            $address['country'] ?? 'United States',
        ]));
    }
}
