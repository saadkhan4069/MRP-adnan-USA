<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Stripe\Stripe;
use App\Models\Tax;
use App\Models\Sale;
use App\Models\Unit;
use App\Models\User;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Currency;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Wproduction;
use App\Models\PosSetting;
use App\Traits\TenantInfo;
use App\Models\CustomField;
use App\Traits\StaffAccess;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Models\ProductVariant;
use App\Models\ProductPurchase;
use App\Models\PaymentWithCheque;
use App\Models\Product_Warehouse;
use Spatie\Permission\Models\Role;
use App\Models\PaymentWithCreditCard;
use App\Models\Product_Sale;
use App\Models\Customer;
use App\Models\product_purchase_log;
use App\Models\PurchaseShipped;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\ShipmentPackage;
use App\Models\ShipmentAttachment;
use App\Models\Brand;
use App\Models\Courier;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Http\Requests\Purchase\UpdatePurchaseRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AllPurchaseExport;
use PDF; 
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File; // <-- yeh add karein


class PurchaseController extends Controller
{
    use TenantInfo, StaffAccess;

    public function index(Request $request)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-index')) {
            if($request->input('warehouse_id'))
                $warehouse_id = $request->input('warehouse_id');
            else
                $warehouse_id = 0;

            if($request->input('purchase_status'))
                $purchase_status = $request->input('purchase_status');
            else
                $purchase_status = 0;

            if($request->input('payment_status'))
                $payment_status = $request->input('payment_status');
            else
                $payment_status = 0;

            if($request->input('starting_date')) {
                $starting_date = $request->input('starting_date');
                $ending_date = $request->input('ending_date');
            }
            else {
                $starting_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d') )))));
                $ending_date = date("Y-m-d");
            }
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';
            $lims_pos_setting_data = PosSetting::select('stripe_public_key')->latest()->first();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_production_list = Wproduction::where('is_active', true)->get();
            $lims_account_list = Account::where('is_active', true)->get();
            $custom_fields = CustomField::where([
                                ['belongs_to', 'purchase'],
                                ['is_table', true]
                            ])->pluck('name');
            $field_name = [];
            foreach($custom_fields as $fieldName) {
                $field_name[] = str_replace(" ", "_", strtolower($fieldName));
            }
              return view('backend.purchase.index', compact( 'lims_account_list', 'lims_warehouse_list', 'lims_production_list', 'all_permission', 'lims_pos_setting_data', 'warehouse_id', 'starting_date', 'ending_date', 'purchase_status', 'payment_status', 'custom_fields', 'field_name'));
        }
        else
         return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    private function isImeiExist(string $imei, string $product_id): bool
    {
        $product_warehouses = Product_Warehouse::where('product_id', $product_id)->get();
        
        foreach ($product_warehouses as $p) {
            $imeis = explode(',', $p->imei_number);
            if (in_array(trim($imei), array_map('trim', $imeis))) {
                return true;
            }
        }

        return false;
    }
   
   public function poSearch(Request $request)
   {
    $query = $request->get('q');

    $results = Purchase::where('po_no', 'like', '%' . $query . '%')
        ->select('id', 'po_no')
        ->limit(20)
        ->get();

    return response()->json($results);
   }



public function createshipment()
{
    $role = Role::find(Auth::user()->role_id);

    //     if($role->hasPermissionTo('purchases-add')){
    //     return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    // }

    // Customers (role based filter same as before)
    $lims_customer_list = Customer::with('user')->get();
    if (Auth::user()->role_id > 2) {
        $lims_customer_list = Customer::with('user')
            ->where('user_id', Auth::user()->id)
            ->whereHas('user', function ($q) {
                $q->where('role_id', Auth::user()->role_id);
            })
            ->get();
    }

    // Warehouses (only if your view needs it; otherwise you can remove these 6 lines)
    if (Auth::user()->role_id > 2) {
        $lims_warehouse_list = Warehouse::where([['is_active', true], ['id', Auth::user()->warehouse_id]])->get();
    } else {
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
    }

    $lims_tax_list = Tax::where('is_active', true)->get();

    // Products (your existing helpers)
    $lims_product_list_without_variant = $this->productWithoutVariant();
    $lims_product_list_with_variant    = $this->productWithVariant();

    // Currency + general setting
    $currency_list = Currency::where('is_active', true)->get();
    $currency = Currency::where('is_active', 1)->first() ?: $currency_list->first();
    $general_setting = GeneralSetting::latest()->first();

    // 🔥 NEW: Units for dropdown (id, name, operator, value)
    $units = Unit::where('is_active', true)
        ->orderBy('unit_name')
        ->get(['id', 'unit_name as name', 'operator', 'operation_value']);

    // Remove unused stuff: suppliers, custom_fields etc. (since they weren’t used in the view)
    return view('backend.shipment.create', compact(
        'lims_customer_list',
        'lims_warehouse_list',
        'lims_tax_list',
        'lims_product_list_without_variant',
        'lims_product_list_with_variant',
        'currency_list',
        'currency',
        'general_setting',
        'units' // <- pass to view
    ));
}



    public function shipmentstore(Request $request)
    {
        // ---------- 1) Validate ----------
        $data = $request->validate([
            // Meta
            'po_no'        => ['nullable','string','max:191'],
            'reference_no' => ['nullable','string','max:191'],
            'customer_id'  => ['nullable','integer'],
            'status'       => ['required','integer','between:1,5'],

            // Shipper (From)
            'ship_from_company'     => ['nullable','string','max:191'],
            'ship_from_first_name'  => ['required','string','max:191'],
            'ship_from_address_1'   => ['required','string'],
            'ship_from_country'     => ['required','string','max:191'],
            'ship_from_state'       => ['required','string','max:191'],
            'ship_from_city'        => ['required','string','max:191'],
            'ship_from_zipcode'     => ['required','string','max:191'],
            'ship_from_contact'     => ['required','string','max:191'],
            'ship_from_email'       => ['required','email','max:191'],

            // Recipient (To)
            'ship_to_company'     => ['nullable','string','max:191'],
            'ship_to_first_name'  => ['required','string','max:191'],
            'ship_to_address_1'   => ['required','string'],
            'ship_to_country'     => ['required','string','max:191'],
            'ship_to_state'       => ['required','string','max:191'],
            'ship_to_city'        => ['required','string','max:191'],
            'ship_to_zipcode'     => ['required','string','max:191'],
            'ship_to_contact'     => ['required','string','max:191'],
            'ship_to_email'       => ['required','email','max:191'],

            // Currency
            'currency_id'   => ['nullable','integer'],
            'exchange_rate' => ['required','numeric','min:0.000001'],

            // Service / Payment
            'provider'          => ['nullable','string','max:50'],
            'service_code'      => ['nullable','string','max:191'],
            'service_name'      => ['nullable','string','max:191'],
            'saturday_delivery' => ['nullable', Rule::in(['0','1'])],
            'signature_option'  => ['nullable','string','max:50'],
            'payer'             => ['nullable', Rule::in(['shipper','receiver','third_party'])],
            'account_number'    => ['nullable','string','max:191'],
            'incoterms'         => ['nullable','string','max:10'],
            'contents_type'     => ['nullable','string','max:50'],
            'declared_value_total' => ['nullable','numeric','min:0'],

            // Order totals
            'order_tax_rate' => ['required','numeric','min:0'],
            'order_discount' => ['required','numeric','min:0'],
            'shipping_cost'  => ['required','numeric','min:0'],

            'comments' => ['nullable','string'],

            // Items arrays
            'product_id'      => ['array'],
            'product_id.*'    => ['required','integer'],
            'product_code'    => ['array'],
            'product_code.*'  => ['nullable','string','max:191'],
            'product_unit'    => ['array'],
            'product_unit.*'  => ['nullable','string','max:191'],
            'qty'             => ['array'],
            'qty.*'           => ['required','numeric','min:1'],
            'net_unit_cost'   => ['array'],
            'net_unit_cost.*' => ['required','numeric','min:0'],
            'discount'        => ['array'],
            'discount.*'      => ['nullable','numeric','min:0'],
            'subtotal'        => ['array'],
            'subtotal.*'      => ['required','numeric','min:0'],

            // Packages arrays
            'packages'                         => ['array'],
            'packages.*.packaging'             => ['nullable','string','max:191'],
            'packages.*.weight'                => ['nullable','numeric','min:0'],
            'packages.*.weight_unit'           => ['nullable', Rule::in(['kg','lb'])],
            'packages.*.length'                => ['nullable','numeric','min:0'],
            'packages.*.width'                 => ['nullable','numeric','min:0'],
            'packages.*.height'                => ['nullable','numeric','min:0'],
            'packages.*.dim_unit'              => ['nullable', Rule::in(['cm','in'])],
            'packages.*.declared_value'        => ['nullable','numeric','min:0'],
            'packages.*.dimensions_note'       => ['nullable','string','max:191'],

            // Attachments
            'attachments'    => ['sometimes','array','max:10'],
            'attachments.*'  => [
                'file',
                'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,csv,txt',
                'max:10240', // 10MB
            ],
        ]);

        $data['saturday_delivery'] = ($request->input('saturday_delivery') == '1');

        $prodIds   = (array) $request->input('product_id', []);
        $prodCodes = (array) $request->input('product_code', []);
        $prodUnits = (array) $request->input('product_unit', []);
        $qtys      = (array) $request->input('qty', []);
        $unitCosts = (array) $request->input('net_unit_cost', []);
        $discounts = (array) $request->input('discount', []);
        $subtotals = (array) $request->input('subtotal', []);

        // Totals
        $lineQtySum = 0; $lineTotal = 0;
        $itemsCount = count($prodIds);
        for ($i = 0; $i < $itemsCount; $i++) {
            $q   = (float) ($qtys[$i]       ?? 0);
            $sub = (float) ($subtotals[$i]  ?? 0);
            $lineQtySum += $q;
            $lineTotal  += $sub;
        }
        $orderDiscount = (float)$request->order_discount;
        $baseForTax    = max($lineTotal - $orderDiscount, 0);
        $orderTax      = $baseForTax * ((float)$request->order_tax_rate / 100);
        $grandTotal    = $baseForTax + $orderTax + (float)$request->shipping_cost;

        $data['item']        = $itemsCount;
        $data['total_qty']   = $lineQtySum;
        $data['total_cost']  = $lineTotal;
        $data['order_tax']   = $orderTax;
        $data['total_tax']   = $orderTax;
        $data['grand_total'] = $grandTotal;

        $savedFiles = [];

        try {
            DB::transaction(function () use ($data, $request, $itemsCount, $prodIds, $prodCodes, $prodUnits, $qtys, $unitCosts, $discounts, $subtotals, &$savedFiles) {

                $shipment = Shipment::create([
                    // meta
                    'po_no'        => $data['po_no']        ?? null,
                    'reference_no' => $data['reference_no'] ?? null,
                    'customer_id'  => $data['customer_id']  ?? null,
                    'status'       => $data['status'],

                    // from
                    'ship_from_company'    => $data['ship_from_company']    ?? null,
                    'ship_from_first_name' => $data['ship_from_first_name'],
                    'ship_from_address_1'  => $data['ship_from_address_1'],
                    'ship_from_country'    => $data['ship_from_country'],
                    'ship_from_state'      => $data['ship_from_state'],
                    'ship_from_city'       => $data['ship_from_city'],
                    'ship_from_zipcode'    => $data['ship_from_zipcode'],
                    'ship_from_contact'    => $data['ship_from_contact'],
                    'ship_from_email'      => $data['ship_from_email'],

                    // to
                    'ship_to_company'    => $data['ship_to_company']    ?? null,
                    'ship_to_first_name' => $data['ship_to_first_name'],
                    'ship_to_address_1'  => $data['ship_to_address_1'],
                    'ship_to_country'    => $data['ship_to_country'],
                    'ship_to_state'      => $data['ship_to_state'],
                    'ship_to_city'       => $data['ship_to_city'],
                    'ship_to_zipcode'    => $data['ship_to_zipcode'],
                    'ship_to_contact'    => $data['ship_to_contact'],
                    'ship_to_email'      => $data['ship_to_email'],

                    // currency
                    'currency_id'   => $data['currency_id']   ?? null,
                    'exchange_rate' => $data['exchange_rate'],

                    // service/payment
                    'provider'          => $data['provider']          ?? null,
                    'service_code'      => $data['service_code']      ?? null,
                    'service_name'      => $data['service_name']      ?? null,
                    'saturday_delivery' => $data['saturday_delivery'] ?? false,
                    'signature_option'  => $data['signature_option']  ?? null,
                    'payer'             => $data['payer']             ?? null,
                    'account_number'    => $data['account_number']    ?? null,
                    'incoterms'         => $data['incoterms']         ?? null,
                    'contents_type'     => $data['contents_type']     ?? null,
                    'declared_value_total' => $data['declared_value_total'] ?? null,

                    // totals
                    'order_tax_rate' => $data['order_tax_rate'],
                    'order_discount' => $data['order_discount'],
                    'shipping_cost'  => $data['shipping_cost'],
                    'item'           => $data['item'],
                    'total_qty'      => $data['total_qty'],
                    'total_cost'     => $data['total_cost'],
                    'total_tax'      => $data['total_tax'],
                    'order_tax'      => $data['order_tax'],
                    'grand_total'    => $data['grand_total'],

                    // payment mirrors
                    'paid_amount'    => (float) ($request->input('paid_amount', 0)),
                    'payment_status' => (int) ($request->input('payment_status', 1)),

                    'comments'       => $data['comments'] ?? null,
                ]);

                // Items
                for ($i = 0; $i < $itemsCount; $i++) {
                    if (!isset($prodIds[$i])) continue;
                    $punitRaw = $prodUnits[$i] ?? null;
                    if (is_numeric($punitRaw)) {
                        $unit = Unit::find((int)$punitRaw);
                        $punitRaw = $unit ? $unit->name : (string)$punitRaw;
                    }
                    ShipmentItem::create([
                        'shipment_id'    => $shipment->id,
                        'product_id'     => (int) $prodIds[$i],
                        'product_code'   => $prodCodes[$i] ?? null,
                        'product_unit'   => $punitRaw,
                        'qty'            => (float)($qtys[$i] ?? 1),
                        'net_unit_cost'  => (float)($unitCosts[$i] ?? 0),
                        'discount'       => (float)($discounts[$i] ?? 0),
                        'subtotal'       => (float)($subtotals[$i] ?? 0),
                    ]);
                }

                // Packages
                $packages = (array) $request->input('packages', []);
                foreach ($packages as $p) {
                    if (!is_array($p)) continue;
                    ShipmentPackage::create([
                        'shipment_id'    => $shipment->id,
                        'packaging'      => $p['packaging']      ?? null,
                        'weight'         => $p['weight']         ?? null,
                        'length'         => $p['length']         ?? null,
                        'width'          => $p['width']          ?? null,
                        'height'         => $p['height']         ?? null,
                        'weight_unit'    => $p['weight_unit']    ?? 'kg',
                        'dim_unit'       => $p['dim_unit']       ?? 'cm',
                        'declared_value' => $p['declared_value'] ?? null,
                        'dimensions_note'=> $p['dimensions_note'] ?? null,
                    ]);
                }

                // Attachments
                $files = $request->file('attachments', []);
                if (!empty($files)) {
                    $baseDir = public_path('shipment/attachments/' . $shipment->id);
                    if (!File::exists($baseDir)) {
                        File::makeDirectory($baseDir, 0775, true);
                    }
                    foreach ($files as $file) {
                        if (!$file->isValid()) continue;

                        // meta BEFORE move
                        $ext      = strtolower($file->getClientOriginalExtension() ?: $file->extension());
                        $uuid     = (string) Str::uuid();
                        $newName  = now()->format('YmdHis') . '-' . $uuid . '.' . $ext;
                        $size     = $file->getSize();
                        $mime     = $file->getClientMimeType();
                        $origName = $file->getClientOriginalName();

                        // move file
                        $file->move($baseDir, $newName);
                        $relativePath = 'shipment/attachments/' . $shipment->id . '/' . $newName;
                        $savedFiles[] = public_path($relativePath);

                        ShipmentAttachment::create([
                            'shipment_id'   => $shipment->id,
                            'original_name' => $origName,
                            'filename'      => $newName,
                            'path'          => $relativePath,
                            'mime'          => $mime,
                            'size'          => $size,
                        ]);
                    }
                }
            });
               return redirect()->route('shipment/index')
                ->with('message', 'Shipment created successfully.');
        } catch (Throwable $e) {
            foreach ($savedFiles as $absPath) {
                try {
                    if (File::exists($absPath)) File::delete($absPath);
                } catch (Throwable $ignored) {}
            }
            report($e);
            return back()
                ->withInput()
                ->withErrors(['not_permitted' => 'Failed to create shipment: ' . $e->getMessage()]);
        }
    }






        public function shipmentindex(Request $request)
        {
               return view('backend.shipment.index');
        }
     
     public function shipmentDatatable(Request $request)
{   
    // Eager load: items, packages, customer (only id,name)
    $shipments = \App\Models\Shipment::with([
            'items:shipment_id,product_code,qty,product_unit,net_unit_cost,subtotal',
            'packages:shipment_id,packaging,weight,weight_unit,length,width,height,dim_unit,declared_value',
            'customer:id,name'
        ])
        ->latest('id')
        ->get([
            'id','reference_no','po_no','customer_id','status',
            'ship_from_first_name','ship_from_company','ship_from_address_1','ship_from_city','ship_from_state','ship_from_zipcode','ship_from_country',
            'ship_to_first_name','ship_to_company','ship_to_address_1','ship_to_city','ship_to_state','ship_to_zipcode','ship_to_country',
            'grand_total','order_tax','shipping_cost','item','total_qty',
            // 'tracking_number',           // <-- agar yeh bhi column abhi nahi hai to comment rehne do
            'created_at','updated_at'
        ]);

    $data = $shipments->map(function ($s) {
        return [
            'id'           => $s->id,
            'reference_no' => $s->reference_no,
            'po_no'        => $s->po_no,
            'buyer'        => optional($s->customer)->name ?? $s->customer_id, // safe fallback
            'status'       => (int) $s->status,
            'from'         => trim(collect([$s->ship_from_first_name, $s->ship_from_city, $s->ship_from_country])->filter()->join(', ')),
            'to'           => trim(collect([$s->ship_to_first_name, $s->ship_to_city, $s->ship_to_country])->filter()->join(', ')),
            'totals'       => [
                'items'        => $s->item,
                'qty'          => $s->total_qty,
                'grand_total'  => $s->grand_total,
                'tax'          => $s->order_tax,
                'shipping'     => $s->shipping_cost,
            ],
            'tracking'     => $s->tracking_number ?? null, // sirf tab jab column ho; warna hamesha null rahega
            'has_label'    => false,  // abhi columns nahi hain, isliye false
            'has_invoice'  => false,  // abhi columns nahi hain, isliye false
            'created_at'   => optional($s->created_at)->format('Y-m-d H:i'),
            'items'        => $s->items->toArray(),
            'packages'     => $s->packages->toArray(),
        ];
    })->values();

    return response()->json(['data' => $data]);
}

