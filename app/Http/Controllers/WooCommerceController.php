<?php

namespace App\Http\Controllers;

use App\Models\WooCommerceApiSetting;
use App\Models\WooCommerceOrder;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
// Removed DataTables facade - using manual response instead

class WooCommerceController extends Controller
{
    public function index()
    {
        $role = Role::find(Auth::user()->role_id);
        $permission = Permission::where('name', 'woocommerce-index')->first();
        if($permission && !$role->hasPermissionTo('woocommerce-index')){
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        return view('backend.woocommerce.orders.index');
    }

    public function apiSettings()
    {
        $role = Role::find(Auth::user()->role_id);
        $permission = Permission::where('name', 'woocommerce-api-settings')->first();
        if($permission && !$role->hasPermissionTo('woocommerce-api-settings')){
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }
        
        $settings = WooCommerceApiSetting::all();
        return view('backend.woocommerce.api-settings', compact('settings'));
    }

    public function storeApiSettings(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        $permission = Permission::where('name', 'woocommerce-api-settings')->first();
        if($permission && !$role->hasPermissionTo('woocommerce-api-settings')){
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $validated = $request->validate([
            'platform_name' => 'required|string|max:255',
            'website_url' => 'required|url',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
            'sync_interval' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $setting = WooCommerceApiSetting::create($validated);
        
        return redirect()->route('woocommerce.api-settings')
            ->with('message', 'API settings saved successfully!');
    }

    public function updateApiSettings(Request $request, $id)
    {
        $role = Role::find(Auth::user()->role_id);
        $permission = Permission::where('name', 'woocommerce-api-settings')->first();
        if($permission && !$role->hasPermissionTo('woocommerce-api-settings')){
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $validated = $request->validate([
            'platform_name' => 'required|string|max:255',
            'website_url' => 'required|url',
            'consumer_key' => 'required|string',
            'consumer_secret' => 'required|string',
            'is_active' => 'boolean',
            'sync_interval' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $setting = WooCommerceApiSetting::findOrFail($id);
        $setting->update($validated);
        
        return redirect()->route('woocommerce.api-settings')
            ->with('message', 'API settings updated successfully!');
    }

    public function testConnection($id)
    {
        $setting = WooCommerceApiSetting::findOrFail($id);
        
        try {
            $url = rtrim($setting->website_url, '/') . '/wp-json/wc/v3/orders';
            $response = Http::timeout(10)->get($url, [
                'consumer_key' => $setting->consumer_key,
                'consumer_secret' => $setting->consumer_secret,
                'per_page' => 1,
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection successful!',
                    'data' => $response->json()
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection failed: ' . $response->status() . ' - ' . $response->body()
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function syncOrders(Request $request, $id)
    {
        $setting = WooCommerceApiSetting::findOrFail($id);
        
        try {
            $url = rtrim($setting->website_url, '/') . '/wp-json/wc/v3/orders';
            $page = 1;
            $perPage = 100;
            $totalSynced = 0;
            $totalSkipped = 0;

            // Date filter - if provided, fetch orders from that date onwards
            $afterDate = $request->input('after_date');
            $beforeDate = $request->input('before_date');

            do {
                $params = [
                    'consumer_key' => $setting->consumer_key,
                    'consumer_secret' => $setting->consumer_secret,
                    'per_page' => $perPage,
                    'page' => $page,
                    'orderby' => 'date',
                    'order' => 'desc',
                ];

                // Add date filters if provided
                if ($afterDate) {
                    $params['after'] = date('Y-m-d\TH:i:s', strtotime($afterDate));
                }
                if ($beforeDate) {
                    $params['before'] = date('Y-m-d\TH:i:s', strtotime($beforeDate . ' 23:59:59'));
                }

                $response = Http::timeout(30)->get($url, $params);

                if (!$response->successful()) {
                    break;
                }

                $orders = $response->json();
                if (empty($orders)) {
                    break;
                }

                foreach ($orders as $orderData) {
                    // Check if order already exists (prevent duplicates)
                    $existingOrder = WooCommerceOrder::where('platform_order_id', $orderData['id'])
                        ->where('api_setting_id', $setting->id)
                        ->first();

                    if ($existingOrder) {
                        $totalSkipped++;
                        continue; // Skip duplicate orders
                    }

                    // Only sync new orders
                    $this->syncSingleOrder($orderData, $setting->id);
                    $totalSynced++;
                }

                $page++;
            } while (count($orders) === $perPage);

            $setting->update([
                'last_sync_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$totalSynced} new orders!" . ($totalSkipped > 0 ? " ({$totalSkipped} duplicates skipped)" : ''),
                'total_synced' => $totalSynced,
                'total_skipped' => $totalSkipped
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync error: ' . $e->getMessage()
            ], 500);
        }
    }

    private function syncSingleOrder($orderData, $apiSettingId)
    {
        // Prepare order data - complete buyer and order details
        $orderNumber = $orderData['number'] ?? $orderData['id'];
        
        // Calculate subtotal properly
        $subtotal = floatval($orderData['total'] ?? 0) 
            - floatval($orderData['shipping_total'] ?? 0) 
            - floatval($orderData['total_tax'] ?? 0) 
            + floatval($orderData['discount_total'] ?? 0);
        
        // Get billing company if available
        $billingCompany = $orderData['billing']['company'] ?? null;
        $shippingCompany = $orderData['shipping']['company'] ?? null;
        
        $order = WooCommerceOrder::create([
                'api_setting_id' => $apiSettingId,
                'platform_order_id' => $orderData['id'],
                'order_number' => $orderNumber,
                'status' => $orderData['status'] ?? 'pending',
                'currency' => $orderData['currency'] ?? 'PKR',
                'total' => floatval($orderData['total'] ?? 0),
                'subtotal' => $subtotal,
                'discount_total' => floatval($orderData['discount_total'] ?? 0),
                'shipping_total' => floatval($orderData['shipping_total'] ?? 0),
                'tax_total' => floatval($orderData['total_tax'] ?? 0),
                'customer_id' => null, // Not linking to CPG customers
                // Complete buyer details
                'customer_email' => $orderData['billing']['email'] ?? null,
                'customer_phone' => $orderData['billing']['phone'] ?? null,
                'customer_first_name' => $orderData['billing']['first_name'] ?? null,
                'customer_last_name' => $orderData['billing']['last_name'] ?? null,
                // Complete billing address
                'billing_address' => $this->formatAddress($orderData['billing'] ?? []),
                'billing_city' => $orderData['billing']['city'] ?? null,
                'billing_state' => $orderData['billing']['state'] ?? null,
                'billing_postcode' => $orderData['billing']['postcode'] ?? null,
                'billing_country' => $orderData['billing']['country'] ?? null,
                // Complete shipping address
                'shipping_address' => $this->formatAddress($orderData['shipping'] ?? []),
                'shipping_city' => $orderData['shipping']['city'] ?? null,
                'shipping_state' => $orderData['shipping']['state'] ?? null,
                'shipping_postcode' => $orderData['shipping']['postcode'] ?? null,
                'shipping_country' => $orderData['shipping']['country'] ?? null,
                // Payment details
                'payment_method' => $orderData['payment_method'] ?? null,
                'payment_method_title' => $orderData['payment_method_title'] ?? null,
                'transaction_id' => $orderData['transaction_id'] ?? null,
                // Complete date information
                'order_date' => !empty($orderData['date_created']) ? date('Y-m-d H:i:s', strtotime($orderData['date_created'])) : now(),
                'date_created' => !empty($orderData['date_created']) ? date('Y-m-d H:i:s', strtotime($orderData['date_created'])) : null,
                'date_modified' => !empty($orderData['date_modified']) ? date('Y-m-d H:i:s', strtotime($orderData['date_modified'])) : null,
                'date_completed' => !empty($orderData['date_completed']) ? date('Y-m-d H:i:s', strtotime($orderData['date_completed'])) : null,
                'date_paid' => !empty($orderData['date_paid']) ? date('Y-m-d H:i:s', strtotime($orderData['date_paid'])) : null,
                // Notes
                'customer_note' => $orderData['customer_note'] ?? null,
                'order_notes' => !empty($orderData['meta_data']) ? json_encode($orderData['meta_data']) : null,
                // Complete order items and metadata
                'line_items' => $orderData['line_items'] ?? [],
                'meta_data' => $orderData['meta_data'] ?? [],
                'raw_data' => $orderData, // Store complete API response
                'product_images' => $this->extractProductImages($orderData['line_items'] ?? []),
                'is_synced' => true,
                'synced_at' => now(),
            ]);

        return $order;
    }

    private function extractProductImages($lineItems)
    {
        $images = [];
        foreach ($lineItems as $item) {
            // WooCommerce line items can have image in different formats
            if (!empty($item['image'])) {
                $imgData = $item['image'];
                $imgUrl = '';
                
                // Handle different image formats
                if (is_string($imgData)) {
                    $imgUrl = $imgData;
                } elseif (is_array($imgData)) {
                    $imgUrl = $imgData['src'] ?? $imgData['url'] ?? '';
                }
                
                if ($imgUrl) {
                    $images[] = [
                        'url' => $imgUrl,
                        'alt' => (is_array($imgData) ? ($imgData['alt'] ?? '') : '') ?: ($item['name'] ?? 'Product'),
                        'name' => $item['name'] ?? '',
                    ];
                }
            }
        }
        return $images;
    }

    private function formatAddress($addressData)
    {
        $parts = array_filter([
            $addressData['address_1'] ?? '',
            $addressData['address_2'] ?? '',
            $addressData['city'] ?? '',
            $addressData['state'] ?? '',
            $addressData['postcode'] ?? '',
            $addressData['country'] ?? '',
        ]);
        return implode(', ', $parts);
    }

    public function ordersDatatable(Request $request)
    {
        $orders = WooCommerceOrder::with(['apiSetting', 'customer'])
            ->orderBy('order_date', 'desc');

        // Manual DataTable response (since DataTables package may not be installed)
        $totalData = $orders->count();
        $totalFiltered = $totalData;

        // Search
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $orders->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_first_name', 'like', "%{$search}%")
                  ->orWhere('customer_last_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
            $totalFiltered = $orders->count();
        }

        // Pagination
        $start = $request->start ?? 0;
        $length = $request->length ?? 25;
        $orders = $orders->skip($start)->take($length)->get();

        $data = [];
        foreach ($orders as $row) {
            // Get product images
            $images = $row->product_images ?? [];
            $imageHtml = '';
            if (!empty($images)) {
                $imageHtml = '<div class="d-flex gap-1">';
                foreach (array_slice($images, 0, 3) as $img) {
                    $imgUrl = is_array($img) ? ($img['url'] ?? $img['src'] ?? '') : $img;
                    if ($imgUrl) {
                        $imageHtml .= '<img src="' . htmlspecialchars($imgUrl) . '" alt="Product" style="width:40px;height:40px;object-fit:cover;border-radius:4px;cursor:pointer;" onclick="showImageModal(\'' . htmlspecialchars($imgUrl) . '\')" title="Click to view">';
                    }
                }
                if (count($images) > 3) {
                    $imageHtml .= '<span class="badge badge-info" style="line-height:40px;">+' . (count($images) - 3) . '</span>';
                }
                $imageHtml .= '</div>';
            } else {
                $imageHtml = '<span class="text-muted">—</span>';
            }

            // Status badge
            $badges = [
                'pending' => '<span class="badge badge-warning">Pending</span>',
                'processing' => '<span class="badge badge-info">Processing</span>',
                'completed' => '<span class="badge badge-success">Completed</span>',
                'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
                'refunded' => '<span class="badge badge-secondary">Refunded</span>',
            ];
            $statusBadge = $badges[$row->status] ?? '<span class="badge badge-secondary">' . ucfirst($row->status) . '</span>';

            // Customer name
            $customerName = '—';
            if ($row->customer) {
                $customerName = $row->customer->name;
            } elseif ($row->customer_first_name || $row->customer_last_name) {
                $customerName = trim(($row->customer_first_name ?? '') . ' ' . ($row->customer_last_name ?? ''));
            }

            // Actions
            $editBtn = '';
            $deleteBtn = '';
            $role = Role::find(Auth::user()->role_id);
            $editPerm = Permission::where('name', 'woocommerce-edit')->first();
            $deletePerm = Permission::where('name', 'woocommerce-delete')->first();
            
            if((!$editPerm || $role->hasPermissionTo('woocommerce-edit'))) {
                $editBtn = '<a href="' . route('woocommerce.orders.edit', $row->id) . '" class="btn btn-info btn-sm"><i class="fa fa-edit"></i></a>';
            }
            if((!$deletePerm || $role->hasPermissionTo('woocommerce-delete'))) {
                $deleteBtn = '<button class="btn btn-danger btn-sm" onclick="deleteOrder(' . $row->id . ')"><i class="fa fa-trash"></i></button>';
            }
            $actionHtml = '<div class="btn-group">' . $editBtn . ' ' . $deleteBtn . '</div>';

            $data[] = [
                'order_number' => $row->order_number,
                'platform_name' => $row->apiSetting->platform_name ?? '—',
                'customer_name' => $customerName,
                'images' => $imageHtml,
                'status_badge' => $statusBadge,
                'total' => number_format($row->total, 2),
                'currency' => $row->currency,
                'order_date' => $row->order_date ? $row->order_date->format('Y-m-d H:i') : '—',
                'action' => $actionHtml,
            ];
        }

        return response()->json([
            'draw' => intval($request->draw ?? 1),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        $permission = Permission::where('name', 'woocommerce-add')->first();
        if($permission && !$role->hasPermissionTo('woocommerce-add')){
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $currencies = \App\Models\Currency::where('is_active', true)->get();
        
        return view('backend.woocommerce.orders.create', compact('currencies'));
    }

    public function store(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        $permission = Permission::where('name', 'woocommerce-add')->first();
        if($permission && !$role->hasPermissionTo('woocommerce-add')){
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $validated = $request->validate([
            'order_number' => 'required|string|unique:woocommerce_orders,order_number',
            'status' => 'required|string|in:pending,processing,completed,cancelled,refunded',
            'currency' => 'required|string|max:10',
            'total' => 'required|numeric|min:0',
            'customer_id' => 'nullable',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
            'customer_first_name' => 'nullable|string',
            'customer_last_name' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'billing_city' => 'nullable|string',
            'billing_state' => 'nullable|string',
            'billing_postcode' => 'nullable|string',
            'billing_country' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'shipping_city' => 'nullable|string',
            'shipping_state' => 'nullable|string',
            'shipping_postcode' => 'nullable|string',
            'shipping_country' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'payment_method_title' => 'nullable|string',
            'order_date' => 'nullable|date',
        ]);

        $validated['platform_order_id'] = 'manual_' . time();
        $validated['subtotal'] = $validated['total'];
        $validated['order_date'] = $validated['order_date'] ?? now();

        WooCommerceOrder::create($validated);

        return redirect()->route('woocommerce.orders.index')
            ->with('message', 'Order created successfully!');
    }

    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        $permission = Permission::where('name', 'woocommerce-edit')->first();
        if($permission && !$role->hasPermissionTo('woocommerce-edit')){
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $order = WooCommerceOrder::findOrFail($id);
        $currencies = \App\Models\Currency::where('is_active', true)->get();
        
        return view('backend.woocommerce.orders.edit', compact('order', 'currencies'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::find(Auth::user()->role_id);
        $permission = Permission::where('name', 'woocommerce-edit')->first();
        if($permission && !$role->hasPermissionTo('woocommerce-edit')){
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $order = WooCommerceOrder::findOrFail($id);

        $validated = $request->validate([
            'order_number' => 'required|string|unique:woocommerce_orders,order_number,' . $id,
            'status' => 'required|string|in:pending,processing,completed,cancelled,refunded',
            'currency' => 'required|string|max:10',
            'total' => 'required|numeric|min:0',
            'customer_id' => 'nullable',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
            'customer_first_name' => 'nullable|string',
            'customer_last_name' => 'nullable|string',
            'billing_address' => 'nullable|string',
            'billing_city' => 'nullable|string',
            'billing_state' => 'nullable|string',
            'billing_postcode' => 'nullable|string',
            'billing_country' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'shipping_city' => 'nullable|string',
            'shipping_state' => 'nullable|string',
            'shipping_postcode' => 'nullable|string',
            'shipping_country' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'payment_method_title' => 'nullable|string',
            'order_date' => 'nullable|date',
        ]);

        $validated['subtotal'] = $validated['total'];

        $order->update($validated);

        return redirect()->route('woocommerce.orders.index')
            ->with('message', 'Order updated successfully!');
    }

    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        $permission = Permission::where('name', 'woocommerce-delete')->first();
        if($permission && !$role->hasPermissionTo('woocommerce-delete')){
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $order = WooCommerceOrder::findOrFail($id);
        $order->delete();

        return response()->json(['success' => true, 'message' => 'Order deleted successfully!']);
    }

    public function show($id)
    {
        $role = Role::find(Auth::user()->role_id);
        $permission = Permission::where('name', 'woocommerce-index')->first();
        if($permission && !$role->hasPermissionTo('woocommerce-index')){
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
        }

        $order = WooCommerceOrder::with(['apiSetting', 'customer'])->findOrFail($id);
        return view('backend.woocommerce.orders.show', compact('order'));
    }
}

