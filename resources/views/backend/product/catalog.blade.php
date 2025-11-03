{{-- resources/views/backend/product/catalog.blade.php --}}
@extends('backend.layout.main')

@section('content')
<div class="container-xxl py-4">

  {{-- Alerts --}}
  @if(session('success'))
    <div class="alert alert-success shadow-sm mb-3">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger shadow-sm mb-3">
      <ul class="mb-0">
        @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
      </ul>
    </div>
  @endif

  {{-- ===== Product Header ===== --}}
  @includeIf('backend.product.partials.header')

  {{-- ===== Laboratory Scale Formula (PDF Table) ===== --}}
  <div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-0 py-3 rounded-top-4">
      <div class="d-flex align-items-center gap-2 w-100">
        <div class="d-flex align-items-center gap-2">
          <i class="fa fa-beaker fs-5 text-primary"></i>
          <h5 class="mb-0">Laboratory Scale Formula</h5>
        </div>
        <span class="ms-3 small text-muted d-none d-md-inline">
          Date: <strong>{{ $product->specs?->formula_date?->format('Y-m-d') ?? '—' }}</strong> &nbsp; • &nbsp;
          Yield: <strong>{{ $product->specs?->yield_gallons 
    ? rtrim(rtrim(number_format((float) $product->specs?->yield_gallons, 2, '.', ''), '0'), '.') 
    : '' }} gal</strong> &nbsp; • &nbsp;
          Process: <strong>{{ $product->specs->process ?? '—' }}</strong>
        </span>

        {{-- Actions: PDF + Sample CSV + Upload CSV --}}
        <div class="ms-auto d-flex gap-2">
          <a href="{{ route('catalog.pdf', $product->id) }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="fa fa-filetype-pdf me-1"></i> PDF
          </a>
          <a href="{{ route('materials.sampleCsv') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="fa fa-download me-1"></i> Sample CSV
          </a>
          <button class="btn btn-primary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#csvUploadModal">
            <i class="fa fa-upload me-1"></i> Upload CSV
          </button>
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr class="text-muted small">
              <th style="width:36px">#</th>
              <th>Ingredients</th>
              <th class="text-end">Qty</th>
              <th>Supplier</th>
              <th>Product Code</th>
              <th class="text-end">% (w/w)</th>
              <th class="text-end">lbs / 1k gal</th>
              <th class="text-end">gal / 1k gal</th>
              <th>Unit</th>
              <th class="text-end">Wastage %</th>
              <th class="text-center" style="width:110px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($materials as $i => $row)
              <tr>
                <td class="text-muted">{{ $i+1 }}</td>
                <td class="fw-semibold">{{ $row['model']->name }}</td>
                <td class="text-end">{{ rtrim(rtrim(number_format($row['qty'], 6, '.', ''), '0'), '.') }}</td>
                <td class="small">{{ $row['supplier_code'] ?? '—' }}</td>
                <td class="small">{{ $row['product_code'] ?? '—' }}</td>
                <td class="text-end">{{ $row['percent_w_w'] !== null ? number_format($row['percent_w_w'], 5) : '—' }}</td>
                <td class="text-end">{{ $row['lbs_per_1k_gal'] !== null ? number_format($row['lbs_per_1k_gal'], 3) : '—' }}</td>
                <td class="text-end">{{ $row['gal_per_1k_gal'] !== null ? number_format($row['gal_per_1k_gal'], 3) : '—' }}</td>
                <td>{{ $row['unit'] }}</td>
                <td class="text-end">{{ number_format($row['wastage_pct'], 2) }}</td>
                <td class="text-center">
                  <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3"
                      data-bs-toggle="modal" data-bs-target="#editMaterialModal{{ $row['model']->id }}">
                      <i class="fa fa-pencil"></i>
                    </button>
                    <form action="{{ route('materials.destroy', [$product->id, $row['model']->id]) }}" method="POST" class="d-inline">
                      @csrf @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger rounded-3"
                        onclick="return confirm('Remove this material?')">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>

              {{-- Collect Edit Modals OUTSIDE the table --}}
              @push('modals')
              <div class="modal fade" id="editMaterialModal{{ $row['model']->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <form action="{{ route('materials.update', [$product->id, $row['model']->id]) }}" method="POST" class="modal-content rounded-4 border-0 shadow">
                    @csrf @method('PUT')
                    <div class="modal-header border-0 pb-0">
                      <h5 class="modal-title">Edit Material</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                      <div class="row g-3">
                        <div class="col-12 small text-muted">{{ $row['model']->name }}</div>

                        <div class="col-md-6">
                          <label class="form-label">Quantity</label>
                          <input type="number" step="0.000001" name="quantity" value="{{ $row['qty'] }}" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Unit</label>
                          <input type="text" name="unit" value="{{ $row['unit'] }}" class="form-control rounded-3" required>
                        </div>

                        <div class="col-md-6">
                          <label class="form-label">Wastage %</label>
                          <input type="number" step="0.01" name="wastage_pct" value="{{ $row['wastage_pct'] }}" class="form-control rounded-3">
                        </div>

                        {{-- PDF extras --}}
                        <div class="col-md-4">
                          <label class="form-label">% (w/w)</label>
                          <input type="number" step="0.00001" name="percent_w_w" value="{{ $row['percent_w_w'] }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">lbs / 1k gal</label>
                          <input type="number" step="0.001" name="lbs_per_1k_gal" value="{{ $row['lbs_per_1k_gal'] }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-4">
                          <label class="form-label">gal / 1k gal</label>
                          <input type="number" step="0.001" name="gal_per_1k_gal" value="{{ $row['gal_per_1k_gal'] }}" class="form-control rounded-3">
                        </div>

                        <div class="col-md-4">
                          <label class="form-label">Sort Order</label>
                          <input type="number" name="sort_order" value="{{ $row['model']->pivot->sort_order }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-4">
                        <label class="form-label">Product Codde (Optional)</label>
                        <input type="text" name="product_code" value="{{ $row['model']->pivot->product_code }}"  class="form-control rounded-3">
                        </div>
                      </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                      <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                      <button class="btn btn-primary rounded-3">Save changes</button>
                    </div>
                  </form>
                </div>
              </div>
              @endpush
              {{-- /Collect Edit Modals --}}
            @empty
              <tr><td colspan="11" class="text-center text-muted py-4">No materials added.</td></tr>
            @endforelse
          </tbody>

          <tfoot class="table-light">
            <tr>
              <th colspan="4" class="text-end">Totals</th>
              <th class="text-end">{{ number_format(($totals['total_percent'] ?? 0), 5) }}</th>
              <th class="text-end">{{ number_format(($totals['total_lbs'] ?? 0), 3) }}</th>
              <th class="text-end">{{ number_format(($totals['total_gal'] ?? 0), 3) }}</th>
              <th colspan="4"></th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    {{-- Render all edit modals here (outside the table) --}}
    @stack('modals')

    {{-- Add Material (button) --}}
    <div class="card-footer bg-white border-0">
      <button class="btn btn-primary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#addMaterialModal">
        <i class="fa fa-plus-circle me-1"></i> Add Material
      </button>
    </div>
  </div>

  {{-- ===== Specifications & Batching ===== --}}
  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-header bg-white border-0 py-3 rounded-top-4">
          <h5 class="mb-0">Laboratory Scale Specifications - Finished Product</h5>
        </div>
        <form action="{{ route('catalog.specs.save', $product->id) }}" method="POST" class="card-body">
          @csrf
          <div class="row g-3">
            <div class="col-4"><label class="form-label">Density (lbs/gal)</label>
              <input type="number" step="0.001" name="density_lbs_per_gal" value="{{ $product->specs->density_lbs_per_gal ?? '' }}" class="form-control rounded-3">
            </div>
            <div class="col-4"><label class="form-label">pH</label>
              <input type="text"  name="ph" value="{{ $product->specs->ph ?? '' }}" class="form-control rounded-3"> <!---//step="0.01"--->
            </div>
            <div class="col-4"><label class="form-label">Brix</label>
              <input type="text"  name="brix" value="{{ $product->specs->brix ?? '' }}" class="form-control rounded-3"> <!---//step="0.01"--->
            </div>
            <div class="col-6"><label class="form-label">Taste</label>
              <input type="text" name="taste" value="{{ $product->specs->taste ?? '' }}" class="form-control rounded-3">
            </div>
            <div class="col-6"><label class="form-label">Appearance</label>
              <input type="text" name="appearance" value="{{ $product->specs->appearance ?? '' }}" class="form-control rounded-3">
            </div>

            <div class="col-4"><label class="form-label">Formula Date</label>
              <input type="text" name="formula_date"  
       value="{{ $product->specs?->formula_date?->format('Y-m-d') ?? '—' }}" 
       class="form-control rounded-3 date">
            </div>
            <div class="col-4"><label class="form-label">Yield (gallons)</label>
             <input type="number" step="0.01" name="yield_gallons" 
       value="{{ optional($product->specs)->yield_gallons ?? '' }}" 
       class="form-control rounded-3">
            </div>
            <div class="col-4"><label class="form-label">Process</label>
              <input type="text" name="process" 
       value="{{ $product->specs?->process ?? '' }}" 
       class="form-control rounded-3">
            </div>

            <div class="col-12"><label class="form-label">Batching Instructions</label>
              <textarea name="batching_instructions" rows="5" class="form-control rounded-3">{{ $product->specs->batching_instructions ?? '' }}</textarea>
              <div class="form-text">Blend in order; dissolve each ingredient completely; carbonate & fill; tunnel pasteurize.</div>
            </div>
            <div class="col-12 text-end">
              <button class="btn btn-primary rounded-3">Save Specifications</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    {{-- ===== Vendor Contacts REMOVED per request ===== --}}

    <div class="col-lg-6">
      <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-header bg-white border-0 py-3 rounded-top-4">
          <h5 class="mb-0">Suggested Nutritional Panel</h5>
        </div>
        <form action="{{ route('catalog.nutrition.save', $product->id) }}" method="POST" class="card-body">
          @csrf
          <div class="row g-3">
            <div class="col-3"><label class="form-label">Serving Size (fl oz)</label>
              <input type="number" step="0.01" name="serving_size_fl_oz" value="{{ $product->nutrition->serving_size_fl_oz ?? '' }}" class="form-control rounded-3">
            </div>
            <div class="col-3"><label class="form-label">% Juice</label>
              <input type="number" step="0.01" name="percent_juice" value="{{ $product->nutrition->percent_juice ?? '' }}" class="form-control rounded-3">
            </div>
            <div class="col-6"><label class="form-label">Allergen</label>
              <input type="text" name="allergen" value="{{ $product->nutrition->allergen ?? '' }}" class="form-control rounded-3" placeholder="None">
            </div>
            <div class="col-12"><label class="form-label">Ingredients Statement</label>
              <textarea name="ingredients_statement" rows="3" class="form-control rounded-3">{{ $product->nutrition->ingredients_statement ?? '' }}</textarea>
            </div>
            <div class="col-4"><label class="form-label">Calories</label>
              <input type="number" name="calories" value="{{ $product->nutrition->calories ?? '' }}" class="form-control rounded-3">
            </div>
            <div class="col-4"><label class="form-label">Total Sugars (g)</label>
              <input type="number" name="total_sugars_g" value="{{ $product->nutrition->total_sugars_g ?? '' }}" class="form-control rounded-3">
            </div>
            <div class="col-4"><label class="form-label">Added Sugars (g)</label>
              <input type="number" name="added_sugars_g" value="{{ $product->nutrition->added_sugars_g ?? '' }}" class="form-control rounded-3">
            </div>
            <div class="col-12 text-end">
              <button class="btn btn-primary rounded-3">Save Nutrition</button>
            </div>
          </div>
        </form>
        <div class="card-footer small text-muted">
          Disclaimer: manufacturer must verify label compliance per regulations.
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ===== Add Material Modal (prices removed) ===== --}}
<div class="modal fade" id="addMaterialModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <form action="{{ route('materials.store', $product->id) }}" method="POST" class="modal-content rounded-4 border-0 shadow">
      @csrf
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">Add Raw Material</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <label class="form-label mb-0">Select Material</label>
              <button type="button" class="btn btn-outline-primary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#createMaterialModal">+ New Material</button>
            </div>
            <select name="raw_material_id" class="form-select rounded-3" required>
              <option value="" disabled selected>— Choose —</option>
              @foreach(\App\Models\RawMaterial::where('is_active',1)->orderBy('name')->get() as $rm)
                <option value="{{ $rm->id }}">{{ $rm->name }} {{ $rm->sku ? '(' . $rm->sku . ')' : '' }}</option>
              @endforeach
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Quantity</label>
            <input type="number" step="0.000001" name="quantity" class="form-control rounded-3" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Unit</label>
            <input type="text" name="unit" value="kg" class="form-control rounded-3" required>
          </div>
          <div class="col-md-3">
            <label class="form-label">Wastage % (optional)</label>
            <input type="number" step="0.01" name="wastage_pct" value="0" class="form-control rounded-3">
          </div>

          {{-- PDF extras --}}
          <div class="col-md-3">
            <label class="form-label">% (w/w)</label>
            <input type="number" step="0.00001" name="percent_w_w" class="form-control rounded-3">
          </div>
          <div class="col-md-3">
            <label class="form-label">lbs / 1k gal</label>
            <input type="number" step="0.001" name="lbs_per_1k_gal" class="form-control rounded-3">
          </div>
          <div class="col-md-3">
            <label class="form-label">gal / 1k gal</label>
            <input type="number" step="0.001" name="gal_per_1k_gal" class="form-control rounded-3">
          </div>
          <div class="col-md-3">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" value="0" class="form-control rounded-3">
          </div>
           <div class="col-md-3">
            <label class="form-label">Product Codde (Optional)</label>
            <input type="text" name="product_code" value="0" class="form-control rounded-3">
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary rounded-3">Add</button>
      </div>
    </form>
  </div>