// App/Http/Controllers/ShipmentController.php (inside the class)

public function shipmentShow(\App\Models\Shipment $shipment)
{
    // Relations (attachments included)
    $shipment->load(['items', 'packages', 'customer', 'attachments']);

    // Status badge map
    $statusMap = [
        1 => ['label' => 'Pending',    'cls' => 'status-1'],
        2 => ['label' => 'In Transit', 'cls' => 'status-2'],
        3 => ['label' => 'Delivered',  'cls' => 'status-3'],
        4 => ['label' => 'Returned',   'cls' => 'status-4'],
        5 => ['label' => 'Cancelled',  'cls' => 'status-5'],
    ];
    $status = $statusMap[$shipment->status] ?? ['label' => '—', 'cls' => 'status-5'];

    // From/To strings
    $from = implode(', ', array_filter([
        $shipment->ship_from_first_name,
        $shipment->ship_from_address_1,
        $shipment->ship_from_city,
        $shipment->ship_from_state,
        $shipment->ship_from_zipcode,
        $shipment->ship_from_country,
    ]));

    $to = implode(', ', array_filter([
        $shipment->ship_to_first_name,
        $shipment->ship_to_address_1,
        $shipment->ship_to_city,
        $shipment->ship_to_state,
        $shipment->ship_to_zipcode,
        $shipment->ship_to_country,
    ]));

    // Safe JSON decode helper
    $safeJson = function ($val) {
        if (empty($val)) return null;
        try {
            return is_array($val) ? $val : json_decode($val, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            return null;
        }
    };

    $rateBreakdown    = $safeJson($shipment->rate_breakdown);
    $carrierRequest   = $safeJson($shipment->carrier_request);
    $carrierResponse  = $safeJson($shipment->carrier_response);
    $meta             = $safeJson($shipment->meta);

    // Tracking URL
    $provider = strtolower((string) $shipment->provider);
    $tn       = trim((string) $shipment->tracking_number);
    $trackingUrl = null;

    if ($tn !== '') {
        switch ($provider) {
            case 'dhl':
                $trackingUrl = "https://www.dhl.com/global-en/home/tracking/tracking-express.html?tracking-id={$tn}";
                break;
            case 'ups':
                $trackingUrl = "https://www.ups.com/track?loc=en_US&tracknum={$tn}";
                break;
            case 'fedex':
                $trackingUrl = "https://www.fedex.com/fedextrack/?tracknumbers={$tn}";
                break;
            default:
                $trackingUrl = null;
        }
    }

    // Label pack
    $label = [
        'provider'               => $shipment->provider,
        'service_code'           => $shipment->service_code,
        'service_name'           => $shipment->service_name,
        'payer'                  => $shipment->payer,
        'account_number'         => $shipment->account_number,
        'signature_option'       => $shipment->signature_option,
        'saturday_delivery'      => (bool) $shipment->saturday_delivery,
        'declared_value_total'   => $shipment->declared_value_total,
        'tracking_number'        => $shipment->tracking_number,
        'master_tracking_number' => $shipment->master_tracking_number,
        'label_format'           => $shipment->label_format,
        'label_url'              => $shipment->label_url,
        'invoice_url'            => $shipment->invoice_url,
        'customs_docs_url'       => $shipment->customs_docs_url,
        'pickup_id'              => $shipment->pickup_id,
        'rate_breakdown'         => $rateBreakdown,
        'carrier_request'        => $carrierRequest,
        'carrier_response'       => $carrierResponse,
        'meta'                   => $meta,
    ];

    $hasLabel    = !empty($shipment->label_url);
    $hasTracking = !empty($shipment->tracking_number);

    // Helper for nice sizes in KB/MB (used in blade)
    $humanSize = function (?int $bytes) {
        if (!$bytes) return '—';
        $units = ['B','KB','MB','GB','TB'];
        $i = 0; $n = $bytes;
        while ($n >= 1024 && $i < count($units)-1) { $n /= 1024; $i++; }
        return number_format($n, $n < 10 && $i > 0 ? 1 : 0) . ' ' . $units[$i];
    };

    return view('backend.shipment.shipmentview', [
        'shipment'     => $shipment,
        'status'       => $status,
        'from'         => $from,
        'to'           => $to,
        'label'        => $label,
        'hasLabel'     => $hasLabel,
        'hasTracking'  => $hasTracking,
        'trackingUrl'  => $trackingUrl,
        'humanSize'    => $humanSize, // pass helper to blade
    ]);
}


public function shipmentedit(\App\Models\Shipment $shipment)
{
    $shipment->load(['items', 'packages', 'customer', 'attachments']); // <-- attachments

    // ---- Status badge mapping: numeric + string keys both ----
    $statuses = [
        '1' => ['label' => 'Pending',   'cls' => 'status-1'],
        '2' => ['label' => 'In Transit','cls' => 'status-2'],
        '3' => ['label' => 'Delivered', 'cls' => 'status-3'],
        '4' => ['label' => 'Returned',  'cls' => 'status-4'],
        '5' => ['label' => 'Cancelled', 'cls' => 'status-5'],
        'pending'    => ['label' => 'Pending',   'cls' => 'status-1'],
        'in_transit' => ['label' => 'In Transit','cls' => 'status-2'],
        'delivered'  => ['label' => 'Delivered', 'cls' => 'status-3'],
        'returned'   => ['label' => 'Returned',  'cls' => 'status-4'],
        'cancelled'  => ['label' => 'Cancelled', 'cls' => 'status-5'],
    ];
    $statusKey = $shipment->status !== null ? (string) $shipment->status : '';
    $status = $statuses[$statusKey] ?? ['label' => '—', 'cls' => 'status-5'];

    // ---- From/To printable strings ----
    $from = $this->formatAddress(
        $shipment->ship_from_company,
        $shipment->ship_from_first_name,
        $shipment->ship_from_address_1,
        $shipment->ship_from_city,
        $shipment->ship_from_state,
        $shipment->ship_from_zipcode,
        $shipment->ship_from_country
    );
    $to = $this->formatAddress(
        $shipment->ship_to_company,
        $shipment->ship_to_first_name,
        $shipment->ship_to_address_1,
        $shipment->ship_to_city,
        $shipment->ship_to_state,
        $shipment->ship_to_zipcode,
        $shipment->ship_to_country
    );

    // ===== Datasets for the EDIT blade =====
    $lims_customer_list = class_exists(\App\Models\Customer::class)
        ? \App\Models\Customer::select('id','name','company_name')->orderBy('name')->get()
        : collect();

    $currency_list = class_exists(\App\Models\Currency::class)
        ? \App\Models\Currency::select('id','code','exchange_rate')->orderBy('code')->get()
        : collect();

    $currency = $currency_list->firstWhere('id', $shipment->currency_id);

    $general_setting = class_exists(\App\Models\GeneralSetting::class)
        ? (\App\Models\GeneralSetting::query()->first() ?: (object)['decimal' => 2])
        : (object)['decimal' => 2];

    // Units (rename to "name" so blade matches)
    $units = class_exists(\App\Models\Unit::class)
        ? \App\Models\Unit::selectRaw('id, COALESCE(unit_name,unit_name) as name, operator, operation_value')->orderByRaw('COALESCE(unit_name,unit_name)')->get()
        : collect();

    $lims_tax_list = class_exists(\App\Models\Tax::class)
        ? \App\Models\Tax::select('id','name','rate')->orderBy('name')->get()
        : collect();

    // Products (without variant)
    $lims_product_list_without_variant = collect();
    if (class_exists(\App\Models\Product::class)) {
        $lims_product_list_without_variant = \App\Models\Product::query()
            ->when(\Schema::hasColumn('products', 'is_variant'), fn ($q) => $q->where('is_variant', 0))
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->selectRaw('products.id, products.code, products.name, brands.title as title, products.cost')
            ->orderBy('products.name')
            ->get();
    }

    // Products (with variant)
    $lims_product_list_with_variant = collect();
    if (class_exists(\App\Models\ProductVariant::class)) {
        $lims_product_list_with_variant = \App\Models\ProductVariant::query()
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->selectRaw('
                product_variants.id as id,
                product_variants.item_code,
                products.name,
                brands.title as title,
                products.cost
            ')
            ->orderBy('products.name')
            ->get();
    }

    return view('backend.shipment.edit', [
        'shipment'                           => $shipment,
        'status'                             => $status,
        'from'                               => $from,
        'to'                                 => $to,
        'lims_customer_list'                 => $lims_customer_list,
        'currency_list'                      => $currency_list,
        'currency'                           => $currency,
        'general_setting'                    => $general_setting,
        'units'                              => $units,
        'lims_tax_list'                      => $lims_tax_list,
        'lims_product_list_without_variant'  => $lims_product_list_without_variant,
        'lims_product_list_with_variant'     => $lims_product_list_with_variant,
    ]);
}



 
        function formatAddress(...$parts): string
    {
        $parts = array_filter(array_map(fn($v) => $v ? trim($v) : null, $parts));
        return $parts ? implode(', ', $parts) : '—';
    }

public function shipmentupdate(Request $request, \App\Models\Shipment $shipment)
{   
    // --- 1) Validate Shipment fields ---
    $validated = $request->validate([
        'company_name'        => ['nullable','string','max:100'],
        'po_no'               => ['nullable','string','max:100'],
        'reference_no'        => ['nullable','string','max:100'],
        'customer_id'         => ['nullable','integer','exists:customers,id'],
        'status'              => ['nullable','string','max:50'],
        'ship_from_company'   => ['nullable','string','max:150'],
        'ship_from_first_name'=> ['nullable','string','max:150'],
        'ship_from_address_1' => ['nullable','string','max:255'],
        'ship_from_country'   => ['nullable','string','max:100'],
        'ship_from_state'     => ['nullable','string','max:100'],
        'ship_from_city'      => ['nullable','string','max:100'],
        'ship_from_zipcode'   => ['nullable','string','max:50'],
        'ship_from_contact'   => ['nullable','string','max:100'],
        'ship_from_email'     => ['nullable','email','max:150'],
        'ship_to_company'     => ['nullable','string','max:150'],
        'ship_to_first_name'  => ['nullable','string','max:150'],
        'ship_to_address_1'   => ['nullable','string','max:255'],
        'ship_to_country'     => ['nullable','string','max:100'],
        'ship_to_state'       => ['nullable','string','max:100'],
        'ship_to_city'        => ['nullable','string','max:100'],
        'ship_to_zipcode'     => ['nullable','string','max:50'],
        'ship_to_contact'     => ['nullable','string','max:100'],
        'ship_to_email'       => ['nullable','email','max:150'],
        'currency_id'         => ['nullable','integer'],
        'exchange_rate'       => ['nullable','numeric'],
        'service_code'        => ['nullable','string','max:100'],
        'saturday_delivery'   => ['nullable','boolean'],
        'signature_option'    => ['nullable','string','max:100'],
        'bill_to'             => ['nullable','string','max:100'],
        'ups_account'         => ['nullable','string','max:100'],
        'incoterms'           => ['nullable','string','max:50'],
        'contents_type'       => ['nullable','string','max:100'],
        'declared_value_total'=> ['nullable','numeric'],
        'order_tax_rate'      => ['nullable','numeric'],
        'order_discount'      => ['nullable','numeric'],
        'shipping_cost'       => ['nullable','numeric'],
        'comments'            => ['nullable','string'],
        'total_qty'           => ['nullable','numeric'],
        'total_tax'           => ['nullable','numeric'],
        'total_cost'          => ['nullable','numeric'],
        'item'                => ['nullable','string','max:255'],
        'order_tax'           => ['nullable','numeric'],
        'grand_total'         => ['nullable','numeric'],
        'paid_amount'         => ['nullable','numeric'],
        'payment_status'      => ['nullable','string','max:50'],
        'provider'            => ['nullable','string','max:100'],
        'service_name'        => ['nullable','string','max:100'],
        'payer'               => ['nullable','string','max:100'],
        'account_number'      => ['nullable','string','max:100'],
        'tracking_number'     => ['nullable','string','max:150'],
        'label_format'        => ['nullable','string','max:50'],
        'label_url'           => ['nullable','url','max:255'],
        'invoice_url'         => ['nullable','url','max:255'],
        'customs_docs_url'    => ['nullable','url','max:255'],
        'rate_breakdown'      => ['nullable','json'],
        'carrier_request'     => ['nullable','json'],
        'carrier_response'    => ['nullable','json'],
        'meta'                => ['nullable','json'],

        // Items / Packages are posted as flat arrays in your blade; keep your own handling below.

        // Attachments
        'attach_title'              => ['nullable','array'],
        'attach_title.*'            => ['nullable','string','max:190'],
        'delete_attachment_ids'     => ['nullable','array'],
        'delete_attachment_ids.*'   => ['integer','exists:shipment_attachments,id'],
        'new_attachments'           => ['nullable','array'],
        'new_attachments.*'         => ['file','mimes:pdf,jpg,jpeg,png,webp','max:5120'],
        'new_titles'                => ['nullable','array'],
        'new_titles.*'              => ['nullable','string','max:190'],
    ]);

    // --- 2) Update Shipment itself ---
    $shipment->update($validated);

    // --- 3) Upsert Items ---
    // NOTE: your blade currently posts flat arrays (product_id[], product_code[] ...)
    // so keep your existing handling or map them to $request->input('items', []) first.
    // (left as-is from your previous code)

    $keptItemIds = [];
    // If you already had flat arrays, reconstruct rows:
    $productIds = $request->input('product_id', []);
    $productCodes = $request->input('product_code', []);
    $units = $request->input('product_unit', []);
    $qtys = $request->input('qty', []);
    $prices = $request->input('net_unit_cost', []);
    $discounts = $request->input('discount', []);
    $subtotals = $request->input('subtotal', []);
    $itemIds = $request->input('item_id', []);

    for ($i=0; $i < count($productIds); $i++) {
        $data = [
            'product_id'    => $productIds[$i] ?? null,
            'product_code'  => $productCodes[$i] ?? null,
            'product_unit'  => $units[$i] ?? null,
            'qty'           => $qtys[$i] ?? null,
            'net_unit_cost' => $prices[$i] ?? null,
            'discount'      => $discounts[$i] ?? null,
            'subtotal'      => $subtotals[$i] ?? null,
        ];
        if (!empty($itemIds[$i])) {
            $item = \App\Models\ShipmentItem::where('shipment_id', $shipment->id)->where('id', $itemIds[$i])->first();
            if ($item) {
                $item->update($data);
                $keptItemIds[] = $item->id;
            }
        } else {
            $item = $shipment->items()->create($data);
            $keptItemIds[] = $item->id;
        }
    }
    $shipment->items()->whereNotIn('id', $keptItemIds ?: [0])->delete();

    // --- 4) Upsert Packages ---
    $keptPackageIds = [];
    $pkgIds   = $request->input('package_id', []);
    $pkgTypes = $request->input('packaging', []);
    $pkgWts   = $request->input('weight', []);
    $pkgWtU   = $request->input('weight_unit', []);
    $pkgL     = $request->input('length', []);
    $pkgW     = $request->input('width', []);
    $pkgH     = $request->input('height', []);
    $pkgDimU  = $request->input('dim_unit', []);
    $pkgVal   = $request->input('declared_value', []);
    $pkgNote  = $request->input('dimensions_note', []);

    $rows = max(count($pkgTypes), count($pkgIds));
    for ($i=0; $i<$rows; $i++) {
        $data = [
            'packaging'       => $pkgTypes[$i] ?? null,
            'weight'          => $pkgWts[$i] ?? null,
            'weight_unit'     => $pkgWtU[$i] ?? null,
            'length'          => $pkgL[$i] ?? null,
            'width'           => $pkgW[$i] ?? null,
            'height'          => $pkgH[$i] ?? null,
            'dim_unit'        => $pkgDimU[$i] ?? null,
            'declared_value'  => $pkgVal[$i] ?? null,
            'dimensions_note' => $pkgNote[$i] ?? null,
        ];
        if (!empty($pkgIds[$i])) {
            $pkg = \App\Models\ShipmentPackage::where('shipment_id', $shipment->id)->where('id', $pkgIds[$i])->first();
            if ($pkg) {
                $pkg->update($data);
                $keptPackageIds[] = $pkg->id;
            }
        } else {
            // skip completely empty rows
            if (array_filter($data, fn($v)=>!is_null($v) && $v!=='') ) {
                $pkg = $shipment->packages()->create($data);
                $keptPackageIds[] = $pkg->id;
            }
        }
    }
    $shipment->packages()->whereNotIn('id', $keptPackageIds ?: [0])->delete();

    // --- 5) Attachments: rename titles ---
    if ($request->filled('attach_title')) {
        foreach ($request->input('attach_title') as $attId => $title) {
            $att = \App\Models\ShipmentAttachment::where('shipment_id', $shipment->id)->where('id', $attId)->first();
            if ($att) {
                $att->title = $title ?: ($att->title ?? $att->original_name);
                $att->save();
            }
        }
    }

    // --- 6) Attachments: delete selected ---
    if ($request->filled('delete_attachment_ids')) {
        $ids = (array) $request->input('delete_attachment_ids', []);
        $toDel = \App\Models\ShipmentAttachment::where('shipment_id', $shipment->id)
            ->whereIn('id', $ids)->get();
        foreach ($toDel as $att) {
            try {
                if (!empty($att->path)) {
                    Storage::disk($att->disk ?? 'public')->delete($att->path);
                }
            } catch (\Throwable $e) {}
            $att->delete();
        }
    }

    // --- 7) Attachments: store new files ---
    if ($request->hasFile('new_attachments')) {
        $files = $request->file('new_attachments');
        $titles= $request->input('new_titles', []);
        foreach ($files as $idx => $file) {
            if (!$file->isValid()) continue;

            $disk = 'public';
            $dir  = 'shipment_attachments/'.$shipment->id;
            $path = $file->store($dir, $disk);

            $mime = $file->getClientMimeType();
            $size = $file->getSize();
            $orig = $file->getClientOriginalName();
            $title= $titles[$idx] ?? pathinfo($orig, PATHINFO_FILENAME);

            $type = 'other';
            if (str_contains($mime, 'pdf')) $type='pdf';
            elseif (str_contains($mime, 'image')) $type='image';

            \App\Models\ShipmentAttachment::create([
                'shipment_id'   => $shipment->id,
                'title'         => $title,
                'original_name' => $orig,
                'disk'          => $disk,
                'path'          => $path,
                'size'          => $size,
                'mime'          => $mime,
                'type'          => $type,
                'uploaded_by'   => auth()->id(),
            ]);
        }
    }

    return redirect('shipment/index')->with('message','Shipment Update successfully.');
}


public function shipmentdestroy($id)
{
    $shipment = Shipment::with(['items','packages'])->findOrFail($id);

    try {
        DB::transaction(function () use ($shipment) {
            // agar FK cascade nahi hai to pehle children hatao
            $shipment->items()->delete();
            $shipment->packages()->delete();

            // ab parent
            $shipment->delete();
        });

        // apni listing route par bhej dein (agar hai)
        return redirect()
            ->route('shipment/index')   // agar listing route nahi, to ->back()
            ->with('message', 'Shipment deleted successfully.');
    } catch (\Throwable $e) {
        return back()->with('not_permitted', 'Delete failed: '.$e->getMessage());
    }
}

public function shipmentLabelCreate(Request $request, Shipment $shipment)
{
    // 1) Validate inputs coming from the modal
    $v = $request->validate([
        'provider'              => ['required','string','max:50'],   // dhl/ups/fedex/other
        'service_code'          => ['nullable','string','max:191'],
        'service_name'          => ['nullable','string','max:191'],
        'payer'                 => ['nullable','string','max:50'],   // shipper/receiver/third_party
        'account_number'        => ['nullable','string','max:191'],
        'signature_option'      => ['nullable','string','max:50'],   // none/direct/adult
        'saturday_delivery'     => ['nullable','in:0,1'],
        'declared_value_total'  => ['nullable','numeric','min:0'],
        'currency'              => ['nullable','string','max:10'],   // only if you want to display/record currency text
        'reference'             => ['nullable','string','max:191'],  // label reference (optional)
        'tracking_number'       => ['nullable','string','max:191'],
        'notes'                 => ['nullable','string'],
        'rate_amount'           => ['nullable','numeric','min:0'],   // store in rate_breakdown JSON
        'label_format'          => ['nullable','string','max:20'],   // PDF/ZPL/etc
        // If later you add file uploads or URLs:
        // 'label_url'          => ['nullable','url'],
        // 'invoice_url'        => ['nullable','url'],
        // 'customs_docs_url'   => ['nullable','url'],
    ]);

    // 2) Build the update payload
    // NOTE: We overwrite fields with what user sent (including nulls if left blank).
    // If you prefer to NOT clear existing values when input is blank, use $request->filled('field') checks.
    $update = [
        'provider'             => $v['provider'],
        'service_code'         => $v['service_code']         ?? null,
        'service_name'         => $v['service_name']         ?? null,
        'payer'                => $v['payer']                ?? null,
        'account_number'       => $v['account_number']       ?? null,
        'signature_option'     => $v['signature_option']     ?? null,
        'saturday_delivery'    => isset($v['saturday_delivery']) ? (int)$v['saturday_delivery'] : 0,
        'declared_value_total' => $v['declared_value_total'] ?? null,
        'tracking_number'      => $v['tracking_number']      ?? null,
        'label_format'         => $v['label_format']         ?? ($shipment->label_format ?: 'PDF'),
        // If you later want to store the currency text somewhere in meta:
        // 'meta'              => json_encode([...]),
    ];

    // 3) Merge rate into JSON (rate_breakdown) if provided
    if (array_key_exists('rate_amount', $v) && $v['rate_amount'] !== null) {
        $existing = [];
        if (!empty($shipment->rate_breakdown)) {
            try { $existing = (array) json_decode($shipment->rate_breakdown, true); } catch (\Throwable $e) {}
        }
        $existing['estimated_rate'] = (float)$v['rate_amount'];
        $update['rate_breakdown'] = json_encode($existing);
    }

    // 4) Optionally append notes into comments (so you still retain old comments)
    if (array_key_exists('notes', $v)) {
        $notes = trim((string)$v['notes']);
        if ($notes !== '') {
            $update['comments'] = trim(($shipment->comments ? ($shipment->comments . "\n\n") : '') . $notes);
        } else {
            // If you want to clear comments when notes are blank, uncomment next line:
            // $update['comments'] = null;
        }
    }

    // 5) Persist atomically
    DB::transaction(function () use ($shipment, $update) {
        $shipment->update($update);
    });

    return redirect()
        ->route('shipment.show', $shipment->id)
        ->with('message', 'Shipping label details updated successfully.');
}
 
    public function create()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-add')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
             $lims_customer_list = Customer::with('user')->get();
            if(Auth::user()->role_id > 2) 
            {
                $lims_customer_list = Customer::with('user')
                ->where('user_id', Auth::user()->id)
                ->whereHas('user', function ($query) {
                $query->where('role_id', Auth::user()->role_id);
                })
                ->get();
            }
     
            if(Auth::user()->role_id > 2) {
                $lims_warehouse_list = Warehouse::where([
                    ['is_active', true],
                    ['id', Auth::user()->warehouse_id]
                ])->get();
            }
            else {
                $lims_warehouse_list = Warehouse::where('is_active', true)->get();
                $lims_production_list = Wproduction::where('is_active', true)->get();
            }
            $lims_tax_list = Tax::where('is_active', true)->get();
        $lims_product_list_without_variant = $this->productWithoutVariant();
        $lims_product_list_with_variant = $this->productWithVariant();
        $currency_list = Currency::where('is_active', true)->get();
        $custom_fields = CustomField::where('belongs_to', 'purchase')->get();
            return view('backend.purchase.create', compact('lims_customer_list','lims_supplier_list', 'lims_warehouse_list','lims_production_list', 'lims_tax_list', 'lims_product_list_without_variant', 'lims_product_list_with_variant', 'currency_list', 'custom_fields'));
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }


 public function getRawMterial(Request $request)
    {
        
        
        
        $productId = (int) $request->product_id;
        if (!$productId) {
            return response()->json(['ok' => false, 'error' => 'product_id missing'], 422);
        }

        // Raw materials joined to products to get names/codes
        $materials = DB::table('product_raw_material as prm')
            ->leftJoin('products as p', 'p.id', '=', 'prm.raw_material_id')
            ->where('prm.product_id', $productId)
            ->orderByRaw('COALESCE(prm.sort_order, 999999), prm.id')
            ->get([
                'prm.id',
                'prm.product_id',
                'prm.raw_material_id',
                'p.name as raw_material_name',
                'p.code as raw_material_code',
                'prm.product_code',           // optional, sometimes vendor code
                'prm.quantity',
                'prm.unit',
                'prm.unit_price',
                'prm.wastage_pct',
                'prm.percent_w_w',
                'prm.lbs_per_1k_gal',
                'prm.gal_per_1k_gal',
                'prm.sort_order',
            ]);

        // Single specs row (latest wins)
        $specs = DB::table('product_specs')
            ->where('product_id', $productId)
            ->orderByDesc('id')
            ->first([
                'density_lbs_per_gal',
                'ph',
                'brix',
                'taste',
                'appearance',
                'process',
                'yield_gallons',
                'formula_date',
                'batching_instructions',
            ]);

        // Finished product meta (for header)
        $product = DB::table('products')->where('id', $productId)->first(['id', 'name', 'code']);

        return response()->json([
            'ok'        => true,
            'product'   => $product,
            'materials' => $materials,
            'specs'     => $specs,
        ]);
    }
    
    public function store(StorePurchaseRequest$request)
    { 
        DB::beginTransaction();

        try {
            $data = $request->except('document');
            $data['user_id'] = $data['customer_id'];
            if (empty($data['customer_id'])) { 
                $data['user_id'] = Auth::id();
            }
            
            if(!isset($data['reference_no']))
            {
            $data['reference_no'] = 'pr-' . date("Ymd") . '-'. date("his");
            }
            
            $document = $request->document;
            // return dd($data);
            if ($document) {
                $v = Validator::make(
                    [
                        'extension' => strtolower($request->document->getClientOriginalExtension()),
                    ],
                    [
                        'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                    ]
                );
                if ($v->fails())
                    return redirect()->back()->withErrors($v->errors());

                $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
                $documentName = date("Ymdhis");
                if(!config('database.connections.saleprosaas_landlord')) {
                    $documentName = $documentName . '.' . $ext;
                    $document->move(public_path('documents/purchase'), $documentName);
                }
                else {
                    $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                    $document->move(public_path('documents/purchase'), $documentName);
                }
                $data['document'] = $documentName;
            }

            if(isset($data['created_at'])) {
                $data['created_at'] = str_replace("/","-",$data['created_at']);
                $data['created_at'] = date("Y-m-d H:i:s", strtotime($data['created_at']));
            }
            else
                $data['created_at'] = date("Y-m-d H:i:s");
            // return dd($data);
            // if(empty($data['po_no'])) {
            //  $data['po_no'] = $this->generatePoNumber();
            //  }
             $data['system_po_no'] = $this->generatePoNumber();
            $lims_purchase_data = Purchase::create($data);
            // ==================log data =========================
            $logFields = $request->except(['_token', 'document','product_code_name']);
$convertedLog = [];

foreach ($logFields as $key => $value) {
    switch ($key) {
        case 'warehouse_id':
            $warehouse = Warehouse::find($value);
            $convertedLog['warehouse'] = $warehouse ? $warehouse->name : $value;
            break;

        case 'customer_id':
            $customer = Customer::find($value);
            $convertedLog['customer'] = $customer ? $customer->name : $value;
            break;

        case 'currency_id':
            $currency = Currency::find($value);
            $convertedLog['currency'] = $currency ? $currency->code : $value;
            break;

        case 'supplier_name':
            $supplierIds = is_array($value) ? $value : explode(',', $value);
            $suppliers = Supplier::whereIn('id', $supplierIds)->pluck('name')->toArray();
            $convertedLog['suppliers'] = implode(', ', $suppliers);
            break;

        case 'product_id':
            $productNames = Product::whereIn('id', $value)->pluck('name')->toArray();
            $convertedLog['products'] = implode(', ', $productNames);
            break;

        default:
            // multi-input values as string
            $convertedLog[$key] = is_array($value) ? implode(', ', $value) : $value;
    }
}

product_purchase_log::create([
    'purchase_id' => $lims_purchase_data->id,
    'user_id' => Auth::id(),
    'notes' => json_encode($convertedLog),
]);

            // ==================log data =========================
            // return $lims_purchase_data;
            //inserting data for custom fields
            $custom_field_data = [];
            $custom_fields = CustomField::where('belongs_to', 'purchase')->select('name', 'type')->get();
            foreach ($custom_fields as $type => $custom_field) {
                $field_name = str_replace(' ', '_', strtolower($custom_field->name));
                if(isset($data[$field_name])) {
                    if($custom_field->type == 'checkbox' || $custom_field->type == 'multi_select')
                        $custom_field_data[$field_name] = implode(",", $data[$field_name]);
                    else
                        $custom_field_data[$field_name] = $data[$field_name];
                }
            }
            if(count($custom_field_data))
                DB::table('purchases')->where('id', $lims_purchase_data->id)->update($custom_field_data);
            
            $product_id = $data['product_id'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $recieved = $data['recieved'];
            $batch_no = $data['batch_no'];
            $lot_no = $request->input('lot_no', []);
            $expired_date = $data['expired_date'];
             $supplier_ids = $data['supplier_name'];
             $ets_date = $data['ets_date'];
             $eta_date = $data['eta_date'];
             $etd_date = $data['etd_date'];
             $moq = $data['moq'];
             $ship_cost = $data['ship_cost'];
            $purchase_unit = $data['purchase_unit'];
            $net_unit_cost = $data['net_unit_cost'];
            $discount = $data['discount'];
            $tax_rate = $data['tax_rate'];
            $tax = $data['tax'];
            $total = $data['subtotal'];
            $imei_numbers = $data['imei_number'];
            $product_purchase = [];
            
            foreach ($product_id as $i => $id) {
                $lims_purchase_unit_data  = Unit::where('unit_name', $purchase_unit[$i])->first();

                if ($lims_purchase_unit_data->operator == '*') {
                    $quantity = $recieved[$i] * $lims_purchase_unit_data->operation_value;
                } else {
                    $quantity = $recieved[$i] / $lims_purchase_unit_data->operation_value;
                }
                $lims_product_data = Product::find($id);
                $price = $lims_product_data->price;
                //dealing with product barch
                if($batch_no[$i]) {
                    $product_batch_data = ProductBatch::where([
                                            ['product_id', $lims_product_data->id],
                                            ['batch_no', $batch_no[$i]],
                                            ['lot_no', $lot_no[$i]]
                                        ])->first();
                    if($product_batch_data) {
                        $product_batch_data->expired_date = $expired_date[$i];
                        $product_batch_data->qty += $quantity;
                       $product_batch_data->save();
                    }
                    else {
                        $product_batch_data = ProductBatch::create([
                                                'product_id' => $lims_product_data->id,
                                                'batch_no' => $batch_no[$i],
                                                'lot_no' => $lot_no[$i] ?? null,
                                                'expired_date' => $expired_date[$i],
                                                'qty' => $quantity
                                            ]);
                    }
                    $product_purchase['product_batch_id'] = $product_batch_data->id;
                }
                else
                    $product_purchase['product_batch_id'] = null;

                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($lims_product_data->id, $product_code[$i])->first();
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $id],
                        ['variant_id', $lims_product_variant_data->variant_id],
                        ['warehouse_id', $data['warehouse_id']]
                    ])->first();
                    $product_purchase['variant_id'] = $lims_product_variant_data->variant_id;
                    //add quantity to product variant table
                    $lims_product_variant_data->qty += $quantity;
                   $lims_product_variant_data->save();

                    // Update product name with variant
                    // if (strpos($lims_product_data->name, ")")) {
                    //     continue;
                    // }
                    // $variant = Variant::where('id', $lims_product_variant_data->variant_id)->select('name')->first();
                    // $lims_product_data->name = $lims_product_data->name . '(' . $variant->name . ')';
                    // $lims_product_data->save();
                }
                else {
                    $product_purchase['variant_id'] = null;
                    if($product_purchase['product_batch_id']) {
                        //checking for price
                        $lims_product_warehouse_data = Product_Warehouse::where([
                                                        ['product_id', $id],
                                                        ['warehouse_id', $data['warehouse_id'] ],
                                                    ])
                                                    ->whereNotNull('price')
                                                    ->select('price')
                                                    ->first();
                        if($lims_product_warehouse_data)
                            $price = $lims_product_warehouse_data->price;
                        else
                            $price = null;
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $id],
                            ['product_batch_id', $product_purchase['product_batch_id'] ],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                    else {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $id],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                }
                //add quantity to product table
                $lims_product_data->qty = $lims_product_data->qty + $quantity;
               $lims_product_data->save();
                //add quantity to warehouse
                if ($lims_product_warehouse_data) {
                    $lims_product_warehouse_data->qty = $lims_product_warehouse_data->qty + $quantity;
                    $lims_product_warehouse_data->product_batch_id = $product_purchase['product_batch_id'];
                }
                else {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $id;
                    $lims_product_warehouse_data->product_batch_id = $product_purchase['product_batch_id'];
                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    $lims_product_warehouse_data->qty = $quantity;
                    if($price)
                        $lims_product_warehouse_data->price = $price;
                    if($lims_product_data->is_variant)
                        $lims_product_warehouse_data->variant_id = $lims_product_variant_data->variant_id;
                }
                
                if($imei_numbers[$i]) {
                    // prevent duplication
                    $imeis = explode(',', $imei_numbers[$i]);
                    $imeis = array_map('trim', $imeis);
                    if (count($imeis) !== count(array_unique($imeis))) {
                        DB::rollBack();
                        return redirect('purchases/create')->with('not_permitted', __('db.Duplicate IMEI not allowed!'));
                    }
                    foreach ($imeis as $imei) {
                        if ($this->isImeiExist($imei, $id)) {
                           DB::rollBack();
                            return redirect('purchases/create')->with('not_permitted', __('db.Duplicate IMEI not allowed!'));
                        }
                    }
                    //added imei numbers to product_warehouse table
                    if($lims_product_warehouse_data->imei_number)
                        $lims_product_warehouse_data->imei_number .= ',' . $imei_numbers[$i];
                    else
                        $lims_product_warehouse_data->imei_number = $imei_numbers[$i];
                }
               $lims_product_warehouse_data->save();

                $product_purchase['purchase_id'] = $lims_purchase_data->id ;
                $product_purchase['product_id'] = $id;
                $product_purchase['imei_number'] = $imei_numbers[$i];
                $product_purchase['qty'] = $qty[$i];
                $product_purchase['recieved'] = $recieved[$i];
                $product_purchase['purchase_unit_id'] = $lims_purchase_unit_data->id;
                $product_purchase['net_unit_cost'] = $net_unit_cost[$i];
                $product_purchase['discount'] = $discount[$i];
                $product_purchase['tax_rate'] = $tax_rate[$i];
                $product_purchase['tax'] = $tax[$i];
                $product_purchase['total'] = $total[$i];
                $product_purchase['supplier_id'] = $supplier_ids[$i];
    $product_purchase['ets_date'] = date('Y-m-d', strtotime($ets_date[$i]));
    $product_purchase['eta_date'] = date('Y-m-d', strtotime($eta_date[$i]));
    $product_purchase['etd_date'] = date('Y-m-d', strtotime($etd_date[$i]));
                $product_purchase['moq'] = $moq[$i];
                $product_purchase['ship_cost'] = $ship_cost[$i];
                
                ProductPurchase::create($product_purchase);
                // echo "<pre>";
                // print_r($product_purchase);
                // echo "</pre>";
            }

           DB::commit();
         // return 1;
           return redirect('purchases')->with('message', __('db.Purchase created successfully'));
        } catch (\Exception $e) {
           DB::rollBack();
            return redirect('purchases/create')->with('not_permitted', 'Transaction failed: ' . $e->getMessage());
        }
    }

    public function purchaseByCsv()
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-add')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();

            return view('backend.purchase.import', compact('lims_supplier_list', 'lims_warehouse_list', 'lims_tax_list'));
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));
    }

    public function importPurchase(Request $request)
    {
        DB::beginTransaction();

        try {
            // return dd($request->all());
            //get the file
            $upload=$request->file('file');
            $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
            //checking if this is a CSV file
            if($ext != 'csv')
                return redirect()->back()->with('message', __('db.Please upload a CSV file'));

            $filePath=$upload->getRealPath();
            $file_handle = fopen($filePath, 'r');
            $i = 0;

            $qty = [];
            $tax = [];
            $discount = [];
            //validate the file
            while (!feof($file_handle) ) {
                $current_line = fgetcsv($file_handle);
                if($current_line && $i > 0){
                    // return dd($current_line);
                    $product_data[] = Product::where([
                                        ['code', $current_line[0]],
                                        ['is_active', true]
                                    ])->first();
                    if(!$product_data[$i-1])
                        return redirect()->back()->with('message', 'Product with this code '.$current_line[0].' does not exist!');
                    $unit[] = Unit::where('unit_code', $current_line[2])->first();
                    if(!$unit[$i-1])
                        return redirect()->back()->with('message', __('db.Purchase unit does not exist!'));
                    if(strtolower($current_line[5]) != "no tax"){
                        $tax[] = Tax::where('name', $current_line[5])->first();
                        if(!$tax[$i-1])
                            return redirect()->back()->with('message', __('db.Tax name does not exist!'));
                    }
                    else
                        $tax[$i-1]['rate'] = 0;

                    $qty[] = $current_line[1];
                    $cost[] = $current_line[3];
                    $discount[] = $current_line[4];
                    if (isset($current_line[6]) && $product_data[$i-1]->is_imei) {
                        $product_data[$i-1]->imei_number = $current_line[6];
                    }
                }
                $i++;
            }
            // return dd($product_data, 'hello');

            $data = $request->except('file');
            if(isset($data['created_at'])) {
                $dateNow = str_replace("/","-",$data['created_at']);
                $data['created_at'] = date("Y-m-d H:i:s", strtotime($dateNow));
                $data['updated_at'] = date("Y-m-d H:i:s", strtotime($dateNow));
            }
            else {
                $data['created_at'] = date("Y-m-d H:i:s");
                $data['updated_at'] = date("Y-m-d H:i:s");
            }
            if(!isset($data['reference_no']))
            {
                $data['reference_no'] = 'pr-' . date("Ymd") . '-'. date("his");
            }

            $document = $request->document;
            if ($document) {
                $v = Validator::make(
                    [
                        'extension' => strtolower($request->document->getClientOriginalExtension()),
                    ],
                    [
                        'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                    ]
                );
                if ($v->fails())
                    return redirect()->back()->withErrors($v->errors());

                $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
                $documentName = date("Ymdhis");
                if(!config('database.connections.saleprosaas_landlord')) {
                    $documentName = $documentName . '.' . $ext;
                    $document->move(public_path('documents/purchase'), $documentName);
                }
                else {
                    $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                    $document->move(public_path('documents/purchase'), $documentName);
                }
                $data['document'] = $documentName;
            }
            $item = 0;
            $grand_total = $data['shipping_cost'];
            $data['user_id'] = Auth::id();
            Purchase::create($data);
            $lims_purchase_data = Purchase::latest()->first();

            foreach ($product_data as $key => $product) {
                if(isset($product->imei_number)) {
                    // prevent duplication
                    if ($this->isImeiExist($product->imei_number, $product->id)) {
                        DB::rollBack();
                        return redirect('purchases/purchase_by_csv')->with('not_permitted', __('db.Duplicate IMEI not allowed!'));
                    }
                }
                $qty[$key] = (int) str_replace(",", "", $qty[$key]);
                $cost[$key] = (float) str_replace(",", "", $cost[$key]);
                $discount[$key] = (float) str_replace(",", "", $discount[$key]);
                if($product['tax_method'] == 1){
                    // return dd($cost);
                    $net_unit_cost = $cost[$key] - $discount[$key];
                    $product_tax = $net_unit_cost * ($tax[$key]['rate'] / 100) * $qty[$key];
                    $total = ($net_unit_cost * $qty[$key]) + $product_tax;
                }
                elseif($product['tax_method'] == 2){
                    $net_unit_cost = (100 / (100 + $tax[$key]['rate'])) * ($cost[$key] - $discount[$key]);
                    $product_tax = ($cost[$key] - $discount[$key] - $net_unit_cost) * $qty[$key];
                    $total = ($cost[$key] - $discount[$key]) * $qty[$key];
                }
                if($data['status'] == 1){
                    if($unit[$key]['operator'] == '*')
                        $quantity = $qty[$key] * $unit[$key]['operation_value'];
                    elseif($unit[$key]['operator'] == '/')
                        $quantity = $qty[$key] / $unit[$key]['operation_value'];
                    $product['qty'] += $quantity;
                    $product_warehouse = Product_Warehouse::where([
                        ['product_id', $product['id']],
                        ['warehouse_id', $data['warehouse_id']]
                    ])->first();
                    if($product_warehouse) {
                        $product_warehouse->qty += $quantity;
                        if (isset($product->imei_number)) {
                            if (empty($product_warehouse->imei_number)) {
                                $product_warehouse->imei_number = $product->imei_number;
                            } else {
                                $product_warehouse->imei_number .= ',' . $product->imei_number;
                            }
                        }
                        $product_warehouse->save();
                    }
                    else {
                        $lims_product_warehouse_data = new Product_Warehouse();
                        $lims_product_warehouse_data->product_id = $product['id'];
                        $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                        $lims_product_warehouse_data->qty = $quantity;
                        if (isset($product->imei_number)) {
                            $lims_product_warehouse_data->imei_number = $product->imei_number;
                        }
                        $lims_product_warehouse_data->save();
                    }
                    $temp = $product->imei_number ?? '';
                    if (isset($product->imei_number)) {
                        unset($product->imei_number);
                    }
                    
                    $product->save();

                    if ($temp != '')
                        $product->imei_number = $temp;
                }

                $product_purchase = new ProductPurchase();
                $product_purchase->purchase_id = $lims_purchase_data->id;
                $product_purchase->product_id = $product['id'];
                $product_purchase->qty = $qty[$key];
                if($data['status'] == 1)
                    $product_purchase->recieved = $qty[$key];
                else
                    $product_purchase->recieved = 0;
                $product_purchase->purchase_unit_id = $unit[$key]['id'];
                $product_purchase->net_unit_cost = number_format((float)$net_unit_cost, config('decimal'), '.', '');
                $product_purchase->discount = $discount[$key] * $qty[$key];
                $product_purchase->tax_rate = $tax[$key]['rate'];
                $product_purchase->tax = number_format((float)$product_tax, config('decimal'), '.', '');
                $product_purchase->total = number_format((float)$total, config('decimal'), '.', '');
                if (isset($product->imei_number)) {
                    if (empty($product_purchase->imei_number)) {
                        $product_purchase->imei_number = $product->imei_number;
                    } else {
                        $product_purchase->imei_number .= ',' . $product->imei_number;
                    }
                }
                $product_purchase->save();
                $lims_purchase_data->total_qty += $qty[$key];
                $lims_purchase_data->total_discount += $discount[$key] * $qty[$key];
                $lims_purchase_data->total_tax += number_format((float)$product_tax, config('decimal'), '.', '');
                $lims_purchase_data->total_cost += number_format((float)$total, config('decimal'), '.', '');
            }
            $lims_purchase_data->item = $key + 1;
            $lims_purchase_data->order_tax = ($lims_purchase_data->total_cost - $lims_purchase_data->order_discount) * ($data['order_tax_rate'] / 100);
            $lims_purchase_data->grand_total = ($lims_purchase_data->total_cost + $lims_purchase_data->order_tax + $lims_purchase_data->shipping_cost) - $lims_purchase_data->order_discount;
            $lims_purchase_data->save();

            DB::commit();
            return redirect('purchases');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect('purchases/purchase_by_csv')->with('not_permitted', $e->getMessage());
        }
    }

    public function purchaseData(Request $request)
    {
        $columns = array(
            1 => 'created_at',
            2 => 'reference_no',
            5 => 'grand_total',
            6 => 'paid_amount',
        );

        $warehouse_id = $request->input('warehouse_id');
        $purchase_status = $request->input('purchase_status');
        $payment_status = $request->input('payment_status');

        $q = Purchase::whereDate('created_at', '>=' ,$request->input('starting_date'))->whereDate('created_at', '<=' ,$request->input('ending_date'));
        //check staff access
        $this->staffAccessCheck($q);
        if($warehouse_id)
            $q = $q->where('warehouse_id', $warehouse_id);
        if($purchase_status)
            $q = $q->where('status', $purchase_status);
        if($payment_status)
            $q = $q->where('payment_status', $payment_status);

        $totalData = $q->count();
        $totalFiltered = $totalData;

        if($request->input('length') != -1)
            $limit = $request->input('length');
        else
            $limit = $totalData;
        $start = $request->input('start');
        $order = 'purchases.'.$columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');
        //fetching custom fields data
        $custom_fields = CustomField::where([
                        ['belongs_to', 'purchase'],
                        ['is_table', true]
                    ])->pluck('name');
        $field_names = [];
        foreach($custom_fields as $fieldName) {
            $field_names[] = str_replace(" ", "_", strtolower($fieldName));
        }
        if(empty($request->input('search.value'))) {
        $q = Purchase::with('supplier', 'warehouse','wproduction')
                ->whereDate('created_at', '>=' ,$request->input('starting_date'))
                ->whereDate('created_at', '<=' ,$request->input('ending_date'))
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir);
            //check staff access
            $this->staffAccessCheck($q);
            if($warehouse_id)
                $q = $q->where('warehouse_id', $warehouse_id);
            if($purchase_status)
                $q = $q->where('status', $purchase_status);
            if($payment_status)
                $q = $q->where('payment_status', $payment_status);
            // \DB::enableQueryLog();
            $purchases = $q->orderBy('purchases.id', 'desc')->get();
            // dd(\DB::getQueryLog());
           
        }
        else
        {
            $search = $request->input('search.value');
            $q = Purchase::join('product_purchases', 'purchases.id', '=', 'product_purchases.purchase_id')
                ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
                ->whereDate('purchases.created_at', '=' , date('Y-m-d', strtotime(str_replace('/', '-', $search))))
                ->offset($start)
                ->limit($limit)
                ->orderBy($order,$dir);
            if(Auth::user()->role_id > 2 && config('staff_access') == 'own') {
                $q =  $q->with('supplier', 'warehouse','wproduction'
            )
                        ->where('purchases.user_id', Auth::id())
                        ->orwhere([
                            ['purchases.reference_no', 'LIKE', "%{$search}%"],
                            ['purchases.user_id', Auth::id()]
                        ])
                        ->orwhere([
                            ['suppliers.name', 'LIKE', "%{$search}%"],
                            ['purchases.user_id', Auth::id()]
                        ])
                        ->orwhere([
                            ['product_purchases.imei_number', 'LIKE', "%{$search}%"],
                            ['purchases.user_id', Auth::id()]
                        ]);
                foreach ($field_names as $key => $field_name) {
                    $q = $q->orwhere([
                            ['purchases.user_id', Auth::id()],
                            ['purchases.' . $field_name, 'LIKE', "%{$search}%"]
                        ]);
                }
            }
            elseif(Auth::user()->role_id > 2 && config('staff_access') == 'warehouse') {
                $q =  $q->with('supplier', 'warehouse','wproduction'
            )
                ->where('purchases.user_id', Auth::id())
                ->orwhere([
                    ['purchases.reference_no', 'LIKE', "%{$search}%"],
                    ['purchases.warehouse_id', Auth::user()->warehouse_id]
                ])
                ->orwhere([
                    ['suppliers.name', 'LIKE', "%{$search}%"],
                    ['purchases.warehouse_id', Auth::user()->warehouse_id]
                ])
                ->orwhere([
                    ['product_purchases.imei_number', 'LIKE', "%{$search}%"],
                    ['purchases.warehouse_id', Auth::user()->warehouse_id]
                ]);
                foreach ($field_names as $key => $field_name) {
                    $q = $q->orwhere([
                        ['purchases.warehouse_id', Auth::user()->warehouse_id],
                        ['purchases.' . $field_name, 'LIKE', "%{$search}%"]
                    ]);
                }
            }
            else {
                $q = $q->with('supplier', 'warehouse','wproduction'
            )
                    ->orwhere('purchases.reference_no', 'LIKE', "%{$search}%")
                    ->orwhere('purchases.po_no', 'LIKE', "%{$search}%")
                    ->orwhere('suppliers.name', 'LIKE', "%{$search}%")
                    ->orwhere('product_purchases.imei_number', 'LIKE', "%{$search}%");
                foreach ($field_names as $key => $field_name) {
                    $q = $q->orwhere('purchases.' . $field_name, 'LIKE', "%{$search}%");
                }
            }
             $purchases = $q->select('purchases.*')->groupBy('purchases.id') ->orderBy('purchases.id', 'desc')->get();


            $totalFiltered = $q->groupBy('purchases.id')->count();
        }

     
        
        $data = array();
        if(!empty($purchases))
        {
            foreach ($purchases as $key=>$purchase)
            {

                $nestedData['id'] = $purchase->id;
                $nestedData['key'] = $key;
                $nestedData['date'] = date(config('date_format'), strtotime($purchase->created_at->toDateString()));
                $nestedData['po_no'] = $purchase->po_no;
                $nestedData['reference_no'] = $purchase->reference_no;

                // if($purchase->supplier_id) {
                //     $supplier = $purchase->supplier;
                // }
                // else {
                //     $supplier = new Supplier();
                // }
                // $nestedData['supplier'] = $supplier->name;
                if($purchase->status == 1){
                    $nestedData['purchase_status'] = '<div class="badge badge-success">'.__('db.Recieved').'</div>';
                    $purchase_status = __('db.Recieved');
                }
                elseif($purchase->status == 2){
                    $nestedData['purchase_status'] = '<div class="badge badge-success">'.__('db.Partial').'</div>';
                    $purchase_status = __('db.Partial');
                }
                elseif($purchase->status == 3){
                    $nestedData['purchase_status'] = '<div class="badge badge-danger">'.__('db.Pending').'</div>';
                    $purchase_status = __('db.Pending');
                }
                else{
                    $nestedData['purchase_status'] = '<div class="badge badge-danger">'.__('db.Ordered').'</div>';
                    $purchase_status = __('db.Ordered');
                }

                if($purchase->payment_status == 1)
                    $nestedData['payment_status'] = '<div class="badge badge-danger">'.__('db.Due').'</div>';
                else
                    $nestedData['payment_status'] = '<div class="badge badge-success">'.__('db.Paid').'</div>';

                $nestedData['grand_total'] = number_format($purchase->grand_total, config('decimal'));
                $returned_amount = DB::table('return_purchases')->where('purchase_id', $purchase->id)->sum('grand_total');
                $nestedData['returned_amount'] = number_format($returned_amount, config('decimal'));
                $nestedData['paid_amount'] = number_format($purchase->paid_amount, config('decimal'));
                $nestedData['due'] = number_format($purchase->grand_total- $returned_amount  - $purchase->paid_amount, config('decimal'));
                //fetching custom fields data
                foreach($field_names as $field_name) {
                    $nestedData[$field_name] = $purchase->$field_name;
                }
                $nestedData['options'] = '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.__("db.action").'
                              <span class="caret"></span>
                              <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                                <li>
                                    <button type="button" class="btn btn-link view"><i class="fa fa-eye"></i> '.__('db.View').'</button>
                                </li>';
                if(in_array("purchases-add", $request['all_permission']))
                    $nestedData['options'] .= '<li>
                        <a href="'.route('purchase.duplicate', $purchase->id).'" class="btn btn-link"><i class="fa fa-copy"></i> '.__('db.Duplicate').'</a>
                        </li>';
                if(in_array("purchases-edit", $request['all_permission']))
                    $nestedData['options'] .= '<li>
                        <a href="'.route('purchases.edit', $purchase->id).'" class="btn btn-link"><i class="dripicons-document-edit"></i> '.__('db.edit').'</a>
                        </li>';
                if(in_array("purchase-payment-index", $request['all_permission']))
                    $nestedData['options'] .=
                        '<li>
                            <button type="button" class="get-payment btn btn-link" data-id = "'.$purchase->id.'"><i class="fa fa-money"></i> '.__('db.View Payment').'</button>
                        </li>';
                if(in_array("purchase-payment-add", $request['all_permission']))
                    $nestedData['options'] .=
                        '<li>
                            <button type="button" class="add-payment btn btn-link" data-id = "'.$purchase->id.'" data-toggle="modal" data-target="#add-payment"><i class="fa fa-plus"></i> '.__('db.Add Payment').'</button>
                        </li>';
                if(in_array("purchases-delete", $request['all_permission']))
                    $nestedData['options'] .= \Form::open(["route" => ["purchases.destroy", $purchase->id], "method" => "DELETE"] ).'
                            <li>
                              <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> '.__("db.delete").'</button>
                            </li>'.\Form::close().'
                        </ul>
                    </div>';

                // data for purchase details by one click
                $user = User::find($purchase->user_id);
                $customer = Customer::find($purchase->user_id);
                $nestedData['supplier'] = $user?->name ?? $customer?->name ?? null;
                if($purchase->currency_id) {
                    $currency = Currency::select('code')->find($purchase->currency_id);
                    if($currency)
                        $currency_code = $currency->code;
                }
                else
                    $currency_code = 'N/A';
 
              $nestedData['purchase'] = [
    '[ "' . date(config('date_format'), strtotime($purchase->created_at->toDateString())) . '"',
    ' "' . $purchase->reference_no . '"',
    ' "' . $purchase_status . '"',
    ' "' . $purchase->id . '"',
    ' "' . optional($purchase->warehouse)->name . '"',
    ' "' . optional($purchase->warehouse)->phone . '"',
    ' "' . preg_replace('/\s+/S', " ", optional($purchase->warehouse)->address) . '"',
    ' "' . optional($purchase->supplier)->name . '"',
    ' "' . optional($purchase->supplier)->company_name . '"',
    ' "' . optional($purchase->supplier)->email . '"',
    ' "' . optional($purchase->supplier)->phone_number . '"',
    ' "' . preg_replace('/\s+/S', " ", optional($purchase->supplier)->address) . '"',
    ' "' . optional($purchase->supplier)->city . '"',
    ' "' . $purchase->total_tax . '"',
    ' "' . $purchase->total_discount . '"',
    ' "' . $purchase->total_cost . '"',
    ' "' . $purchase->order_tax . '"',
    ' "' . $purchase->order_tax_rate . '"',
    ' "' . $purchase->order_discount . '"',
    ' "' . $purchase->shipping_cost . '"',
    ' "' . $purchase->grand_total . '"',
    ' "' . $purchase->paid_amount . '"',
    ' "' . preg_replace('/\s+/S', " ", $purchase->note) . '"',
    ' "' . optional($customer)->name . '"',
    ' "' . optional($customer)->email . '"',
    ' "' . optional($customer)->phone_number . '"',
    ' "' . optional($customer)->company_name . '"',
    ' "' . optional($customer)->address . '"',
    ' "' . $purchase->document . '"',
    ' "' . $currency_code . '"',
    ' "' . $purchase->exchange_rate . '"',
    ' "' . $purchase->po_no . '"',
    ' "' . optional($purchase->warehouse)->company . '"',
    ' "' . $purchase->system_po_no . '"',
    ' "' . $purchase->signature . '"',
    ' "' . preg_replace('/\s+/S', " ", trim($purchase->comments)). '"',
    ' "' . optional($customer)->web . '"',
    ' "' . optional($purchase->warehouse)->web . '"',
    // ' "' . optional($purchase->wproduction)->name . '"',
    // ' "' . optional($purchase->wproduction)->phone . '"',
    // ' "' . optional($purchase->wproduction)->company . '"',
    // ' "' . optional($purchase->wproduction)->email . '"',
    ' "'.preg_replace('/\s+/S', " ", trim($purchase->ship_instruction)).'" ]',
];

                $data[] = $nestedData;
            }
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        echo json_encode($json_data);
    }

    public function productPurchaseData($id)
    {
         // echo "<pre>";
         // echo $id;
         // echo "</pre>";

         // return 1;
        try {

            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            $product_purchase = [];
            foreach ($lims_product_purchase_data as $key => $product_purchase_data) {
                $product = Product::find($product_purchase_data->product_id);
        $supplier = Supplier::find($product_purchase_data->supplier_id);
                $unit = Unit::find($product_purchase_data->purchase_unit_id);
                 if($product_purchase_data->variant_id) {
                    $lims_product_variant_data = ProductVariant::FindExactProduct($product->id, $product_purchase_data->variant_id)->select('item_code')->first();
                    $product->code = $lims_product_variant_data->item_code ?? '';
                }
                if($product_purchase_data->product_batch_id) {
                 $product_batch_data = ProductBatch::select('batch_no')->find($product_purchase_data->product_batch_id);
                $product_purchase[7][$key] = $product_batch_data->batch_no;
                $product_purchase[10][$key] = $product_batch_data->lot_no;
                }
                else
                    $product_purchase[7][$key] = 'N/A';
                    $product_purchase[10][$key] = 'N/A';
                $product_purchase[0][$key] = $product->name . ' [' . $product->code.']';
                $returned_imei_number_data = '';
                if($product_purchase_data->imei_number) {
                    $product_purchase[0][$key] .= '<br>IMEI or Serial Number: '. $product_purchase_data->imei_number;
                    $returned_imei_number_data = DB::table('return_purchases')
                    ->join('purchase_product_return', 'return_purchases.id', '=', 'purchase_product_return.return_id')
                    ->where([
                        ['return_purchases.purchase_id', $id],
                        ['purchase_product_return.product_id', $product_purchase_data->product_id]
                    ])->select('purchase_product_return.imei_number')
                    ->first();
                }
                $product_purchase[1][$key] = $product_purchase_data->qty;
                $product_purchase[2][$key] = $unit->unit_code;
                $product_purchase[3][$key] = $product_purchase_data->tax;
                $product_purchase[4][$key] = $product_purchase_data->tax_rate;
                $product_purchase[5][$key] = $product_purchase_data->ship_cost;
                $product_purchase[6][$key] = $product_purchase_data->total;
                if($returned_imei_number_data) {
                    $product_purchase[8][$key] = $product_purchase_data->return_qty.'<br>IMEI or Serial Number: '. $returned_imei_number_data->imei_number;
                }
                else
                    $product_purchase[8][$key] = $product_purchase_data->return_qty;
                    $product_purchase[9][$key] = $supplier;
                    $product_purchase[11][$key] = $product_purchase_data->moq;
                    
                   
                    // ✅ Check and add unique supplier based on ID
                    // $alreadyExists = false;
                    // foreach ($product_purchase[9] ?? [] as $existingSupplier) {
                    // if ($existingSupplier->id === $supplier->id) {
                    // $alreadyExists = true;
                    // break;
                    // }
                    // }

                    // if (!$alreadyExists) {
                    // $product_purchase[9][] = $supplier;
                    // }

            }
            return $product_purchase;
        }
        catch (Exception $e) {
            /*return response()->json('errors' => [$e->getMessage());*/
            //return response()->json(['errors' => [$e->getMessage()]], 422);
            return 'Something is wrong!';
        }

    }

    // public function productWithoutVariant()
    // {
    //     return Product::ActiveStandard()->select('id', 'name', 'code')
    //             ->whereNull('is_variant')->get();
    // }

    // public function productWithVariant()
    // {
    //     return Product::join('product_variants', 'products.id', 'product_variants.product_id')
    //         ->ActiveStandard()
    //         ->whereNotNull('is_variant')
    //         ->select('products.id', 'products.name', 'product_variants.item_code')
    //         ->orderBy('position')
    //         ->get();
    // }



    public function productWithoutVariant()
{
    return Product::ActiveStandard()
        ->whereNull('is_variant')
        ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
        ->select(
            'products.id',
            'products.name',
            'products.code',
            'products.price',
            'brands.title as title'
        )
        ->get();
}

  public function productWithVariant()
{
    return Product::join('product_variants', 'products.id', '=', 'product_variants.product_id')
        ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
        ->ActiveStandard()
        ->whereNotNull('is_variant')
        ->select(
            'products.id',
            'products.name',
            'product_variants.item_code',
            'product_variants.qty',
            'products.price',
            'brands.title as brand_title'
        )
        ->orderBy('position')
        ->get();
}

    public function newProductWithVariant()
    {
        return Product::ActiveStandard()
                ->whereNotNull('is_variant')
                ->whereNotNull('variant_data')
                ->select('id', 'name', 'variant_data')
                ->get();
    }

 //   public function limsProductSearch(Request $request)
 //    {
 //        $product_code = explode("|", $request['data']);
 //        $product_code[0] = rtrim($product_code[0], " ");
 //        $lims_product_data = Product::where([
 //                                ['code', $product_code[0]],
 //                                ['is_active', true]
 //                            ])
 //                            ->whereNull('is_variant')
 //                            ->first();
 //        if(!$lims_product_data) {
 //            $lims_product_data = Product::where([
 //                                ['name', $product_code[1]],
 //                                ['is_active', true]
 //                            ])
 //                            ->whereNotNull(['is_variant'])
 //                            ->first();
 //            $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
 //                ->where([
 //                    ['product_variants.item_code', $product_code[0]],
 //                    ['products.is_active', true]
 //                ])
 //                ->whereNotNull('is_variant')
 //                ->select('products.*', 'product_variants.item_code', 'product_variants.additional_cost')
 //                ->first();
 //            $lims_product_data->cost += $lims_product_data->additional_cost;
 //        }
 //        $product[] = $lims_product_data->name;
 //        if($lims_product_data->is_variant)
 //            $product[] = $lims_product_data->item_code;
 //        else
 //            $product[] = $lims_product_data->code;
 //        $product[] = $lims_product_data->cost;

 //        if ($lims_product_data->tax_id) {
 //            $lims_tax_data = Tax::find($lims_product_data->tax_id);
 //            $product[] = $lims_tax_data->rate;
 //            $product[] = $lims_tax_data->name;
 //        } else {
 //            $product[] = 0;
 //            $product[] = 'No Tax';
 //        }
 //        $product[] = $lims_product_data->tax_method;

 //        $units = Unit::where("base_unit", $lims_product_data->unit_id)
 //                    ->orWhere('id', $lims_product_data->unit_id)
 //                    ->get();
 //        $unit_name = array();
 //        $unit_operator = array();
 //        $unit_operation_value = array();
 //        foreach ($units as $unit) {
 //            if ($lims_product_data->purchase_unit_id == $unit->id) {
 //                array_unshift($unit_name, $unit->unit_name);
 //                array_unshift($unit_operator, $unit->operator);
 //                array_unshift($unit_operation_value, $unit->operation_value);
 //            } else {
 //                $unit_name[]  = $unit->unit_name;
 //                $unit_operator[] = $unit->operator;
 //                $unit_operation_value[] = $unit->operation_value;
 //            }
 //        }

 //        $product[] = implode(",", $unit_name) . ',';
 //        $product[] = implode(",", $unit_operator) . ',';
 //        $product[] = implode(",", $unit_operation_value) . ',';
 //        $product[] = $lims_product_data->id;
 //        $product[] = $lims_product_data->is_batch;
 //        $product[] = $lims_product_data->is_imei;
 //        // return dd($product);
 //        return $product;
 //    }

 // Attempt to read property "additional_cost" on null

public function limsProductSearch(Request $request)
{
    $product_code = explode("|", $request['data']);
    $product_code[0] = rtrim($product_code[0], " ");

    $lims_product_data = Product::where([
            ['code', $product_code[0]],
            ['is_active', true]
        ])
        ->whereNull('is_variant')
        ->first();

    if (!$lims_product_data) {
        $lims_product_data = Product::where([
                ['name', $product_code[1]],
                ['is_active', true]
            ])
            ->whereNotNull('is_variant')
            ->first();

        $lims_product_data = Product::join('product_variants', 'products.id', 'product_variants.product_id')
            ->where([
                ['product_variants.item_code', $product_code[0]],
                ['products.is_active', true]
            ])
            ->whereNotNull('is_variant')
            ->select('products.*', 'product_variants.item_code', 'product_variants.additional_cost')
            ->first();

        if ($lims_product_data) {
            $lims_product_data->cost += $lims_product_data->additional_cost;
        } else {
            return response()->json(['error' => 'Product not found'], 404);
        }
    }

    $product[] = $lims_product_data->name;
    $product[] = $lims_product_data->is_variant ? $lims_product_data->item_code : $lims_product_data->code;
    $product[] = $lims_product_data->cost;

    if ($lims_product_data->tax_id) {
        $lims_tax_data = Tax::find($lims_product_data->tax_id);
        $product[] = $lims_tax_data->rate;
        $product[] = $lims_tax_data->name;
    } else {
        $product[] = 0;
        $product[] = 'No Tax';
    }

    $product[] = $lims_product_data->tax_method;

    $units = Unit::where("base_unit", $lims_product_data->unit_id)
        ->orWhere('id', $lims_product_data->unit_id)
        ->get();

    $unit_name = $unit_operator = $unit_operation_value = [];

    foreach ($units as $unit) {
        if ($lims_product_data->purchase_unit_id == $unit->id) {
            array_unshift($unit_name, $unit->unit_name);
            array_unshift($unit_operator, $unit->operator);
            array_unshift($unit_operation_value, $unit->operation_value);
        } else {
            $unit_name[] = $unit->unit_name;
            $unit_operator[] = $unit->operator;
            $unit_operation_value[] = $unit->operation_value;
        }
    }

    $product[] = implode(",", $unit_name) . ',';
    $product[] = implode(",", $unit_operator) . ',';
    $product[] = implode(",", $unit_operation_value) . ',';
    $product[] = $lims_product_data->id;
    $product[] = $lims_product_data->is_batch;
    $product[] = $lims_product_data->is_imei;

    return $product;
}



    public function edit($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-edit')){
        $lims_supplier_list = Supplier::where('is_active', true)->get();
        $lims_warehouse_list = Warehouse::where('is_active', true)->get();
        $lims_production_list = Wproduction::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_customer_list = Customer::with('user')->get();
            $product_purchase_log = product_purchase_log::with('user','customer')->where("purchase_id",$id)->get();

            if(Auth::user()->role_id > 2) 
            {
                $lims_customer_list = Customer::with('user')
                ->where('user_id', Auth::user()->id)
                ->whereHas('user', function ($query) {
                $query->where('role_id', Auth::user()->role_id);
                })
                ->get();
            }

            $lims_product_list_without_variant = $this->productWithoutVariant();
            $lims_product_list_with_variant = $this->productWithVariant();
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            if($lims_purchase_data->exchange_rate)
                $currency_exchange_rate = $lims_purchase_data->exchange_rate;
            else
                $currency_exchange_rate = 1;
            $custom_fields = CustomField::where('belongs_to', 'purchase')->get();
            return view('backend.purchase.edit', compact('lims_warehouse_list','lims_production_list', 'lims_supplier_list','lims_customer_list', 'lims_product_list_without_variant', 'lims_product_list_with_variant', 'lims_tax_list', 'lims_purchase_data', 'lims_product_purchase_data', 'currency_exchange_rate', 'custom_fields','product_purchase_log'));
        // echo "<pre>";
        // print_r($product_purchase_log);
        // echo "</pre>";
        // return 1;
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));

    }

