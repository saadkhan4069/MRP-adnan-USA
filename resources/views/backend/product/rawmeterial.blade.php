@extends('backend.layout.main')

@section('content')
<div class="container-xxl py-4">
    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger shadow-sm">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Product header card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex gap-3 align-items-center">
            <img src="{{ $product->image ? asset('images/products/'.$product->image) : asset('images/placeholder.png') }}"
                 class="rounded-3" style="width:96px;height:96px;object-fit:cover" alt="{{ $product->name }}">
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h4 class="mb-1 fw-bold">{{ $product->name }}</h4>
                        <div class="text-muted small">
                            <span class="me-3">Code: <strong>{{ $product->code }}</strong></span>
                            <span class="me-3">Category: <strong>{{ optional($product->category)->name ?? '—' }}</strong></span>
                            <span class="me-3">Type: <strong>{{ $product->type ?? '—' }}</strong></span>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Selling Price</div>
                        <div class="fs-5 fw-semibold">{{ number_format($product->price ?? 0, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Materials table card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-basket2 fs-5"></i>
                <h5 class="mb-0">Raw Materials (Recipe)</h5>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMaterialModal">
                <i class="bi bi-plus-circle me-1"></i> Add Material
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th class="text-end">Qty</th>
                            <th>Unit</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Wastage %</th>
                            <th class="text-end">Eff. Qty</th>
                            <th class="text-end">Line Total</th>
                            <th class="text-center" style="width:120px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materials as $i => $row)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $row['model']->name }}</div>
                                    <div class="small text-muted">SKU: {{ $row['model']->sku ?? '—' }}</div>
                                </td>
                                <td class="text-end">{{ rtrim(rtrim(number_format($row['qty'], 6, '.', ''), '0'), '.') }}</td>
                                <td>{{ $row['unit'] }}</td>
                                <td class="text-end">{{ number_format($row['unit_price'], 2) }}</td>
                                <td class="text-end">{{ number_format($row['wastage_pct'], 2) }}</td>
                                <td class="text-end">{{ rtrim(rtrim(number_format($row['effective_qty'], 6, '.', ''), '0'), '.') }}</td>
                                <td class="text-end fw-semibold">{{ number_format($row['line_total'], 2) }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editMaterialModal{{ $row['model']->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('materials.destroy', [$product->id, $row['model']->id]) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this material?')"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>

                            {{-- Edit Modal per-row --}}
                            <div class="modal fade" id="editMaterialModal{{ $row['model']->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <form action="{{ route('materials.update', [$product->id, $row['model']->id]) }}" method="POST" class="modal-content">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit: {{ $row['model']->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-6">
                                                    <label class="form-label">Quantity</label>
                                                    <input type="number" step="0.000001" name="quantity" value="{{ $row['qty'] }}" class="form-control" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Unit</label>
                                                    <input type="text" name="unit" value="{{ $row['unit'] }}" class="form-control" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Unit Price</label>
                                                    <input type="number" step="0.0001" name="unit_price" value="{{ $row['unit_price'] }}" class="form-control" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Wastage %</label>
                                                    <input type="number" step="0.01" name="wastage_pct" value="{{ $row['wastage_pct'] }}" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button class="btn btn-primary">Save changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No raw materials added yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="7" class="text-end">Total Materials Cost</th>
                            <th class="text-end fs-6">{{ number_format($totals['materials_cost'], 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Material Modal --}}
<div class="modal fade" id="addMaterialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="{{ route('materials.store', $product->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Raw Material</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Select Material</label>
                        <select name="raw_material_id" class="form-select" required>
                            <option value="" disabled selected>— Choose —</option>
                            @foreach(\App\Models\RawMaterial::where('is_active',1)->orderBy('name')->get() as $rm)
                                <option value="{{ $rm->id }}">{{ $rm->name }} {{ $rm->sku ? '(' . $rm->sku . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4">
                        <label class="form-label">Quantity</label>
                        <input type="number" step="0.000001" name="quantity" class="form-control" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label">Unit</label>
                        <input type="text" name="unit" value="kg" class="form-control" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label">Unit Price</label>
                        <input type="number" step="0.0001" name="unit_price" class="form-control" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label">Wastage % (optional)</label>
                        <input type="number" step="0.01" name="wastage_pct" value="0" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Add Material</button>
            </div>
        </form>
    </div>
</div>
@endsection

<?php
/** ----------------------------------------------------------------------
 * OPTIONAL: POLISH
 * - Add search/filter on materials
 * - Add export to PDF/Excel buttons
 * - Add batch size scaler to recalc quantities
 * ---------------------------------------------------------------------- */
?>
