<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 24mm 18mm; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
    h1 { font-size: 18px; margin: 0 0 6px; }
    h2 { font-size: 15px; margin: 18px 0 8px; }
    .meta { font-size: 12px; margin-bottom: 8px; }
    table { width:100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 6px 8px; }
    th { background: #f5f5f5; text-align: left; }
    .right { text-align: right; }
    .small { font-size: 11px; }
    .page-break { page-break-after: always; }
  </style>
</head>
<body>

  <h1>LABORATORY SCALE FORMULA</h1>
  <div class="meta">
    Date: <strong>{{ optional($product->formula_date)->format('m/d/Y') }}</strong><br>
    Product Name: <strong>{{ $product->name }}</strong><br>
    Yield: <strong>{{ rtrim(rtrim(number_format($product->yield_gallons, 2, '.', ''), '0'), '.') }} gallons</strong><br>
    Process: <strong>{{ $product->process ?? '—' }}</strong>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width:26px">#</th>
        <th>Ingredients</th>
        <th>Supplier</th>
        <th>Product Code</th>
        <th class="right">% (w/w)</th>
        <th class="right">lbs/ 1k gal</th>
        <th class="right">gal/ 1k gal</th>
      </tr>
    </thead>
    <tbody>
      @foreach($materials as $i => $m)
      <tr>
        <td class="small">{{ $i+1 }}</td>
        <td>{{ $m->name }}</td>
        <td class="small">{{ $m->supplier_product_code ?? ($m->pivot->supplier_product_code ?? '—') }}</td>
        <td class="small">{{ $m->supplier_name ?? ($m->pivot->product_code ?? '—') }}</td>
        <td class="right">{{ $m->pivot->percent_w_w !== null ? number_format($m->pivot->percent_w_w, 5) : '—' }}</td>
        <td class="right">{{ $m->pivot->lbs_per_1k_gal !== null ? number_format($m->pivot->lbs_per_1k_gal, 3) : '—' }}</td>
        <td class="right">{{ $m->pivot->gal_per_1k_gal !== null ? number_format($m->pivot->gal_per_1k_gal, 3) : '—' }}</td>
      </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <th colspan="4" class="right">Total</th>
        <th class="right">{{ number_format($totals['percent'] ?? 0, 5) }}</th>
        <th class="right">{{ number_format($totals['lbs'] ?? 0, 3) }}</th>
        <th class="right">{{ number_format($totals['gal'] ?? 0, 3) }}</th>
      </tr>
    </tfoot>
  </table>

  <h2>Batching Instructions</h2>
  <p class="small">
    {{ $product->specs->batching_instructions ?? 'Blend all ingredients in the order listed. Dissolve each ingredient completely before adding another. Carbonate and fill; Tunnel Pasteurize.' }}
  </p>

  <h2>Laboratory Scale Specifications - Finished Product</h2>
  <table>
    <tr><th>Density (lbs/gal)</th><td>{{ $product->specs->density_lbs_per_gal ?? '—' }}</td></tr>
    <tr><th>pH</th><td>{{ $product->specs->ph ?? '—' }}</td></tr>
    <tr><th>Brix</th><td>{{ $product->specs->brix ?? '—' }}</td></tr>
    <tr><th>Taste</th><td>{{ $product->specs->taste ?? '—' }}</td></tr>
    <tr><th>Appearance</th><td>{{ $product->specs->appearance ?? '—' }}</td></tr>
  </table>

  <div class="page-break"></div>

  <h1>Suggested Nutritional Panel</h1>
  <div class="meta">
    Date: <strong>{{ optional($product->formula_date)->format('m/d/Y') }}</strong><br>
    Product Name: <strong>{{ $product->name }}</strong>
  </div>
  <table>
    <tr><th style="width:220px">Serving Size (fl oz)</th><td>{{ $product->nutrition->serving_size_fl_oz ?? '—' }}</td></tr>
    <tr><th>% Juice</th><td>{{ $product->nutrition->percent_juice ?? '—' }}%</td></tr>
    <tr><th>Allergen</th><td>{{ $product->nutrition->allergen ?? 'None' }}</td></tr>
    <tr><th>Ingredients Statement</th><td>{{ $product->nutrition->ingredients_statement ?? '—' }}</td></tr>
    <tr><th>Calories</th><td>{{ $product->nutrition->calories ?? '—' }}</td></tr>
    <tr><th>Total Sugars (g)</th><td>{{ $product->nutrition->total_sugars_g ?? '—' }}</td></tr>
    <tr><th>Added Sugars (g)</th><td>{{ $product->nutrition->added_sugars_g ?? '—' }}</td></tr>
  </table>

  <p class="small" style="margin-top:12px;">
    <em>Disclaimer:</em> Manufacturer must verify label compliance per regulations.
  </p>

</body>
</html>