public function generatePdf($supplierId, $purchaseId)
{
    $supplier = Supplier::findOrFail($supplierId);
    $purchase = Purchase::with(['supplier', 'warehouse', 'wproduction'])->findOrFail($purchaseId);
    $customer = Customer::find($purchase->user_id);
    $currency = Currency::find($purchase->currency_id);

    $products = ProductPurchase::with('unit')
        ->where('purchase_id', $purchaseId)
        ->where('supplier_id', $supplierId)
        ->join('products', 'product_purchases.product_id', '=', 'products.id')
        ->select('products.name', 'products.code', 'product_purchases.*')
        ->get();

    // Calculate totals manually
    $total_shipping = 0;
    $total_tax = 0;
    $total_discount = 0;
    $sub_total = 0;

    foreach ($products as $product) {
        $row_total = ($product->net_unit_cost * $product->qty) + $product->tax; // adjust as needed
        $sub_total += $row_total;
        $total_tax += $product->tax;
        $total_discount += $product->discount;
        $total_shipping += $product->ship_cost; // from product_purchases table
    }

    $grand_total = $sub_total + $total_shipping + $total_tax - $total_discount;
    $paid = $purchase->paid_amount;
    $due = $grand_total - $paid;

    $data = [
        'po_no' => $purchase->po_no,
        'system_po_no' => $purchase->system_po_no,
        'date' => $purchase->created_at->format('d/m/Y'),
        'reference_no' => $purchase->reference_no,
        'purchase_status' => $this->getPurchaseStatusText($purchase->status),
        'currency_code' => $currency->code ?? 'N/A',

        'customer' => [
            'name' => $customer->name ?? '',
            'email' => $customer->email ?? '',
            'phone' => $customer->phone_number ?? '',
            'company' => $customer->company_name ?? '',
            'address' => $customer->address ?? '',
        ],

        'warehouse' => [
            'name' => $purchase->warehouse->name ?? '',
            'phone' => $purchase->warehouse->phone ?? '',
            'email' => $purchase->warehouse->email ?? '',
            'company' => $purchase->warehouse->company ?? '',
            'address' => $purchase->warehouse->address ?? '',
        ],

        'supplier' => [
            'name' => $supplier->name ?? '',
            'company' => $supplier->company_name ?? '',
            'phone' => $supplier->phone_number ?? '',
            'email' => $supplier->email ?? '',
            'address' => $supplier->address ?? '',
        ],

        'products' => $products,

        'totals' => [
            'sub_total' => $sub_total,
            'order_tax' => $total_tax,
            'order_discount' => $total_discount,
            'shipping_cost' => $total_shipping,
            'grand_total' => $grand_total,
            'paid_amount' => $paid,
            'due' => $due,
        ],

        'signature' => $purchase->signature ?? '',
        'comments' => $purchase->comments ?? '',
    ];

    $pdf = \PDF::loadView('pdf.purchase', $data)->setPaper('a4');
    return $pdf->stream("single_purchase-{$supplierId}.pdf");
    
}



