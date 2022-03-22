<?php

namespace App\Http\Controllers\Subscriptions;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        if (auth()->user()->subscribed('default'))
            return redirect()->route('subscription.premium');

        return view('subscriptions.index', [
            'intent' => auth()->user()->createSetupIntent(),
            'plan' => session('plan')
        ]);
    }

    public function store(Request $request)
    {
        $plan = session('plan');

        $request->user()
                ->newSubscription('default', $plan->stripe_id)
                ->create($request->token);

        return redirect()->route('subscription.premium');
    }

    public function premium()
    {
        return view('subscriptions.premium');
    }

    public function account()
    {
        $user = auth()->user();

        $invoices = $user->invoices();

        $subscription = $user->subscription('default');

        return view('subscriptions.account', compact('invoices', 'user', 'subscription'));
    }

    public function downloadInvoice($invoiceId)
    {
        return Auth::user()
                    ->downloadInvoice($invoiceId, [
                        'vendor' => config('app.name'),
                        'product' => 'Assinatura VIP'
                    ]);
    }

    public function cancel()
    {
        auth()->user()->subscription('default')->cancel();

        return redirect()->route('subscription.account');
    }

    public function resume()
    {
        auth()->user()->subscription('default')->resume();

        return redirect()->route('subscription.account');
    }
}
