<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Permission;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\ShipmentPackage;
use App\Models\ShipmentLog;
use App\Models\Customer;
use App\Models\Currency;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class ShipmentController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        // $this->middleware('permission:shipment-view', ['only' => ['index', 'show']]);
        // $this->middleware('permission:shipment-create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:shipment-edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:shipment-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        $permissions = [
            'shipment-create' => Auth::user()->can('shipment-create'),
            'shipment-edit' => Auth::user()->can('shipment-edit'),
            'shipment-delete' => Auth::user()->can('shipment-delete'),
        ];

        return view('backend.shipment.index', compact('permissions'));
    }

    public function create()
    {
        $lims_customer_list = Customer::where('is_active', true)->get();
        $currency_list = Currency::where('is_active', true)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        $lims_product_list_without_variant = Product::where('is_active', true)
            
            ->get();
        $lims_product_list_with_variant = Product::where('is_active', true)
           
            ->get();
        $currency = Currency::where('is_active', true)->first();

        return view('backend.shipment.create', compact(
            'lims_customer_list',
            'currency_list',
            'lims_tax_list',
            'units',
            'lims_product_list_without_variant',
            'lims_product_list_with_variant',
            'currency'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'ship_from_first_name' => 'required|string|max:255',
            'ship_from_address_1' => 'required|string|max:500',
            'ship_from_city' => 'required|string|max:255',
            'ship_from_state' => 'required|string|max:255',
            'ship_from_zipcode' => 'required|string|max:20',
            'ship_from_country' => 'required|string|max:255',
            'ship_from_contact' => 'required|string|max:255',
            'ship_from_email' => 'required|email|max:255',
            'ship_to_first_name' => 'required|string|max:255',
            'ship_to_address_1' => 'required|string|max:500',
            'ship_to_city' => 'required|string|max:255',
            'ship_to_state' => 'required|string|max:255',
            'ship_to_zipcode' => 'required|string|max:20',
            'ship_to_country' => 'required|string|max:255',
            'ship_to_contact' => 'required|string|max:255',
            'ship_to_email' => 'required|email|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'required|numeric|min:0',
            'packages' => 'required|array|min:1',
            'packages.*.weight' => 'required|numeric|min:0',
            'packages.*.length' => 'required|numeric|min:0',
            'packages.*.width' => 'required|numeric|min:0',
            'packages.*.height' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $shipment = Shipment::create([
                'po_no' => $request->po_no,
                'reference_no' => $request->reference_no,
                'customer_id' => $request->customer_id,
                'status' => $request->status ?? 1,
                'ship_from_company' => $request->ship_from_company,
                'ship_from_first_name' => $request->ship_from_first_name,
                'ship_from_address_1' => $request->ship_from_address_1,
                'ship_from_country' => $request->ship_from_country,
                'ship_from_state' => $request->ship_from_state,
                'ship_from_city' => $request->ship_from_city,
                'ship_from_zipcode' => $request->ship_from_zipcode,
                'ship_from_contact' => $request->ship_from_contact,
                'ship_from_email' => $request->ship_from_email,
                'ship_to_company' => $request->ship_to_company,
                'ship_to_first_name' => $request->ship_to_first_name,
                'ship_to_address_1' => $request->ship_to_address_1,
                'ship_to_country' => $request->ship_to_country,
                'ship_to_state' => $request->ship_to_state,
                'ship_to_city' => $request->ship_to_city,
                'ship_to_zipcode' => $request->ship_to_zipcode,
                'ship_to_contact' => $request->ship_to_contact,
                'ship_to_email' => $request->ship_to_email,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate,
                'order_tax_rate' => $request->order_tax_rate ?? 0,
                'order_discount' => $request->order_discount ?? 0,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'comments' => $request->comments,
                'total_qty' => $request->total_qty ?? 0,
                'total_discount' => $request->total_discount ?? 0,
                'total_tax' => $request->total_tax ?? 0,
                'total_cost' => $request->total_cost ?? 0,
                'item' => $request->item ?? 0,
                'order_tax' => $request->order_tax ?? 0,
                'grand_total' => $request->grand_total ?? 0,
                'paid_amount' => $request->paid_amount ?? 0,
                'payment_status' => $request->payment_status ?? 1,
                'user_id' => Auth::id(),
            ]);

            // Create packages
            if ($request->has('packages')) {
                foreach ($request->packages as $packageData) {
                    ShipmentPackage::create([
                        'shipment_id' => $shipment->id,
                        'packaging' => $packageData['packaging'] ?? 'your_packaging',
                        'declared_value' => $packageData['declared_value'] ?? 0,
                        'weight' => $packageData['weight'],
                        'weight_unit' => $packageData['weight_unit'] ?? 'kg',
                        'length' => $packageData['length'],
                        'width' => $packageData['width'],
                        'height' => $packageData['height'],
                        'dim_unit' => $packageData['dim_unit'] ?? 'cm',
                        'dimensions_note' => $packageData['dimensions_note'],
                    ]);
                }
            }

            // Create items
            if ($request->has('product_id')) {
                foreach ($request->product_id as $key => $productId) {
                    if ($productId && $request->qty[$key]) {
                        ShipmentItem::create([
                            'shipment_id' => $shipment->id,
                            'product_id' => $productId,
                            'product_code' => $request->product_code[$key] ?? '',
                            'qty' => $request->qty[$key],
                            'product_unit' => $request->product_unit[$key] ?? '',
                            'net_unit_cost' => $request->net_unit_cost[$key] ?? 0,
                            'subtotal' => $request->subtotal[$key] ?? 0,
                        ]);
                    }
                }
            }

            // Create initial log entry
            ShipmentLog::create([
                'shipment_id' => $shipment->id,
                'status' => $shipment->status,
                'action' => 'created',
                'description' => 'Shipment created',
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('shipment.show', $shipment->id)
                ->with('message', 'Shipment created successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Failed to create shipment: ' . $e->getMessage()]);
        }
    }

    public function show(Shipment $shipment)
    {
        $shipment->load(['customer', 'items.product', 'packages', 'logs.user']);
        return view('backend.shipment.show', compact('shipment'));
    }

    public function edit(Shipment $shipment)
    {
        $lims_customer_list = Customer::where('is_active', true)->get();
        $currency_list = Currency::where('is_active', true)->get();
        $lims_tax_list = Tax::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        $lims_product_list_without_variant = Product::where('is_active', true)
            ->whereNull('variant_id')
            ->get();
        $lims_product_list_with_variant = Product::where('is_active', true)
            ->whereNotNull('variant_id')
            ->get();
        $currency = Currency::find($shipment->currency_id);

        $shipment->load(['items.product', 'packages']);

        return view('backend.shipment.edit', compact(
            'shipment',
            'lims_customer_list',
            'currency_list',
            'lims_tax_list',
            'units',
            'lims_product_list_without_variant',
            'lims_product_list_with_variant',
            'currency'
        ));
    }

    public function update(Request $request, Shipment $shipment)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'ship_from_first_name' => 'required|string|max:255',
            'ship_from_address_1' => 'required|string|max:500',
            'ship_from_city' => 'required|string|max:255',
            'ship_from_state' => 'required|string|max:255',
            'ship_from_zipcode' => 'required|string|max:20',
            'ship_from_country' => 'required|string|max:255',
            'ship_from_contact' => 'required|string|max:255',
            'ship_from_email' => 'required|email|max:255',
            'ship_to_first_name' => 'required|string|max:255',
            'ship_to_address_1' => 'required|string|max:500',
            'ship_to_city' => 'required|string|max:255',
            'ship_to_state' => 'required|string|max:255',
            'ship_to_zipcode' => 'required|string|max:20',
            'ship_to_country' => 'required|string|max:255',
            'ship_to_contact' => 'required|string|max:255',
            'ship_to_email' => 'required|email|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'exchange_rate' => 'required|numeric|min:0',
            'packages' => 'required|array|min:1',
            'packages.*.weight' => 'required|numeric|min:0',
            'packages.*.length' => 'required|numeric|min:0',
            'packages.*.width' => 'required|numeric|min:0',
            'packages.*.height' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $shipment->status;
            
            $shipment->update([
                'po_no' => $request->po_no,
                'reference_no' => $request->reference_no,
                'customer_id' => $request->customer_id,
                'status' => $request->status ?? 1,
                'ship_from_company' => $request->ship_from_company,
                'ship_from_first_name' => $request->ship_from_first_name,
                'ship_from_address_1' => $request->ship_from_address_1,
                'ship_from_country' => $request->ship_from_country,
                'ship_from_state' => $request->ship_from_state,
                'ship_from_city' => $request->ship_from_city,
                'ship_from_zipcode' => $request->ship_from_zipcode,
                'ship_from_contact' => $request->ship_from_contact,
                'ship_from_email' => $request->ship_from_email,
                'ship_to_company' => $request->ship_to_company,
                'ship_to_first_name' => $request->ship_to_first_name,
                'ship_to_address_1' => $request->ship_to_address_1,
                'ship_to_country' => $request->ship_to_country,
                'ship_to_state' => $request->ship_to_state,
                'ship_to_city' => $request->ship_to_city,
                'ship_to_zipcode' => $request->ship_to_zipcode,
                'ship_to_contact' => $request->ship_to_contact,
                'ship_to_email' => $request->ship_to_email,
                'currency_id' => $request->currency_id,
                'exchange_rate' => $request->exchange_rate,
                'order_tax_rate' => $request->order_tax_rate ?? 0,
                'order_discount' => $request->order_discount ?? 0,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'comments' => $request->comments,
                'total_qty' => $request->total_qty ?? 0,
                'total_discount' => $request->total_discount ?? 0,
                'total_tax' => $request->total_tax ?? 0,
                'total_cost' => $request->total_cost ?? 0,
                'item' => $request->item ?? 0,
                'order_tax' => $request->order_tax ?? 0,
                'grand_total' => $request->grand_total ?? 0,
                'paid_amount' => $request->paid_amount ?? 0,
                'payment_status' => $request->payment_status ?? 1,
            ]);

            // Update packages
            $shipment->packages()->delete();
            if ($request->has('packages')) {
                foreach ($request->packages as $packageData) {
                    ShipmentPackage::create([
                        'shipment_id' => $shipment->id,
                        'packaging' => $packageData['packaging'] ?? 'your_packaging',
                        'declared_value' => $packageData['declared_value'] ?? 0,
                        'weight' => $packageData['weight'],
                        'weight_unit' => $packageData['weight_unit'] ?? 'kg',
                        'length' => $packageData['length'],
                        'width' => $packageData['width'],
                        'height' => $packageData['height'],
                        'dim_unit' => $packageData['dim_unit'] ?? 'cm',
                        'dimensions_note' => $packageData['dimensions_note'],
                    ]);
                }
            }

            // Update items
            $shipment->items()->delete();
            if ($request->has('product_id')) {
                foreach ($request->product_id as $key => $productId) {
                    if ($productId && $request->qty[$key]) {
                        ShipmentItem::create([
                            'shipment_id' => $shipment->id,
                            'product_id' => $productId,
                            'product_code' => $request->product_code[$key] ?? '',
                            'qty' => $request->qty[$key],
                            'product_unit' => $request->product_unit[$key] ?? '',
                            'net_unit_cost' => $request->net_unit_cost[$key] ?? 0,
                            'subtotal' => $request->subtotal[$key] ?? 0,
                        ]);
                    }
                }
            }

            // Log status change if status changed
            if ($oldStatus != $shipment->status) {
                ShipmentLog::create([
                    'shipment_id' => $shipment->id,
                    'status' => $shipment->status,
                    'action' => 'status_changed',
                    'description' => 'Status changed from ' . $this->getStatusText($oldStatus) . ' to ' . $this->getStatusText($shipment->status),
                    'user_id' => Auth::id(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            // Log update
            ShipmentLog::create([
                'shipment_id' => $shipment->id,
                'status' => $shipment->status,
                'action' => 'updated',
                'description' => 'Shipment updated',
                'user_id' => Auth::id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('shipment.show', $shipment->id)
                ->with('message', 'Shipment updated successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->withErrors(['error' => 'Failed to update shipment: ' . $e->getMessage()]);
        }
    }

    public function destroy(Shipment $shipment)
    {
        try {
            DB::beginTransaction();

            // Log deletion
            ShipmentLog::create([
                'shipment_id' => $shipment->id,
                'status' => $shipment->status,
                'action' => 'deleted',
                'description' => 'Shipment deleted',
                'user_id' => Auth::id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Delete related records
            $shipment->items()->delete();
            $shipment->packages()->delete();
            $shipment->logs()->delete();
            
            // Delete shipment
            $shipment->delete();

            DB::commit();

            return redirect()->route('shipment.index')
                ->with('message', 'Shipment deleted successfully');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to delete shipment: ' . $e->getMessage()]);
        }
    }

    public function datatable()
    {
        $shipments = Shipment::with(['customer', 'items', 'packages'])
            ->select('shipments.*');

        return DataTables::of($shipments)
            ->addColumn('buyer', function ($shipment) {
                return $shipment->customer ? $shipment->customer->name : '—';
            })
            ->addColumn('from', function ($shipment) {
                $parts = array_filter([
                    $shipment->ship_from_city,
                    $shipment->ship_from_state,
                    $shipment->ship_from_country
                ]);
                return implode(', ', $parts) ?: '—';
            })
            ->addColumn('to', function ($shipment) {
                $parts = array_filter([
                    $shipment->ship_to_city,
                    $shipment->ship_to_state,
                    $shipment->ship_to_country
                ]);
                return implode(', ', $parts) ?: '—';
            })
            ->addColumn('totals', function ($shipment) {
                return [
                    'items' => $shipment->item,
                    'qty' => $shipment->total_qty,
                    'tax' => $shipment->total_tax,
                    'shipping' => $shipment->shipping_cost,
                    'grand_total' => $shipment->grand_total
                ];
            })
            ->addColumn('packages', function ($shipment) {
                return $shipment->packages->map(function ($package) {
                    return [
                        'packaging' => $package->packaging,
                        'weight' => $package->weight,
                        'weight_unit' => $package->weight_unit,
                        'length' => $package->length,
                        'width' => $package->width,
                        'height' => $package->height,
                        'dim_unit' => $package->dim_unit,
                        'declared_value' => $package->declared_value
                    ];
                });
            })
            ->addColumn('items', function ($shipment) {
                return $shipment->items->map(function ($item) {
                    return [
                        'product_code' => $item->product_code,
                        'qty' => $item->qty,
                        'product_unit' => $item->product_unit,
                        'net_unit_cost' => $item->net_unit_cost,
                        'subtotal' => $item->subtotal
                    ];
                });
            })
            ->addColumn('tracking', function ($shipment) {
                return $shipment->tracking_number;
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    public function poSearch(Request $request)
    {
        $term = $request->get('term');
        
        $shipments = Shipment::where('po_no', 'LIKE', "%{$term}%")
            ->orWhere('reference_no', 'LIKE', "%{$term}%")
            ->limit(10)
            ->get(['id', 'po_no', 'reference_no', 'created_at']);

        return response()->json($shipments);
    }

    private function getStatusText($status)
    {
        $statusMap = [
            1 => 'Pending',
            2 => 'In Transit',
            3 => 'Delivered',
            4 => 'Returned',
            5 => 'Cancelled'
        ];

        return $statusMap[$status] ?? 'Unknown';
    }
} 