public function ShippedCheck(Request $request)
{
    // Validate inputs
    $validated = $request->validate([
        'supplier_id' => 'required|exists:suppliers,id',
        'purchase_id' => 'required|exists:purchases,id',
        'tracking_number' => 'required|string|max:255',
        'product_ids' => 'required|array',
        'product_ids.*' => 'required|integer|exists:products,id',
    ]);

    $userId = auth()->id(); // Or set a default user ID if needed

    // Check existing shipped products for this purchase
    $existing = DB::table('PurchaseShippeds')
        ->where('purchase_id', $request->purchase_id)
        ->whereIn('product_id', $request->product_ids)
        ->pluck('product_id')
        ->toArray();

    $productIdsToInsert = array_diff($request->product_ids, $existing);

    if (empty($productIdsToInsert)) {
        return response()->json([
            'message' => 'All selected products are already marked as shipped.',
        ], 422);
    }

    $insertData = [];
    foreach ($productIdsToInsert as $productId) {
        $insertData[] = [
            'supplier_id'     => $request->supplier_id,
            'purchase_id'     => $request->purchase_id,
            'product_id'      => $productId,
            'user_id'         => $userId,
            'tracking_number' => $request->tracking_number,
            'created_at'      => now(),
            'updated_at'      => now(),
        ];
    }

    DB::table('PurchaseShippeds')->insert($insertData);

    return response()->json([
        'message' => 'Shipment records saved successfully.',
        'inserted_count' => count($insertData),
    ]);
}




