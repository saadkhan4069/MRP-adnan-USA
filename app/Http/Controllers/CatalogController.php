<?php

// app/Http/Controllers/CatalogController.php
namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\ProductSpec;
use App\Models\NutritionPanel;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;

  
class CatalogController extends Controller
{
   public function sampleCsv(): StreamedResponse
    {
        $csv = implode("\n", [
            'ingredient,supplier,product_code,percent_w_w,lbs_per_1k_gal,gal_per_1k_gal,qty,unit,wastage_pct,sort_order,product_code',
            'Carbonated Filtered Water,Plant,n/a,91.8482,7871.391,943.246,943.246,gal,0,1',
            'Fine/Extra Fine Granulated Sugar,American International Foods (AIF),n/a,7.0433,603.611,,603.611,lbs,0,2',
        ]);

        return response()->streamDownload(function() use ($csv) {
            echo $csv;
        }, 'materials_sample.csv', ['Content-Type' => 'text/csv']);
    }

    public function importCsv(Request $request, Product $product)
    {
        $request->validate([
            'csv' => 'required|file|mimes:csv,txt',
        ]);

        $path = $request->file('csv')->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_map(fn($h) => strtolower(trim($h)), array_shift($rows));

        $required = ['ingredient','qty','unit'];
        foreach ($required as $need) {
            if (!in_array($need, $header)) {
                return back()->withErrors(["CSV missing required column: {$need}"]);
            }
        }

        foreach ($rows as $r) {
            if (count($r) === 1 && trim($r[0]) === '') continue; // skip blanks
            $data = array_combine($header, $r);

            $rm = RawMaterial::firstOrCreate(
                ['name' => trim($data['ingredient'])],
                ['sku'  => $data['product_code'] ?? null]
            );

            // optional store supplier fields on RM (if your schema allows)
            if (isset($data['supplier'])) {
                $rm->supplier_name = $data['supplier'];
            }
            if (isset($data['product_code'])) {
                $rm->supplier_product_code = $data['product_code'];
            }
            if ($rm->isDirty()) $rm->save();

            $product->rawMaterials()->syncWithoutDetaching([
                $rm->id => [
                    'quantity' => (float)($data['qty'] ?? 0),
                    'unit' => $data['unit'] ?? 'kg',
                    'wastage_pct' => (float)($data['wastage_pct'] ?? 0),
                    'percent_w_w' => $data['percent_w_w'] !== '' ? (float)$data['percent_w_w'] : null,
                    'lbs_per_1k_gal' => $data['lbs_per_1k_gal'] !== '' ? (float)$data['lbs_per_1k_gal'] : null,
                    'gal_per_1k_gal' => $data['gal_per_1k_gal'] !== '' ? (float)$data['gal_per_1k_gal'] : null,
                    'supplier' => $data['supplier'] ?? null,
                    'supplier_product_code' => $data['product_code'] ?? null,
                    'sort_order' => (int)($data['sort_order'] ?? 0),
                    'product_code' => (int)($data['product_code'] ?? null),
                ],
            ]);
        }

        return back()->with('success', 'CSV imported successfully.');
    }

	 public function pdf(Product $product)
    {
        // Load rows same order you show on page
        $materials = $product->rawMaterials()->withPivot([
            'quantity','unit','wastage_pct','percent_w_w','lbs_per_1k_gal','gal_per_1k_gal','sort_order','product_code'
        ])->orderBy('pivot_sort_order')->get();

        $totals = [
            'percent' => round($materials->sum('pivot.percent_w_w'), 5),
            'lbs'     => round($materials->sum('pivot.lbs_per_1k_gal'), 3),
            'gal'     => round($materials->sum('pivot.gal_per_1k_gal'), 3),
        ];

        $pdf = \PDF::loadView('backend.product.catalog-pdf', compact('product', 'materials', 'totals'))
                  ->setPaper('letter', 'portrait');
         
        $name = preg_replace('/[^A-Za-z0-9 _-]/', '', $product->name) . '_LabFormula.pdf';
        return $pdf->download($name);
    }
      
    /* ---------- Raw Material Master ---------- */
    public function rawMaterialStore(Request $request)
    {
        $data = $request->validate([
            'name'               => ['required','string','max:255'],
            'sku'                => ['nullable','string','max:255'],
            'category'           => ['nullable','string','max:255'],
            'default_unit'       => ['required','string','max:16'],
            'default_price'      => ['nullable','numeric','min:0'],
            'is_active'          => ['nullable','boolean'],
            'vendor_id'          => ['nullable','exists:vendors,id'],
            'supplier_product_code' => ['nullable','string','max:255'],
        ]);
        $data['is_active'] = $data['is_active'] ?? 1;

        $rm = RawMaterial::create($data);
        return back()->with('success', 'Raw material created: '.$rm->name);
    }

