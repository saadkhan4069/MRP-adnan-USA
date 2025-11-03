<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPurchase;
use App\Models\Product_Warehouse;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryMovementController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::where('is_active', true)->get();
        return view('backend.inventory_movement.index', compact('warehouses'));
    }

    public function movementData(Request $request)
{
    $columns = [
        0 => 'created_at',
        1 => 'product_name',
        2 => 'type',
        3 => 'warehouse',
        4 => 'qty',
        5 => 'reference',
    ];

    // Purchases (IN)
    $purchases = DB::table('product_purchases')
        ->join('purchases', 'product_purchases.purchase_id', '=', 'purchases.id')
        ->join('products', 'product_purchases.product_id', '=', 'products.id')
        ->join('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
        ->select([
            'product_purchases.created_at as created_at',
            'products.name as product_name',
            'products.code as product_code',
            DB::raw("'Purchase' as type"),
            DB::raw("'IN' as movement_type"),
            'warehouses.name as warehouse',
            'product_purchases.qty as qty',
            DB::raw("CONCAT('PO-', purchases.id) as reference"),
            'purchases.id as reference_id',
            'product_purchases.product_id as product_id',
            'purchases.warehouse_id as warehouse_id',
        ])
        ->where('purchases.status', 1); // Only received purchases

    // Sales (OUT)
    $sales = DB::table('product_sales')
        ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
        ->join('products', 'product_sales.product_id', '=', 'products.id')
        ->join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
        ->select([
            'product_sales.created_at as created_at',
            'products.name as product_name',
            'products.code as product_code',
            DB::raw("'Sale' as type"),
            DB::raw("'OUT' as movement_type"),
            'warehouses.name as warehouse',
            'product_sales.qty as qty',
            DB::raw("CONCAT('SO-', sales.id) as reference"),
            'sales.id as reference_id',
            'product_sales.product_id as product_id',
            'sales.warehouse_id as warehouse_id',
        ])
        ->where('sales.sale_status', 1); // Only completed sales

    // Union both with same column set/order
    $union = $purchases->unionAll($sales);

    // Wrap union and apply filters/sort/pagination OUTSIDE
    $query = DB::query()->fromSub($union, 'm');

    // Filters
    if ($request->product_id && $request->product_id != 'all') {
        $query->where('m.product_id', $request->product_id);
    }

    if ($request->warehouse_id && $request->warehouse_id != 'all') {
        $query->where('m.warehouse_id', $request->warehouse_id);
    }

    if ($request->movement_type && $request->movement_type != 'all') {
        $query->where('m.movement_type', $request->movement_type);
    }

    if ($request->start_date) {
        $query->whereDate('m.created_at', '>=', $request->start_date);
    }

    if ($request->end_date) {
        $query->whereDate('m.created_at', '<=', $request->end_date);
    }

    // Counts
    $totalData = (clone $query)->count();
    $totalFiltered = $totalData;

    // Paging
    $limit = ($request->input('length') != -1) ? (int)$request->input('length') : $totalData;
    $start = (int)$request->input('start', 0);

    // Ordering (default safety)
    $orderIndex = (int)$request->input('order.0.column', 0);
    $order = $columns[$orderIndex] ?? 'created_at';
    $dir = $request->input('order.0.dir', 'desc');

    $movements = (clone $query)
        ->orderBy("m.$order", $dir)
        ->skip($start)
        ->take($limit)
        ->get();

    // Format rows
    $data = [];
    foreach ($movements as $key => $movement) {
        $nestedData['key'] = $key;
        $nestedData['date'] = date('d-m-Y H:i', strtotime($movement->created_at));
        $nestedData['product'] = $movement->product_name.' ['.$movement->product_code.']';
        $nestedData['type'] = $movement->type;
        $nestedData['movement'] = $movement->movement_type;
        $nestedData['warehouse'] = $movement->warehouse;

        if ($movement->movement_type === 'IN') {
            $nestedData['qty_in'] = number_format($movement->qty, 2);
            $nestedData['qty_out'] = '-';
        } else {
            $nestedData['qty_in'] = '-';
            $nestedData['qty_out'] = number_format($movement->qty, 2);
        }

        $nestedData['reference'] = $movement->reference;
        $data[] = $nestedData;
    }

    return response()->json([
        'draw' => intval($request->input('draw')),
        'recordsTotal' => intval($totalData),
        'recordsFiltered' => intval($totalFiltered),
        'data' => $data,
    ]);
}

    public function getProductStock(Request $request)
    {
        $product_id = $request->product_id;
        $warehouse_id = $request->warehouse_id;

        if ($warehouse_id && $warehouse_id != 'all') {
            $stock = Product_Warehouse::where('product_id', $product_id)
                ->where('warehouse_id', $warehouse_id)
                ->sum('qty');
        } else {
            $product = Product::find($product_id);
            $stock = $product ? $product->qty : 0;
        }

        return response()->json(['stock' => $stock]);
    }

    /**
     * API endpoint to get inventory movements by category
     * GET /api/inventory-movements?category_id=1&warehouse_id=1&start_date=2024-01-01&end_date=2024-12-31&movement_type=IN
     */
    public function getInventoryMovementsApi(Request $request)
    {
        try {
            // Validate input parameters
            $request->validate([
                'category_id' => 'nullable|integer|exists:categories,id',
                'warehouse_id' => 'nullable|integer|exists:warehouses,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'movement_type' => 'nullable|in:IN,OUT,all',
                'product_id' => 'nullable|integer|exists:products,id',
                'limit' => 'nullable|integer|min:1|max:1000',
                'page' => 'nullable|integer|min:1'
            ]);

            // Get purchases (IN movements)
            $purchases = DB::table('product_purchases')
                ->join('purchases', 'product_purchases.purchase_id', '=', 'purchases.id')
                ->join('products', 'product_purchases.product_id', '=', 'products.id')
                ->join('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select([
                    'product_purchases.created_at as created_at',
                    'products.name as product_name',
                    'products.code as product_code',
                    'categories.name as category_name',
                    DB::raw("'Purchase' as type"),
                    DB::raw("'IN' as movement_type"),
                    'warehouses.name as warehouse',
                    'product_purchases.qty as qty',
                    DB::raw("CONCAT('PO-', purchases.id) as reference"),
                    'purchases.id as reference_id',
                    'product_purchases.product_id as product_id',
                    'purchases.warehouse_id as warehouse_id',
                    'products.category_id as category_id'
                ])
                ->where('purchases.status', 1); // Only received purchases

            // Get sales (OUT movements)
            $sales = DB::table('product_sales')
                ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
                ->join('products', 'product_sales.product_id', '=', 'products.id')
                ->join('warehouses', 'sales.warehouse_id', '=', 'warehouses.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select([
                    'product_sales.created_at as created_at',
                    'products.name as product_name',
                    'products.code as product_code',
                    'categories.name as category_name',
                    DB::raw("'Sale' as type"),
                    DB::raw("'OUT' as movement_type"),
                    'warehouses.name as warehouse',
                    'product_sales.qty as qty',
                    DB::raw("CONCAT('SO-', sales.id) as reference"),
                    'sales.id as reference_id',
                    'product_sales.product_id as product_id',
                    'sales.warehouse_id as warehouse_id',
                    'products.category_id as category_id'
                ])
                ->where('sales.sale_status', 1); // Only completed sales

            // Union both queries
            $union = $purchases->unionAll($sales);
            $query = DB::query()->fromSub($union, 'm');

            // Apply filters
            if ($request->category_id) {
                $query->where('m.category_id', $request->category_id);
            }

            if ($request->product_id) {
                $query->where('m.product_id', $request->product_id);
            }

            if ($request->warehouse_id) {
                $query->where('m.warehouse_id', $request->warehouse_id);
            }

            if ($request->movement_type && $request->movement_type != 'all') {
                $query->where('m.movement_type', $request->movement_type);
            }

            if ($request->start_date) {
                $query->whereDate('m.created_at', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $query->whereDate('m.created_at', '<=', $request->end_date);
            }

            // Get total count
            $totalRecords = (clone $query)->count();

            // Pagination
            $limit = $request->limit ?? 50;
            $page = $request->page ?? 1;
            $offset = ($page - 1) * $limit;

            // Get data with pagination
            $movements = (clone $query)
                ->orderBy('m.created_at', 'desc')
                ->skip($offset)
                ->take($limit)
                ->get();

            // Format response data
            $formattedData = [];
            foreach ($movements as $movement) {
                $formattedData[] = [
                    'date' => date('d-m-Y H:i', strtotime($movement->created_at)),
                    'product_name' => $movement->product_name,
                    'product_code' => $movement->product_code,
                    'category_name' => $movement->category_name,
                    'type' => $movement->type,
                    'movement_type' => $movement->movement_type,
                    'warehouse' => $movement->warehouse,
                    'quantity' => (float) $movement->qty,
                    'quantity_in' => $movement->movement_type === 'IN' ? (float) $movement->qty : 0,
                    'quantity_out' => $movement->movement_type === 'OUT' ? (float) $movement->qty : 0,
                    'reference' => $movement->reference,
                    'reference_id' => $movement->reference_id,
                    'product_id' => $movement->product_id,
                    'warehouse_id' => $movement->warehouse_id,
                    'category_id' => $movement->category_id
                ];
            }

            // Calculate totals
            $totals = (clone $query)->selectRaw('
                SUM(CASE WHEN movement_type = "IN" THEN qty ELSE 0 END) as total_in,
                SUM(CASE WHEN movement_type = "OUT" THEN qty ELSE 0 END) as total_out
            ')->first();

            return response()->json([
                'success' => true,
                'message' => 'Inventory movements retrieved successfully',
                'data' => $formattedData,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total_records' => $totalRecords,
                    'total_pages' => ceil($totalRecords / $limit),
                    'has_next_page' => $page < ceil($totalRecords / $limit),
                    'has_prev_page' => $page > 1
                ],
                'totals' => [
                    'total_in' => (float) ($totals->total_in ?? 0),
                    'total_out' => (float) ($totals->total_out ?? 0),
                    'net_movement' => (float) (($totals->total_in ?? 0) - ($totals->total_out ?? 0))
                ],
                'filters_applied' => [
                    'category_id' => $request->category_id,
                    'product_id' => $request->product_id,
                    'warehouse_id' => $request->warehouse_id,
                    'movement_type' => $request->movement_type,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving inventory movements: ' . $e->getMessage(),
                'data' => [],
                'pagination' => null,
                'totals' => null
            ], 500);
        }
    }
}