public function checkShipmentDates(Request $request)
{
    $supplier = Supplier::find($request->supplier_id);

    $products = ProductPurchase::join('products', 'products.id', '=', 'product_purchases.product_id')
        ->where('product_purchases.purchase_id', $request->purchase_id)
        ->where('product_purchases.supplier_id', $request->supplier_id)
        ->select(
            'products.name as product_name',
            'product_purchases.product_id as pp_product_id',
            'product_purchases.ets_date',
            'product_purchases.eta_date',
            'product_purchases.etd_date'
        )
        ->get();

    $missingProducts = $products->filter(function ($product) {
        return empty($product->ets_date) || $product->ets_date === '1970-01-01 00:00:00'
            || empty($product->eta_date) || $product->eta_date === '1970-01-01 00:00:00'
            || empty($product->etd_date) || $product->etd_date === '1970-01-01 00:00:00';
    })->map(function ($product) {
        $missing = [];

        if (empty($product->ets_date) || $product->ets_date === '1970-01-01 00:00:00') $missing[] = 'ETS';
        if (empty($product->eta_date) || $product->eta_date === '1970-01-01 00:00:00') $missing[] = 'ETA';
        if (empty($product->etd_date) || $product->etd_date === '1970-01-01 00:00:00') $missing[] = 'ETD';

        return [
            'product_id' => $product->pp_product_id,
            'message' => $product->product_name . ' (Missing: ' . implode(', ', $missing) . ')'
        ];
    })->values();

    $allProductIds = $products->pluck('pp_product_id');

    if ($missingProducts->isNotEmpty()) {
        $supplierName = $supplier->name ?? 'Unknown Supplier';
        $productMessages = $missingProducts->pluck('message')->implode("\n");

        return response()->json([
            'message' => "Supplier: {$supplierName}\nProducts with missing dates:\n{$productMessages}",
            'product_ids' => $missingProducts->pluck('product_id')
        ], 422);
    }

    return response()->json([
        'message' => 'All shipment dates are present.',
        'product_ids' => $allProductIds
    ]);
}