    public function vendorStore(Request $request)
    {
        $v = Vendor::create(
            $request->validate([
                'name' => ['required','string','max:255'],
                'contact_person' => ['nullable','string','max:255'],
                'phone' => ['nullable','string','max:255'],
                'email' => ['nullable','email','max:255'],
            ])
        );
        return back()->with('success', 'Vendor saved: '.$v->name);
    }

    /* ---------- Catalog (PDF-style) ---------- */
   public function show(Product $product)
{
    // Eager-load with ordering on the pivot + vendor + other relations
    $product->load([
        'specs',
        'nutrition',
        // order by pivot.sort_order, then name
        'rawMaterials' => function ($q) {
            $q->orderBy('product_raw_material.sort_order')
              ->orderBy('name');
        },
        
    ]);

    // Build rows (null-safe casting)
    $materials = $product->rawMaterials->map(function ($m) {
        $qty        = (float) ($m->pivot->quantity      ?? 0);
        $price      = (float) ($m->pivot->unit_price    ?? 0);
        $wastage    = (float) ($m->pivot->wastage_pct   ?? 0);
        $effQty     = $qty * (1 + ($wastage / 100));
        $lineTotal  = $effQty * $price;

        // PDF extras (may be null -> cast to float safely for totals later)
        $percent    = $m->pivot->percent_w_w    !== null ? (float) $m->pivot->percent_w_w    : null;
        $lbs1k      = $m->pivot->lbs_per_1k_gal !== null ? (float) $m->pivot->lbs_per_1k_gal : null;
        $gal1k      = $m->pivot->gal_per_1k_gal !== null ? (float) $m->pivot->gal_per_1k_gal : null;

        return [
            'model'          => $m,
            'qty'            => $qty,
            'unit'           => $m->pivot->unit ?? 'kg',
            'unit_price'     => $price,
            'wastage_pct'    => $wastage,
            'effective_qty'  => $effQty,
            'line_total'     => $lineTotal,

            // PDF extra columns:
            'percent_w_w'    => $percent,
            'lbs_per_1k_gal' => $lbs1k,
            'gal_per_1k_gal' => $gal1k,
            'supplier'       => optional($m->vendor)->name,
            'supplier_code'  => $m->supplier_product_code,
            'sort_order'     => (int) ($m->pivot->sort_order ?? 0),
            'product_code'     => (string) ($m->pivot->product_code ?? ''),
        ];
    });

    // Totals (null-safe: treat null as 0)
    $totals = [
        'materials_cost' => round((float) $materials->sum('line_total'), 2),
        'total_percent'  => round((float) $materials->sum(fn ($r) => (float) ($r['percent_w_w']    ?? 0)), 5),
        'total_lbs'      => round((float) $materials->sum(fn ($r) => (float) ($r['lbs_per_1k_gal'] ?? 0)), 3),
        'total_gal'      => round((float) $materials->sum(fn ($r) => (float) ($r['gal_per_1k_gal'] ?? 0)), 3),
    ];

    return view('backend.product.catalog', compact('product', 'materials', 'totals'));
}


    /* ---------- Materials on product ---------- */
    public function storeMaterial(Product $product, Request $request)
    {
        $data = $request->validate([
            'raw_material_id' => ['required','exists:raw_materials,id'],
            'quantity'        => ['required','numeric','min:0'],
            'unit'            => ['required','string','max:16'],
            //'unit_price'      => ['required','numeric','min:0'],
            'wastage_pct'     => ['nullable','numeric','min:0'],
            // PDF-extra columns
            'percent_w_w'     => ['nullable','numeric','min:0'],
            'lbs_per_1k_gal'  => ['nullable','numeric','min:0'],
            'gal_per_1k_gal'  => ['nullable','numeric','min:0'],
            'sort_order'      => ['nullable','integer','min:0'],
            'product_code'      => ['nullable','string','max:150'],
        ]);

        $product->rawMaterials()->syncWithoutDetaching([
            $data['raw_material_id'] => [
                'quantity'        => $data['quantity'],
                'unit'            => $data['unit'],
                //'unit_price'      => $data['unit_price'],
                'wastage_pct'     => $data['wastage_pct'] ?? 0,
                'percent_w_w'     => $data['percent_w_w'] ?? null,
                'lbs_per_1k_gal'  => $data['lbs_per_1k_gal'] ?? null,
                'gal_per_1k_gal'  => $data['gal_per_1k_gal'] ?? null,
                'sort_order'      => $data['sort_order'] ?? 0,
                'product_code'      => $data['product_code'] ?? null,
            ]
        ]);

        return back()->with('success', 'Raw material added.');
    }