</div>

{{-- ===== Create Material Modal (Vendor + Default Price removed) ===== --}}
<div class="modal fade" id="createMaterialModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="{{ route('raw-materials.store') }}" method="POST" class="modal-content rounded-4 border-0 shadow">
      @csrf
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">New Raw Material</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-12"><label class="form-label">Name</label>
            <input type="text" name="name" class="form-control rounded-3" required>
          </div>
          <div class="col-6"><label class="form-label">SKU (optional)</label>
            <input type="text" name="sku" class="form-control rounded-3">
          </div>
          <div class="col-6"><label class="form-label">Category (optional)</label>
            <input type="text" name="category" class="form-control rounded-3">
          </div>
          <div class="col-6"><label class="form-label">Default Unit</label>
            <input type="text" name="default_unit" value="kg" class="form-control rounded-3" required>
          </div>

          {{-- Vendor field removed per request --}}

          <div class="col-12"><label class="form-label">Supplier </label>
            <input type="text" name="supplier_product_code" class="form-control rounded-3" placeholder="e.g., BBJC65F-L001-PA55">
            <div class="form-text">Matches “Product Code” column in PDF.</div>
          </div>

          <div class="col-12 form-check mt-1">
            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
            <label class="form-check-label" for="is_active">Active</label>
          </div>
        </div>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary rounded-3">Create</button>
      </div>
    </form>
  </div>
</div>

{{-- ===== CSV Upload Modal ===== --}}
<div class="modal fade" id="csvUploadModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form action="{{ route('materials.importCsv', $product->id) }}" method="POST" enctype="multipart/form-data"
          class="modal-content rounded-4 border-0 shadow">
      @csrf
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title">Upload Materials CSV</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-2">CSV columns (header required):</p>
        <code class="d-block small mb-3">ingredient,supplier,product_code,percent_w_w,lbs_per_1k_gal,gal_per_1k_gal,qty,unit,wastage_pct,sort_order</code>
        <input type="file" name="csv" accept=".csv,text/csv" class="form-control rounded-3" required>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary rounded-3">Import</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
  .object-fit-cover{object-fit:cover}
  .table > :not(caption) > * > * { padding: .9rem 1rem; }
  .table tbody tr + tr { border-top: 1px solid rgba(0,0,0,.04); }
  .table thead th { font-weight: 600; letter-spacing: .2px; }
  .card, .modal-content { border-radius: 1rem; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endpush
