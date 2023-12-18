<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends Controller
{
    
    public function createPaymentIntent(Request $request)
    {
        Stripe::setApiKey(config('stripe.sk'));

        try {
            // Create a PaymentIntent
            $intent = PaymentIntent::create([
                'amount' => $request->amount * 100,
                'currency' => 'usd',
            ]);

            // $order = Order::create([

           

            $order = new Order();
            $order->name = $request->name;
            $order->mount = $request->amount;
            $order->save();

            return response()->json(['client_secret' => $intent->client_secret]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

   
}