    public function updateMaterial(Product $product, RawMaterial $material, Request $request)
    {
        $data = $request->validate([
            'quantity'        => ['required','numeric','min:0'],
            'unit'            => ['required','string','max:16'],
            // 'unit_price'      => ['required','numeric','min:0'],
            'wastage_pct'     => ['nullable','numeric','min:0'],
            'percent_w_w'     => ['nullable','numeric','min:0'],
            'lbs_per_1k_gal'  => ['nullable','numeric','min:0'],
            'gal_per_1k_gal'  => ['nullable','numeric','min:0'],
            'sort_order'      => ['nullable','integer','min:0'],
            'product_code'      => ['nullable','string','max:150'],
        ]);

        $product->rawMaterials()->updateExistingPivot($material->id, [
            'quantity'        => $data['quantity'],
            'unit'            => $data['unit'],
            // 'unit_price'      => $data['unit_price'],
            'wastage_pct'     => $data['wastage_pct'] ?? 0,
            'percent_w_w'     => $data['percent_w_w'] ?? null,
            'lbs_per_1k_gal'  => $data['lbs_per_1k_gal'] ?? null,
            'gal_per_1k_gal'  => $data['gal_per_1k_gal'] ?? null,
            'sort_order'      => $data['sort_order'] ?? 0,
            'product_code'      => $data['product_code'] ?? null,
        ]);

        return back()->with('success', 'Raw material updated.');
    }

    public function destroyMaterial(Product $product, RawMaterial $material)
    {
        $product->rawMaterials()->detach($material->id);
        return back()->with('success', 'Raw material removed.');
    }

    /* ---------- Specs & Nutrition ---------- */
    public function saveSpecs(Product $product, Request $request)
    {   
        $data = $request->validate([
            'formula_date'          => ['nullable','date'],
            'process'               => ['nullable','string','max:255'],
            'yield_gallons'         => ['nullable','numeric','min:0'],
            'density_lbs_per_gal'   => ['nullable','numeric','min:0'],
            'ph'                    => ['nullable','string','max:255'],
            'brix'                  => ['nullable','string','max:255'],
            'taste'                 => ['nullable','string','max:255'],
            'appearance'            => ['nullable','string','max:255'],
            'batching_instructions' => ['nullable','string'],
        ]);
  
       
 
        
        // $product->update($request->only('formula_date','process','yield_gallons'));
        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // return 1;
      // ✅ Convert formula_date if provided
    if (!empty($data['formula_date'])) {
        try {
            // Convert d-m-Y (02-09-2025) to Y-m-d H:i:s
            $data['formula_date'] = Carbon::createFromFormat('d-m-Y', $data['formula_date'])->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // fallback agar user galat format bhej de
            $data['formula_date'] = null;
        }
    }

    $spec = $product->specs ?: new ProductSpec(['product_id' => $product->id]);

    $spec->fill([
        'density_lbs_per_gal'   => $data['density_lbs_per_gal'] ?? null,
        'ph'                    => $data['ph'] ?? null,
        'brix'                  => $data['brix'] ?? null,
        'taste'                 => $data['taste'] ?? null,
        'appearance'            => $data['appearance'] ?? null,
        'batching_instructions' => $data['batching_instructions'] ?? null,
        'formula_date'          => $data['formula_date'] ?? null,
        'process'               => $data['process'] ?? null,
        'yield_gallons'         => $data['yield_gallons'] ?? null,
    ])->save();
    
    return back()->with('success', 'Specifications saved.');
    }

    public function saveNutrition(Product $product, Request $request)
    {
        $data = $request->validate([
            'serving_size_fl_oz'     => ['nullable','numeric','min:0'],
            'percent_juice'          => ['nullable','numeric','min:0'],
            'allergen'               => ['nullable','string','max:255'],
            'ingredients_statement'  => ['nullable','string'],
            'calories'               => ['nullable','integer','min:0'],
            'total_sugars_g'         => ['nullable','integer','min:0'],
            'added_sugars_g'         => ['nullable','integer','min:0'],
        ]);

        $np = $product->nutrition ?: new NutritionPanel(['product_id'=>$product->id]);
        $np->fill($data)->save();

        return back()->with('success', 'Nutrition panel saved.');
    }
}