public function shippedIndex(Request $request)
{   
    $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-index')) {
            if($request->input('warehouse_id'))
                $warehouse_id = $request->input('warehouse_id');
            else
                $warehouse_id = 0;

            if($request->input('purchase_status'))
                $purchase_status = $request->input('purchase_status');
            else
                $purchase_status = 0;

            if($request->input('payment_status'))
                $payment_status = $request->input('payment_status');
            else
                $payment_status = 0;

            if($request->input('starting_date')) {
                $starting_date = $request->input('starting_date');
                $ending_date = $request->input('ending_date');
            }
            else {
                $starting_date = date("Y-m-d", strtotime(date('Y-m-d', strtotime('-1 year', strtotime(date('Y-m-d') )))));
                $ending_date = date("Y-m-d");
            }
            $permissions = Role::findByName($role->name)->permissions;
            foreach ($permissions as $permission)
                $all_permission[] = $permission->name;
            if(empty($all_permission))
                $all_permission[] = 'dummy text';
           
           

           
            $couriers = Courier::all();
              return view('backend.purchase.shippedview', compact( 'starting_date', 'ending_date','all_permission','couriers'));
        }
        else
         return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));


}

public function getShippedData(Request $request)
{
    $columns = [
        1 => 'purchase_id',
        2 => 'supplier_id',
        3 => 'created_at',
    ];

    // Original query with relations
    $q = PurchaseShipped::with(['supplier', 'purchase', 'user', 'product.unit', 'Courier']);

    // Filters
    if ($request->starting_date) {
        $q->whereDate('created_at', '>=', $request->starting_date);
    }
    if ($request->ending_date) {
        $q->whereDate('created_at', '<=', $request->ending_date);
    }
    if ($request->shipping_status) {
        $q->where('shipment_status', $request->shipping_status);
    }

    // Searching
   if ($search = $request->search['value']) {
    $q->where(function ($q) use ($search) {
        $q->whereHas('supplier', function ($s) use ($search) {
            $s->where('name', 'like', "%$search%");
        })
        ->orWhereHas('purchase', function ($p) use ($search) {
            $p->where('reference_no', 'like', "%$search%")
              ->orWhere('po_no', 'like', "%$search%");
        })
        ->orWhere('tracking_number', 'like', "%$search%");
    });
}

    // Get all filtered records and group
    $grouped = $q->get()->groupBy(function ($item) {
        return $item->purchase_id . '-' . $item->supplier_id;
    });

    $totalFiltered = $grouped->count(); // filtered after search

    // Total (unfiltered)
    $baseQuery = PurchaseShipped::query();

    if ($request->starting_date) {
        $baseQuery->whereDate('created_at', '>=', $request->starting_date);
    }
    if ($request->ending_date) {
        $baseQuery->whereDate('created_at', '<=', $request->ending_date);
    }
    if ($request->shipping_status) {
        $baseQuery->where('shipment_status', $request->shipping_status);
    }

    $unfilteredGrouped = $baseQuery->get()->groupBy(function ($item) {
        return $item->purchase_id . '-' . $item->supplier_id;
    });

    $totalData = $unfilteredGrouped->count(); // full count (no search)

    // Pagination
    $limit = $request->length != -1 ? $request->length : $totalFiltered;
    $start = $request->start;
    $groupedPage = $grouped->slice($start, $limit);

    // Status badges
    $statusLabels = [
        0 => ['text' => 'Pending',    'class' => 'secondary'],
        1 => ['text' => 'Processing', 'class' => 'info'],
        2 => ['text' => 'Packed',     'class' => 'primary'],
        3 => ['text' => 'Dispatched', 'class' => 'warning'],
        4 => ['text' => 'In Transit', 'class' => 'dark'],
        5 => ['text' => 'Delivered',  'class' => 'success'],
        6 => ['text' => 'Failed',     'class' => 'danger'],
    ];

    $data = [];

    foreach ($groupedPage as $group) {
        $item = $group->first();

        // Total shipping cost
        $totalShipCost = ProductPurchase::where('purchase_id', $item->purchase_id)
            ->where('supplier_id', $item->supplier_id)
            ->sum('ship_cost');

        // Product rows
        $products = ProductPurchase::with('product.unit')
            ->where('purchase_id', $item->purchase_id)
            ->whereHas('purchase', function ($q) use ($item) {
                $q->where('supplier_id', $item->supplier_id);
            })
            ->get()
            ->map(function ($entry) {
                return [
                    'product_name' => $entry->product->name ?? '',
                    'qty'          => $entry->qty,
                    'unit_cost'    => number_format($entry->net_unit_cost, config('decimal')),
                    'ship_cost'    => number_format($entry->ship_cost, config('decimal')),
                ];
            });

        $status = $statusLabels[$item->ship_status] ?? ['text' => 'Unknown', 'class' => 'light'];

        $data[] = [
            'po_no'           => $item->purchase->po_no ?? '',
            'reference_no'    => $item->purchase->reference_no ?? '',
            'supplier_name'   => $item->supplier->name ?? '',
            'customer_name'   => $item->user->name ?? '',
            'date'            => $item->created_at->format('Y-m-d'),
            'ship_cost'       => number_format($totalShipCost, config('decimal')),
            'shipment_status' => '<span class="badge badge-' . $status['class'] . '">' . $status['text'] . '</span>',
            'courier'         => $item->Courier->name ?? '',
            'tracking_number' => $item->tracking_number,
            'options'         => '<button class="btn btn-sm btn-info view-details" data-purchase="'.$item->purchase_id.'" data-supplier="'.$item->supplier_id.'"><i class="fa fa-eye"></i></button> 
                <button class="btn btn-sm btn-warning edit-shipment" 
                    data-id="'.$item->id.'" 
                    data-status="'.$item->ship_status.'" 
                    data-courier="'.$item->courier_id.'" 
                    data-tracking="'.$item->tracking_number.'">
                    <i class="fa fa-edit"></i>
                </button>',
            'products_data'   => $products,
            'DT_RowAttr'      => [
                'data-purchaseid' => $item->purchase_id,
                'data-supplierid' => $item->supplier_id,
            ]
        ];
    }

    return response()->json([
        'draw'            => intval($request->draw),
        'recordsTotal'    => $totalData,
        'recordsFiltered' => $totalFiltered,
        'data'            => $data,
    ]);
}



public function childDetails(Request $request)
{
    $items = ProductPurchase::with(['product.unit'])
        ->where('purchase_id', $request->purchase_id)
        ->where('supplier_id', $request->supplier_id)
        ->get();

    $html = '<table class="table table-sm table-bordered">
                <thead><tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Net Cost</th>
                    <th>Shipping Cost</th>
                </tr></thead><tbody>';

    foreach ($items as $item) {
        $html .= '<tr>
                    <td>' . ($item->product->name ?? '-') . '</td>
                    <td>' . $item->qty . '</td>
                    <td>' . number_format($item->net_unit_cost, 2) . '</td>
                    <td>' . number_format($item->ship_cost, 2) . '</td>
                 </tr>';
    }

    $html .= '</tbody></table>';

    return $html;
}


 public function shippmnetUpdate(Request $request)
{
    $request->validate([
        'id' => 'required|exists:PurchaseShippeds,id',
        'shipment_status' => 'required|in:0,1,2,3,4,5,6',
        'carrier_id' => 'nullable|exists:couriers,id',
        'tracking_number' => 'nullable|string|max:255',
    ]);

    $shipment = PurchaseShipped::findOrFail($request->id);
    $shipment->ship_status = $request->shipment_status;
    $shipment->courier_id = $request->carrier_id;
    $shipment->tracking_number = $request->tracking_number;
    $shipment->save();

    return response()->json(['success' => true]);
}

