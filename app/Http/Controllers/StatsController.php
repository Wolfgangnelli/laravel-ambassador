<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Order;
use Illuminate\Http\Request;

class StatsController extends Controller
{

    /**
     * How find information for each link
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $links = Link::where('user_id', $user->id)->get();

        return $links->map(function (Link $link) {

            $orders = Order::where('code', $link->code)->where('complete', 1)->get();

            return [
                'code' => $link->code,
                'count' => $orders->count(),
                'revenue' => $orders->sum(fn (Order $order) => $order->ambassador_revenue)
            ];
        });
    }
}
