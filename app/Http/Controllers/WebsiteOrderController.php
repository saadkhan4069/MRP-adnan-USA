<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

class WebsiteOrderController extends Controller
{
    public function index(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        $roleName = $role?->name;
        $isCustomer = Auth::user()->role_id == 5 || (is_string($roleName) && strtolower($roleName) === 'customer');

        if ($role && ($role->hasPermissionTo('sales-index') || $isCustomer)) {
            return view('backend.website_orders.index');
        }

        return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function fetch(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        $roleName = $role?->name;
        $isCustomer = Auth::user()->role_id == 5 || (is_string($roleName) && strtolower($roleName) === 'customer');

        if (! $role || (! $role->hasPermissionTo('sales-index') && ! $isCustomer)) {
            return response()->json([
                'success' => false,
                'message' => __('db.Sorry! You are not allowed to access this module'),
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'nullable|string|max:500',
            'dateFrom' => 'nullable|date_format:Y-m-d',
            'dateTo' => 'nullable|date_format:Y-m-d',
        ]);

        $query = [];
        if (! empty($validated['status'])) {
            $query['status'] = $validated['status'];
        }
        if (! empty($validated['dateFrom'])) {
            $query['dateFrom'] = $validated['dateFrom'];
        }
        if (! empty($validated['dateTo'])) {
            $query['dateTo'] = $validated['dateTo'];
        }

        $baseUrl = rtrim(config('website_orders.api_url'), '/');
        $url = $baseUrl.(count($query) ? '?'.http_build_query($query) : '');

        try {
            // Open API: plain GET — koi custom auth / extra headers nahi
            $response = Http::timeout(config('website_orders.timeout', 45))->get($url);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 502);
        }

        if (! $response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'API error HTTP '.$response->status().': '.mb_substr($response->body(), 0, 500),
            ], $response->status() >= 400 && $response->status() < 600 ? $response->status() : 502);
        }

        $payload = $response->json();
        $orders = [];
        if (is_array($payload)) {
            $orders = $payload['orders'] ?? [];
            if ($orders === [] && isset($payload['data']['orders']) && is_array($payload['data']['orders'])) {
                $orders = $payload['data']['orders'];
            }
        }

        if (! is_array($orders)) {
            $orders = [];
        }

        return response()->json([
            'success' => true,
            'orders' => $orders,
        ]);
    }
}