public function shipmentModal(Request $request)
{
    $purchaseId = $request->purchase_id;
    $supplierId = $request->supplier_id;

    $purchase = Purchase::with(['warehouse', 'wproduction', 'supplier'])->find($purchaseId);
    $shipment = PurchaseShipped::with(['courier', 'customer'])->where('purchase_id', $purchaseId)
        ->where('supplier_id', $supplierId)
        ->first();

    $customer = Customer::find($purchase->user_id);
    $warehouse = $purchase->warehouse;

    $products = ProductPurchase::with(['product', 'purchase_unit']) // 👈 use purchase_unit here
        ->where('purchase_id', $purchaseId)
        ->where('supplier_id', $supplierId)
        ->get();

    ob_start();
    ?>

    <div class="row mb-2">
        <div class="col-md-6">
            <h6><strong>Customer Info:</strong></h6>
            <p>
                <strong>Name:</strong> <?= $customer->name ?? '-' ?><br>
                <strong>Email:</strong> <?= $customer->email ?? '-' ?><br>
                <strong>Phone:</strong> <?= $customer->phone_number ?? '-' ?><br>
                <strong>Company:</strong> <?= $customer->company_name ?? '-' ?><br>
                <strong>Address:</strong> <?= $customer->address ?? '-' ?><br>
                <strong>Website:</strong> <?= $customer->website ?? '-' ?>
            </p>
        </div>

        <div class="col-md-6">
            <h6><strong>Warehouse / Production Info:</strong></h6>
            <p>
                <strong>Name:</strong> <?= $warehouse->name ?? '-' ?><br>
                <strong>Company:</strong> <?= $warehouse->company_name ?? '-' ?><br>
                <strong>Phone:</strong> <?= $warehouse->phone_number ?? '-' ?><br>
                <strong>Address:</strong> <?= $warehouse->address ?? '-' ?><br>
                <strong>Website:</strong> <?= $warehouse->website ?? '-' ?>
            </p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <h6><strong>Courier Info:</strong></h6>
            <p>
                <strong>Courier:</strong> <?= $shipment->courier->name ?? '-' ?><br>
                <strong>Tracking No:</strong> <?= $shipment->tracking_number ?? '-' ?><br>
                <strong>Status:</strong> <?= $this->shipmentStatusText($shipment->ship_status ?? 0) ?>
            </p>
        </div>
        <div class="col-md-6">
            <h6><strong>Shipping Instructions:</strong></h6>
            <p><?= $shipment->instructions ?? '-' ?></p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-sm text-center">
            <thead class="bg-light">
                <tr>
                    <th>PRODUCT</th>
                    <th>QTY</th>
                    <th>NET COST</th>
                    <th>SHIP COST</th>
                    <th>SUBTOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalShipping = 0;
                $grandTotal = 0;
                foreach ($products as $item):
                    $net = $item->net_unit_cost * $item->qty;
                    $ship = $item->ship_cost;
                    $total = $net + $ship;
                    $totalShipping += $ship;
                    $grandTotal += $total;
                ?>
                <tr>
                    <td><?= $item->product->name ?? '-' ?></td>
                    <td><?= $item->qty . ' ' . ($item->purchase_unit->unit_name ?? '-') ?></td>
                    <td><?= number_format($item->net_unit_cost, 2) ?></td>
                    <td><?= number_format($item->ship_cost, 2) ?></td>
                    <td><?= number_format($net + $ship, 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end">
        <div class="col-md-4">
            <table class="table table-bordered">
                <tr><th>Total Shipping</th><td><?= number_format($totalShipping, 2) ?></td></tr>
                <tr><th>Grand Total</th><td><?= number_format($grandTotal, 2) ?></td></tr>
                <tr><th>Paid</th><td><?= number_format($shipment->paid_amount ?? 0, 2) ?></td></tr>
                <tr><th>Due</th><td><?= number_format($grandTotal - ($shipment->paid_amount ?? 0), 2) ?></td></tr>
            </table>
        </div>
    </div>

    <p><strong>Special Instructions:</strong> <?= $shipment->special_note ?? 'None' ?></p>
    <p class="text-muted">© <span id="footer_note">Authorized Signature</span></p>

    <?php
    return ob_get_clean();
}



private function getPurchaseStatusText($status)
{
    switch ($status) {
        case 1: return 'Received';
        case 2: return 'Partial';
        case 3: return 'Pending';
        default: return 'Ordered';
    }
}
   
    public function update(UpdatePurchaseRequest $request, $id)
    {    
        $lims_purchase_data = Purchase::find($id);
        $data = $request->except('document');
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $this->fileDelete(public_path('documents/purchase/'), $lims_purchase_data->document);

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/purchase'), $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/purchase'), $documentName);
            }
            $data['document'] = $documentName;
        }
        // return dd($data);
        DB::beginTransaction();

        try {
            $balance = (float)$data['grand_total'] - (float)$data['paid_amount'];
            if ($balance < 0 || $balance > 0) {
                $data['payment_status'] = 1;
            } else {
                $data['payment_status'] = 2;
            }
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();

            $data['created_at'] = date("Y-m-d", strtotime(str_replace("/", "-", $data['created_at']))) . ' '. date("H:i:s");
            $product_id = $data['product_id'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $recieved = $data['recieved'];
            $batch_no = $data['batch_no'];
            $lot_no = $request->input('lot_no', []);
            $po_no = $data['po_no'];
            $expired_date = $data['expired_date'];
            $purchase_unit = $data['purchase_unit'];
            $net_unit_cost = $data['net_unit_cost'];
            $discount = $data['discount'];
            $tax_rate = $data['tax_rate'];
            $supplier_ids = $data['supplier_name'];
            $ets_date = $data['ets_date'];
            $eta_date = $data['eta_date'];
            $etd_date = $data['etd_date'];
            $moq = $data['moq'];
            $ship_cost = $data['ship_cost'];
            $data['user_id'] = $request->customer_id;
            $tax = $data['tax'];
            $total = $data['subtotal'];
            $imei_number = $new_imei_number = $data['imei_number'];
            $product_purchase = [];

            foreach ($lims_product_purchase_data as $product_purchase_data) {

                $old_recieved_value = $product_purchase_data->recieved;
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);

                if ($lims_purchase_unit_data->operator == '*') {
                    $old_recieved_value = $old_recieved_value * $lims_purchase_unit_data->operation_value;
                } else {
                    $old_recieved_value = $old_recieved_value / $lims_purchase_unit_data->operation_value;
                }
                $lims_product_data = Product::find($product_purchase_data->product_id);
                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $lims_product_data->id],
                        ['variant_id', $product_purchase_data->variant_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id]
                    ])->first();
                    $lims_product_variant_data->qty -= $old_recieved_value;
                    $lims_product_variant_data->save();
                }
                elseif($product_purchase_data->product_batch_id) {
                    $product_batch_data = ProductBatch::find($product_purchase_data->product_batch_id);
                    $product_batch_data->qty -= $old_recieved_value;
                    $product_batch_data->save();

                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['product_batch_id', $product_purchase_data->product_batch_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();
                }
                if($product_purchase_data->imei_number) {
                    $position = array_search($lims_product_data->id, $product_id);
                    if($imei_number[$position]) {
                        $prev_imei_numbers = explode(",", $product_purchase_data->imei_number);
                        $new_imei_numbers = explode(",", $imei_number[$position]);
                        $temp_imeis = explode(',', $lims_product_warehouse_data->imei_number);
                        foreach ($prev_imei_numbers as $prev_imei_number) {
                            // $pos = array_search($prev_imei_number, $new_imei_numbers);
                            // if ($pos !== false) {
                            //     unset($new_imei_numbers[$pos]);
                            // }
                            $pos = array_search($prev_imei_number, $temp_imeis);
                            if ($pos !== false) {
                                unset($temp_imeis[$pos]);
                            }
                        }

                        // return dd($prev_imei_number, $temp_imeis);
                        $lims_product_warehouse_data->imei_number = !empty($temp_imeis) ? implode(',', $temp_imeis) : null;

                        $new_imei_number[$position] = implode(",", $new_imei_numbers);
                    }
                }
                $lims_product_data->qty -= $old_recieved_value;
                if($lims_product_warehouse_data) {
                    $lims_product_warehouse_data->qty -= $old_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                $lims_product_data->save();
                $product_purchase_data->delete();
            }

            foreach ($product_id as $key => $pro_id) {
                $lims_purchase_unit_data = Unit::where('unit_name', $purchase_unit[$key])->first();
                if ($lims_purchase_unit_data->operator == '*') {
                    $new_recieved_value = $recieved[$key] * $lims_purchase_unit_data->operation_value;
                } else {
                    $new_recieved_value = $recieved[$key] / $lims_purchase_unit_data->operation_value;
                }

                $lims_product_data = Product::find($pro_id);
                $price = null;
                //dealing with product barch
                if($batch_no[$key]) {
                    $product_batch_data = ProductBatch::where([
                                            ['product_id', $lims_product_data->id],
                                            ['lot_no', $lot_no[$key]],
                                            ['batch_no', $batch_no[$key]]
                                        ])->first();
                    if($product_batch_data) {
                        $product_batch_data->qty += $new_recieved_value;
                        $product_batch_data->expired_date = $expired_date[$key];
                        $product_batch_data->save();
                    }
                    else {
                        $product_batch_data = ProductBatch::create([
                                                'product_id' => $lims_product_data->id,
                                                'batch_no' => $batch_no[$key],
                                            'lot_no' => $lot_no[$i] ?? null,
                                                'expired_date' => $expired_date[$key],
                                                'qty' => $new_recieved_value
                                            ]);
                    }
                    $product_purchase['product_batch_id'] = $product_batch_data->id;
                }
                else
                    $product_purchase['product_batch_id'] = null;

                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($pro_id, $product_code[$key])->first();
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $pro_id],
                        ['variant_id', $lims_product_variant_data->variant_id],
                        ['warehouse_id', $data['warehouse_id']]
                    ])->first();
                    $product_purchase['variant_id'] = $lims_product_variant_data->variant_id;
                    //add quantity to product variant table
                    $lims_product_variant_data->qty += $new_recieved_value;
                    $lims_product_variant_data->save();
                }
                else {
                    $product_purchase['variant_id'] = null;
                    if($product_purchase['product_batch_id']) {
                        //checking for price
                        $lims_product_warehouse_data = Product_Warehouse::where([
                                                        ['product_id', $pro_id],
                                                        ['warehouse_id', $data['warehouse_id'] ],
                                                    ])
                                                    ->whereNotNull('price')
                                                    ->select('price')
                                                    ->first();
                        if($lims_product_warehouse_data)
                            $price = $lims_product_warehouse_data->price;

                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['product_batch_id', $product_purchase['product_batch_id'] ],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                    else {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                }

                $lims_product_data->qty += $new_recieved_value;
                if($lims_product_warehouse_data){
                    $lims_product_warehouse_data->qty += $new_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                else {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $pro_id;
                    $lims_product_warehouse_data->product_batch_id = $product_purchase['product_batch_id'];
                    if($lims_product_data->is_variant)
                        $lims_product_warehouse_data->variant_id = $lims_product_variant_data->variant_id;
                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    $lims_product_warehouse_data->qty = $new_recieved_value;
                    if($price)
                        $lims_product_warehouse_data->price = $price;
                }
                //dealing with imei numbers
                if($new_imei_number[$key]) {
                    // prevent duplication
                    $imeis = explode(',', $new_imei_number[$key]);
                    $imeis = array_map('trim', $imeis);
                    if (count($imeis) !== count(array_unique($imeis))) {
                        DB::rollBack();
                        return redirect()->route('purchases.edit', $id)->with('not_permitted', __('db.Duplicate IMEI not allowed!'));
                    }
                    foreach ($imeis as $imei) {
                        if ($this->isImeiExist($imei, $product_purchase_data->product_id)) {
                            DB::rollBack();
                            return redirect()->route('purchases.edit', $id)->with('not_permitted', __('db.Duplicate IMEI not allowed!'));
                        }
                    }

                    if(isset($lims_product_warehouse_data->imei_number)) {
                        $lims_product_warehouse_data->imei_number .= ',' . $new_imei_number[$key];
                    }
                    else {
                        $lims_product_warehouse_data->imei_number = $new_imei_number[$key];
                    }
                }

                $lims_product_data->save();
                $lims_product_warehouse_data->save();

                $product_purchase['purchase_id'] = $id;
                $product_purchase['product_id'] = $pro_id;
                $product_purchase['qty'] = $qty[$key];
                $product_purchase['recieved'] = $recieved[$key];
                $product_purchase['purchase_unit_id'] = $lims_purchase_unit_data->id;
                $product_purchase['net_unit_cost'] = $net_unit_cost[$key];
                $product_purchase['discount'] = $discount[$key];
                $product_purchase['tax_rate'] = $tax_rate[$key];
                $product_purchase['tax'] = $tax[$key];
                $product_purchase['total'] = $total[$key];
                $product_purchase['imei_number'] = $imei_number[$key] ?? null;
                $product_purchase['moq'] = $moq[$key] ?? null;
                $product_purchase['ship_cost'] = $ship_cost[$key] ?? null;
            $product_purchase['supplier_id'] = $supplier_ids[$key] ?? null;
            $product_purchase['ets_date'] = date('Y-m-d', strtotime($ets_date[$key]));
            $product_purchase['eta_date'] = date('Y-m-d', strtotime($eta_date[$key]));
            $product_purchase['etd_date'] = date('Y-m-d', strtotime($etd_date[$key]));

                ProductPurchase::create($product_purchase);
            }

            DB::commit();
            
//==============Log data ==============/// 
            $original = $lims_purchase_data->getOriginal();
$updatedFields = [];

foreach ($data as $key => $value) {
    if (!array_key_exists($key, $original)) continue;

    if ((string)$original[$key] !== (string)$value) {
        switch ($key) {
            case 'warehouse_id':
                $warehouse = Warehouse::find($value);
                $updatedFields['warehouse'] = $warehouse ? $warehouse->name : $value;
                break;

            case 'customer_id':
                $customer = Customer::find($value);
                $updatedFields['customer'] = $customer ? $customer->name : $value;
                break;

            case 'currency_id':
                $currency = Currency::find($value);
                $updatedFields['currency'] = $currency ? $currency->code : $value;
                break;

            case 'supplier_name': // multiple comma-separated IDs
                $supplierIds = is_array($value) ? $value : explode(',', $value);
                $suppliers = Supplier::whereIn('id', $supplierIds)->pluck('name')->toArray();
                $updatedFields['suppliers'] = implode(', ', $suppliers);
                break;

            default:
                $updatedFields[$key] = $value;
        }
    }
}

                if (!empty($updatedFields)) {
                product_purchase_log::create([
                'purchase_id' => $lims_purchase_data->id,
                'user_id' => Auth::id(),
                'notes' => json_encode($updatedFields),
                ]);
                }
//==============Log data ==============/// 
             $lims_purchase_data->update($data);

            //inserting data for custom fields
            $custom_field_data = [];
            $custom_fields = CustomField::where('belongs_to', 'purchase')->select('name', 'type')->get();
            foreach ($custom_fields as $type => $custom_field) {
                $field_name = str_replace(' ', '_', strtolower($custom_field->name));
                if(isset($data[$field_name])) {
                    if($custom_field->type == 'checkbox' || $custom_field->type == 'multi_select')
                        $custom_field_data[$field_name] = implode(",", $data[$field_name]);
                    else
                        $custom_field_data[$field_name] = $data[$field_name];
                }
            }
            if(count($custom_field_data))
                DB::table('purchases')->where('id', $lims_purchase_data->id)->update($custom_field_data);
            return redirect('purchases')->with('message', __('db.Purchase updated successfully'));
        } catch(\Exception $e) {
            DB::rollBack();
            return redirect()->route('purchases.edit', $id)->with('not_permitted', $e->getMessage());
        }
        
    }

    public function duplicate($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-add')){
            $lims_supplier_list = Supplier::where('is_active', true)->get();
            $lims_warehouse_list = Warehouse::where('is_active', true)->get();
            $lims_tax_list = Tax::where('is_active', true)->get();
            $lims_product_list_without_variant = $this->productWithoutVariant();
            $lims_product_list_with_variant = $this->productWithVariant();
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            if($lims_purchase_data->exchange_rate)
                $currency_exchange_rate = $lims_purchase_data->exchange_rate;
            else
                $currency_exchange_rate = 1;
            $custom_fields = CustomField::where('belongs_to', 'purchase')->get();
            return view('backend.purchase.duplicate', compact('lims_warehouse_list', 'lims_supplier_list', 'lims_product_list_without_variant', 'lims_product_list_with_variant', 'lims_tax_list', 'lims_purchase_data', 'lims_product_purchase_data', 'currency_exchange_rate', 'custom_fields'));
        }
        else
            return redirect()->back()->with('not_permitted', __('db.Sorry! You are not allowed to access this module'));

    }

    public function addPayment(Request $request)
    {
        $data = $request->all();
        $lims_purchase_data = Purchase::find($data['purchase_id']);
        $lims_purchase_data->paid_amount += $data['amount'];
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if($balance > 0 || $balance < 0)
            $lims_purchase_data->payment_status = 1;
        elseif ($balance == 0)
            $lims_purchase_data->payment_status = 2;
        $lims_purchase_data->save();

        if($data['paid_by_id'] == 1)
            $paying_method = 'Cash';
        elseif ($data['paid_by_id'] == 2)
            $paying_method = 'Gift Card';
        elseif ($data['paid_by_id'] == 3)
            $paying_method = 'Credit Card';
        else
            $paying_method = 'Cheque';

        $lims_payment_data = new Payment();
        $lims_payment_data->user_id = Auth::id();
        $lims_payment_data->purchase_id = $lims_purchase_data->id;
        $lims_payment_data->account_id = $data['account_id'];
        $lims_payment_data->payment_reference = 'ppr-' . date("Ymd") . '-'. date("his");
        $lims_payment_data->amount = $data['amount'];
        $lims_payment_data->change = $data['paying_amount'] - $data['amount'];
        $lims_payment_data->paying_method = $paying_method;
        $lims_payment_data->payment_note = $data['payment_note'];
        $lims_payment_data->save();

        $lims_payment_data = Payment::latest()->first();
        $data['payment_id'] = $lims_payment_data->id;
        $lims_pos_setting_data = PosSetting::latest()->first();
        if($paying_method == 'Credit Card' && $lims_pos_setting_data->stripe_secret_key){
            Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            $token = $data['stripeToken'];
            $amount = $data['amount'];

            // Charge the Customer
            $charge = \Stripe\Charge::create([
                'amount' => $amount * 100,
                'currency' => 'usd',
                'source' => $token,
            ]);

            $data['charge_id'] = $charge->id;
            PaymentWithCreditCard::create($data);
        }
        elseif ($paying_method == 'Cheque') {
            PaymentWithCheque::create($data);
        }
        return redirect('purchases')->with('message', __('db.Payment created successfully'));
    }

    public function getPayment($id)
    {
        $lims_payment_list = Payment::where('purchase_id', $id)->get();
        $date = [];
        $payment_reference = [];
        $paid_amount = [];
        $paying_method = [];
        $payment_id = [];
        $payment_note = [];
        $cheque_no = [];
        $change = [];
        $paying_amount = [];
        $account_name = [];
        $account_id = [];
        foreach ($lims_payment_list as $payment) {
            $date[] = date(config('date_format'), strtotime($payment->created_at->toDateString())) . ' '. $payment->created_at->toTimeString();
            $payment_reference[] = $payment->payment_reference;
            $paid_amount[] = $payment->amount;
            $change[] = $payment->change;
            $paying_method[] = $payment->paying_method;
            $paying_amount[] = $payment->amount + $payment->change;
            if($payment->paying_method == 'Cheque'){
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id',$payment->id)->first();
                $cheque_no[] = $lims_payment_cheque_data->cheque_no;
            }
            else{
                $cheque_no[] = null;
            }
            $payment_id[] = $payment->id;
            $payment_note[] = $payment->payment_note;
            $lims_account_data = Account::find($payment->account_id);
            if($lims_account_data) {
                $account_name[] = $lims_account_data->name;
                $account_id[] = $lims_account_data->id;
            }
            else {
                $account_name[] = 'N/A';
                $account_id[] = 0;
            }
        }
        $payments[] = $date;
        $payments[] = $payment_reference;
        $payments[] = $paid_amount;
        $payments[] = $paying_method;
        $payments[] = $payment_id;
        $payments[] = $payment_note;
        $payments[] = $cheque_no;
        $payments[] = $change;
        $payments[] = $paying_amount;
        $payments[] = $account_name;
        $payments[] = $account_id;

        return $payments;
    }

    public function updatePayment(Request $request)
    {
        $data = $request->all();
        $lims_payment_data = Payment::find($data['payment_id']);
        $lims_purchase_data = Purchase::find($lims_payment_data->purchase_id);
        //updating purchase table
        $amount_dif = $lims_payment_data->amount - $data['edit_amount'];
        $lims_purchase_data->paid_amount = $lims_purchase_data->paid_amount - $amount_dif;
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if($balance > 0 || $balance < 0)
            $lims_purchase_data->payment_status = 1;
        elseif ($balance == 0)
            $lims_purchase_data->payment_status = 2;
        $lims_purchase_data->save();

        //updating payment data
        $lims_payment_data->account_id = $data['account_id'];
        $lims_payment_data->amount = $data['edit_amount'];
        $lims_payment_data->change = $data['edit_paying_amount'] - $data['edit_amount'];
        $lims_payment_data->payment_note = $data['edit_payment_note'];
        $lims_pos_setting_data = PosSetting::latest()->first();
        if($data['edit_paid_by_id'] == 1)
            $lims_payment_data->paying_method = 'Cash';
        elseif ($data['edit_paid_by_id'] == 2)
            $lims_payment_data->paying_method = 'Gift Card';
        elseif ($data['edit_paid_by_id'] == 3 && $lims_pos_setting_data->stripe_secret_key) {
            \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            $token = $data['stripeToken'];
            $amount = $data['edit_amount'];
            if($lims_payment_data->paying_method == 'Credit Card'){
                $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $lims_payment_data->id)->first();

                \Stripe\Refund::create(array(
                  "charge" => $lims_payment_with_credit_card_data->charge_id,
                ));

                $charge = \Stripe\Charge::create([
                    'amount' => $amount * 100,
                    'currency' => 'usd',
                    'source' => $token,
                ]);

                $lims_payment_with_credit_card_data->charge_id = $charge->id;
                $lims_payment_with_credit_card_data->save();
            }
            elseif($lims_pos_setting_data->stripe_secret_key) {
                // Charge the Customer
                $charge = \Stripe\Charge::create([
                    'amount' => $amount * 100,
                    'currency' => 'usd',
                    'source' => $token,
                ]);

                $data['charge_id'] = $charge->id;
                PaymentWithCreditCard::create($data);
            }
            $lims_payment_data->paying_method = 'Credit Card';
        }
        else{
            if($lims_payment_data->paying_method == 'Cheque'){
                $lims_payment_data->paying_method = 'Cheque';
                $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $data['payment_id'])->first();
                $lims_payment_cheque_data->cheque_no = $data['edit_cheque_no'];
                $lims_payment_cheque_data->save();
            }
            else{
                $lims_payment_data->paying_method = 'Cheque';
                $data['cheque_no'] = $data['edit_cheque_no'];
                PaymentWithCheque::create($data);
            }
        }
        $lims_payment_data->save();
        return redirect('purchases')->with('message', __('db.Payment updated successfully'));
    }

    public function deletePayment(Request $request)
    {
        $lims_payment_data = Payment::find($request['id']);
        $lims_purchase_data = Purchase::where('id', $lims_payment_data->purchase_id)->first();
        $lims_purchase_data->paid_amount -= $lims_payment_data->amount;
        $balance = $lims_purchase_data->grand_total - $lims_purchase_data->paid_amount;
        if($balance > 0 || $balance < 0)
            $lims_purchase_data->payment_status = 1;
        elseif ($balance == 0)
            $lims_purchase_data->payment_status = 2;
        $lims_purchase_data->save();
        $lims_pos_setting_data = PosSetting::latest()->first();

        if($lims_payment_data->paying_method == 'Credit Card' && $lims_pos_setting_data->stripe_secret_key) {
            $lims_payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $request['id'])->first();
            \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
            \Stripe\Refund::create(array(
              "charge" => $lims_payment_with_credit_card_data->charge_id,
            ));

            $lims_payment_with_credit_card_data->delete();
        }
        elseif ($lims_payment_data->paying_method == 'Cheque') {
            $lims_payment_cheque_data = PaymentWithCheque::where('payment_id', $request['id'])->first();
            $lims_payment_cheque_data->delete();
        }
        $lims_payment_data->delete();
        return redirect('purchases')->with('not_permitted', __('db.Payment deleted successfully'));
    }

    private function purchaseHasSale($lims_product_purchase_data)
    {
        $has_sale = false;
        foreach ($lims_product_purchase_data as $product_purchase_data) {
            $product_sale = Product_Sale::where('product_id', $product_purchase_data->product_id)
                ->select('updated_at')
                ->latest('updated_at')
                ->first();

            if (!$product_sale) {
                continue;
            }

            if ($product_sale->updated_at->gt($product_purchase_data->updated_at)) {
                $has_sale = true;
            }
        }

        return $has_sale;
    }

    public function deleteBySelection(Request $request)
    {
        $purchase_id = $request['purchaseIdArray'];
        try {
            DB::beginTransaction();
            foreach ($purchase_id as $id) {
                $lims_purchase_data = Purchase::find($id);
                $this->fileDelete(public_path('documents/purchase/'), $lims_purchase_data->document);

                $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();

                if ($this->purchaseHasSale($lims_product_purchase_data)) {
                    DB::rollBack();
                    return redirect('purchases')->with('not_permitted', __('db.Can not delete purchase has sale!'));
                }

                $lims_payment_data = Payment::where('purchase_id', $id)->get();
                foreach ($lims_product_purchase_data as $product_purchase_data) {
                    $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);
                    if ($lims_purchase_unit_data->operator == '*')
                        $recieved_qty = $product_purchase_data->recieved * $lims_purchase_unit_data->operation_value;
                    else
                        $recieved_qty = $product_purchase_data->recieved / $lims_purchase_unit_data->operation_value;

                    $lims_product_data = Product::find($product_purchase_data->product_id);
                    if($product_purchase_data->variant_id) {
                        $lims_product_variant_data = ProductVariant::select('id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_purchase_data->product_id, $product_purchase_data->variant_id, $lims_purchase_data->warehouse_id)
                            ->first();
                        $lims_product_variant_data->qty -= $recieved_qty;
                        $lims_product_variant_data->save();
                    }
                    elseif($product_purchase_data->product_batch_id) {
                        $lims_product_batch_data = ProductBatch::find($product_purchase_data->product_batch_id);
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_batch_id', $product_purchase_data->product_batch_id],
                            ['warehouse_id', $lims_purchase_data->warehouse_id]
                        ])->first();

                        $lims_product_batch_data->qty -= $recieved_qty;
                        $lims_product_batch_data->save();
                    }
                    else {
                        $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_purchase_data->product_id, $lims_purchase_data->warehouse_id)
                            ->first();
                    }

                    $lims_product_data->qty -= $recieved_qty;
                    $lims_product_warehouse_data->qty -= $recieved_qty;

                    $lims_product_warehouse_data->save();
                    $lims_product_data->save();
                    $product_purchase_data->delete();
                }
                $lims_pos_setting_data = PosSetting::latest()->first();
                foreach ($lims_payment_data as $payment_data) {
                    if($payment_data->paying_method == "Cheque"){
                        $payment_with_cheque_data = PaymentWithCheque::where('payment_id', $payment_data->id)->first();
                        $payment_with_cheque_data->delete();
                    }
                    elseif($payment_data->paying_method == "Credit Card" && $lims_pos_setting_data->stripe_secret_key) {
                        $payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $payment_data->id)->first();
                        \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
                        \Stripe\Refund::create(array(
                        "charge" => $payment_with_credit_card_data->charge_id,
                        ));

                        $payment_with_credit_card_data->delete();
                    }
                    $payment_data->delete();
                }

                $lims_purchase_data->delete();
            }

            DB::commit();
            return 'Purchase deleted successfully!';
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect('purchases')->with('not_permitted', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $role = Role::find(Auth::user()->role_id);
        if($role->hasPermissionTo('purchases-delete')){
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();
            
            if ($this->purchaseHasSale($lims_product_purchase_data)) {
                return redirect('purchases')->with('not_permitted', __('db.Can not delete, purchase has sale!'));
            }

            $lims_payment_data = Payment::where('purchase_id', $id)->get();
            foreach ($lims_product_purchase_data as $product_purchase_data) {
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);
                if ($lims_purchase_unit_data->operator == '*')
                    $recieved_qty = $product_purchase_data->recieved * $lims_purchase_unit_data->operation_value;
                else
                    $recieved_qty = $product_purchase_data->recieved / $lims_purchase_unit_data->operation_value;

                $lims_product_data = Product::find($product_purchase_data->product_id);
                if($product_purchase_data->variant_id) {
                    $lims_product_variant_data = ProductVariant::select('id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithVariant($product_purchase_data->product_id, $product_purchase_data->variant_id, $lims_purchase_data->warehouse_id)
                        ->first();
                    $lims_product_variant_data->qty -= $recieved_qty;
                    $lims_product_variant_data->save();
                }
                elseif($product_purchase_data->product_batch_id) {
                    $lims_product_batch_data = ProductBatch::find($product_purchase_data->product_batch_id);
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_batch_id', $product_purchase_data->product_batch_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id]
                    ])->first();

                    $lims_product_batch_data->qty -= $recieved_qty;
                    $lims_product_batch_data->save();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::FindProductWithoutVariant($product_purchase_data->product_id, $lims_purchase_data->warehouse_id)
                        ->first();
                }
                //deduct imei number if available
                if($product_purchase_data->imei_number) {
                    $imei_numbers = explode(",", $product_purchase_data->imei_number);
                    $all_imei_numbers = explode(",", $lims_product_warehouse_data->imei_number);
                    foreach ($imei_numbers as $number) {
                        if (($j = array_search($number, $all_imei_numbers)) !== false) {
                            unset($all_imei_numbers[$j]);
                        }
                    }
                    $lims_product_warehouse_data->imei_number = implode(",", $all_imei_numbers);
                }

                $lims_product_data->qty -= $recieved_qty;
                $lims_product_warehouse_data->qty -= $recieved_qty;

                $lims_product_warehouse_data->save();
                $lims_product_data->save();
                $product_purchase_data->delete();
            }
            $lims_pos_setting_data = PosSetting::latest()->first();
            foreach ($lims_payment_data as $payment_data) {
                if($payment_data->paying_method == "Cheque"){
                    $payment_with_cheque_data = PaymentWithCheque::where('payment_id', $payment_data->id)->first();
                    $payment_with_cheque_data->delete();
                }
                elseif($payment_data->paying_method == "Credit Card" && $lims_pos_setting_data->stripe_secret_key) {
                    $payment_with_credit_card_data = PaymentWithCreditCard::where('payment_id', $payment_data->id)->first();
                    \Stripe\Stripe::setApiKey($lims_pos_setting_data->stripe_secret_key);
                    \Stripe\Refund::create(array(
                      "charge" => $payment_with_credit_card_data->charge_id,
                    ));

                    $payment_with_credit_card_data->delete();
                }
                $payment_data->delete();
            }

            $lims_purchase_data->delete();
            $this->fileDelete(public_path('documents/purchase/'), $lims_purchase_data->document);

            return redirect('purchases')->with('not_permitted', __('db.Purchase deleted successfully'));
        }

    }

    public function updateFromClient(Request $request, $id)
    {
        $data = $request->except('document');
        $document = $request->document;
        if ($document) {
            $v = Validator::make(
                [
                    'extension' => strtolower($request->document->getClientOriginalExtension()),
                ],
                [
                    'extension' => 'in:jpg,jpeg,png,gif,pdf,csv,docx,xlsx,txt',
                ]
            );
            if ($v->fails())
                return redirect()->back()->withErrors($v->errors());

            $ext = pathinfo($document->getClientOriginalName(), PATHINFO_EXTENSION);
            $documentName = date("Ymdhis");
            if(!config('database.connections.saleprosaas_landlord')) {
                $documentName = $documentName . '.' . $ext;
                $document->move(public_path('documents/purchase'), $documentName);
            }
            else {
                $documentName = $this->getTenantId() . '_' . $documentName . '.' . $ext;
                $document->move(public_path('documents/purchase'), $documentName);
            }
            $data['document'] = $documentName;
        }
        //return dd($data);
        DB::beginTransaction();
        try {
            $balance = $data['grand_total'] - $data['paid_amount'];
            if ($balance < 0 || $balance > 0) {
                $data['payment_status'] = 1;
            } else {
                $data['payment_status'] = 2;
            }
            $lims_purchase_data = Purchase::find($id);
            $lims_product_purchase_data = ProductPurchase::where('purchase_id', $id)->get();

            $data['created_at'] = date("Y-m-d", strtotime(str_replace("/", "-", $data['created_at'])));
            $product_id = $data['product_id'];
            $product_code = $data['product_code'];
            $qty = $data['qty'];
            $recieved = $data['recieved'];
            $batch_no = $data['batch_no'];
            $expired_date = $data['expired_date'];
            $purchase_unit = $data['purchase_unit'];
            $net_unit_cost = $data['net_unit_cost'];
            $discount = $data['discount'];
            $tax_rate = $data['tax_rate'];
            $tax = $data['tax'];
            $total = $data['subtotal'];
            $imei_number = $new_imei_number = $data['imei_number'];
            $product_purchase = [];
            $lims_product_warehouse_data = null;

            foreach ($lims_product_purchase_data as $product_purchase_data) {

                $old_recieved_value = $product_purchase_data->recieved;
                $lims_purchase_unit_data = Unit::find($product_purchase_data->purchase_unit_id);

                if ($lims_purchase_unit_data->operator == '*') {
                    $old_recieved_value = $old_recieved_value * $lims_purchase_unit_data->operation_value;
                } else {
                    $old_recieved_value = $old_recieved_value / $lims_purchase_unit_data->operation_value;
                }
                $lims_product_data = Product::find($product_purchase_data->product_id);
                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProduct($lims_product_data->id, $product_purchase_data->variant_id)->first();
                    if($lims_product_variant_data) {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $lims_product_data->id],
                            ['variant_id', $product_purchase_data->variant_id],
                            ['warehouse_id', $lims_purchase_data->warehouse_id]
                        ])->first();
                        $lims_product_variant_data->qty -= $old_recieved_value;
                        $lims_product_variant_data->save();
                    }
                }
                elseif($product_purchase_data->product_batch_id) {
                    $product_batch_data = ProductBatch::find($product_purchase_data->product_batch_id);
                    $product_batch_data->qty -= $old_recieved_value;
                    $product_batch_data->save();

                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['product_batch_id', $product_purchase_data->product_batch_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();
                }
                else {
                    $lims_product_warehouse_data = Product_Warehouse::where([
                        ['product_id', $product_purchase_data->product_id],
                        ['warehouse_id', $lims_purchase_data->warehouse_id],
                    ])->first();
                }
                if($product_purchase_data->imei_number) {
                    $position = array_search($lims_product_data->id, $product_id);
                    if($imei_number[$position]) {
                        $prev_imei_numbers = explode(",", $product_purchase_data->imei_number);
                        $new_imei_numbers = explode(",", $imei_number[$position]);
                        foreach ($prev_imei_numbers as $prev_imei_number) {
                            if(($pos = array_search($prev_imei_number, $new_imei_numbers)) !== false) {
                                unset($new_imei_numbers[$pos]);
                            }
                        }
                        $new_imei_number[$position] = implode(",", $new_imei_numbers);
                    }
                }
                $lims_product_data->qty -= $old_recieved_value;
                if($lims_product_warehouse_data) {
                    $lims_product_warehouse_data->qty -= $old_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                $lims_product_data->save();
                $product_purchase_data->delete();
            }

            foreach ($product_id as $key => $pro_id) {
                $price = null;
                $lims_purchase_unit_data = Unit::where('unit_name', $purchase_unit[$key])->first();
                if ($lims_purchase_unit_data->operator == '*') {
                    $new_recieved_value = $recieved[$key] * $lims_purchase_unit_data->operation_value;
                } else {
                    $new_recieved_value = $recieved[$key] / $lims_purchase_unit_data->operation_value;
                }

                $lims_product_data = Product::find($pro_id);
                //dealing with product barch
                if($batch_no[$key]) {
                    $product_batch_data = ProductBatch::where([
                                            ['product_id', $lims_product_data->id],
                                            ['batch_no', $batch_no[$key]]
                                        ])->first();
                    if($product_batch_data) {
                        $product_batch_data->qty += $new_recieved_value;
                        $product_batch_data->expired_date = $expired_date[$key];
                        $product_batch_data->save();
                    }
                    else {
                        $product_batch_data = ProductBatch::create([
                                                'product_id' => $lims_product_data->id,
                                                'batch_no' => $batch_no[$key],
                                                'expired_date' => $expired_date[$key],
                                                'qty' => $new_recieved_value
                                            ]);
                    }
                    $product_purchase['product_batch_id'] = $product_batch_data->id;
                }
                else
                    $product_purchase['product_batch_id'] = null;

                if($lims_product_data->is_variant) {
                    $lims_product_variant_data = ProductVariant::select('id', 'variant_id', 'qty')->FindExactProductWithCode($pro_id, $product_code[$key])->first();
                    if($lims_product_variant_data) {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['variant_id', $lims_product_variant_data->variant_id],
                            ['warehouse_id', $data['warehouse_id']]
                        ])->first();
                        $product_purchase['variant_id'] = $lims_product_variant_data->variant_id;
                        //add quantity to product variant table
                        $lims_product_variant_data->qty += $new_recieved_value;
                        $lims_product_variant_data->save();
                    }
                }
                else {
                    $product_purchase['variant_id'] = null;
                    if($product_purchase['product_batch_id']) {
                        //checking for price
                        $lims_product_warehouse_data = Product_Warehouse::where([
                                                        ['product_id', $pro_id],
                                                        ['warehouse_id', $data['warehouse_id'] ],
                                                    ])
                                                    ->whereNotNull('price')
                                                    ->select('price')
                                                    ->first();
                        if($lims_product_warehouse_data)
                            $price = $lims_product_warehouse_data->price;

                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['product_batch_id', $product_purchase['product_batch_id'] ],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                    else {
                        $lims_product_warehouse_data = Product_Warehouse::where([
                            ['product_id', $pro_id],
                            ['warehouse_id', $data['warehouse_id'] ],
                        ])->first();
                    }
                }

                $lims_product_data->qty += $new_recieved_value;
                if($lims_product_warehouse_data){
                    $lims_product_warehouse_data->qty += $new_recieved_value;
                    $lims_product_warehouse_data->save();
                }
                else {
                    $lims_product_warehouse_data = new Product_Warehouse();
                    $lims_product_warehouse_data->product_id = $pro_id;
                    $lims_product_warehouse_data->product_batch_id = $product_purchase['product_batch_id'];
                    if($lims_product_data->is_variant && $lims_product_variant_data)
                        $lims_product_warehouse_data->variant_id = $lims_product_variant_data->variant_id;
                    $lims_product_warehouse_data->warehouse_id = $data['warehouse_id'];
                    $lims_product_warehouse_data->qty = $new_recieved_value;
                    if($price)
                        $lims_product_warehouse_data->price = $price;
                }
                //dealing with imei numbers
                if($imei_number[$key]) {
                    if($lims_product_warehouse_data->imei_number) {
                        $lims_product_warehouse_data->imei_number .= ',' . $new_imei_number[$key];
                    }
                    else {
                        $lims_product_warehouse_data->imei_number = $new_imei_number[$key];
                    }
                }

                $lims_product_data->save();
                $lims_product_warehouse_data->save();

                $product_purchase['purchase_id'] = $id ;
                $product_purchase['product_id'] = $pro_id;
                $product_purchase['qty'] = $qty[$key];
                $product_purchase['recieved'] = $recieved[$key];
                $product_purchase['purchase_unit_id'] = $lims_purchase_unit_data->id;
                $product_purchase['net_unit_cost'] = $net_unit_cost[$key];
                $product_purchase['discount'] = $discount[$key];
                $product_purchase['tax_rate'] = $tax_rate[$key];
                $product_purchase['tax'] = $tax[$key];
                $product_purchase['total'] = $total[$key];
                $product_purchase['imei_number'] = $imei_number[$key];
                ProductPurchase::create($product_purchase);
            }
            DB::commit();
        }
        catch(Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }
        $lims_purchase_data->update($data);
        return redirect('purchases')->with('message', __('db.Purchase updated successfully'));
    }


public function exportAll(Request $request)
{
    return Excel::download(new AllPurchaseExport($request), 'purchases_all.xlsx');
    return 1;
}

    private function generatePoNumber()
  {
    // Format: PO-20250625-0001
    $date = now()->format('Ymd');
$lastOrder = Purchase::whereDate('created_at', today())->latest()->first();
$nextNumber = $lastOrder ? ((int) substr($lastOrder->po_number, -4)) + 1 : 1;
    return 'PO-' . $date . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
  }
   

    function shipmentStatusText($status)
    {
        switch ($status) {
            case 0:
                return '<span class="badge badge-secondary">Pending</span>';
            case 1:
                return '<span class="badge badge-info">Processing</span>';
            case 2:
                return '<span class="badge badge-primary">In Transit</span>';
            case 3:
                return '<span class="badge badge-warning">Out for Delivery</span>';
            case 4:
                return '<span class="badge badge-success">Delivered</span>';
            case 5:
                return '<span class="badge badge-danger">Failed</span>';
            case 6:
                return '<span class="badge badge-dark">Cancelled</span>';
            default:
                return '<span class="badge badge-light">Unknown</span>';
        }
    }




 public function generatePoNoQT()
{
    $date = now()->format('Ymd');
    $prefix = 'QT-EZ-' . $date . '-';
    $counter = 1;

    do {
        $po_no = $prefix . str_pad($counter, 4, '0', STR_PAD_LEFT);
        $exists = Purchase::where('po_no', $po_no)->exists();
        $counter++;
    } while ($exists);

    return response()->json(['po_no' => $po_no]);
}


}
