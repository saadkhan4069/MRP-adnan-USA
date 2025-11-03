@extends('backend.layout.main')

@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<style>
  .status-badge{font-size:12px;padding:.25rem .5rem;border-radius:999px}
  .status-1{background:#fff3cd;color:#856404;border:1px solid #ffeeba}      /* Pending */
  .status-2{background:#cce5ff;color:#004085;border:1px solid #b8daff}      /* In Transit */
  .status-3{background:#d4edda;color:#155724;border:1px solid #c3e6cb}      /* Delivered */
  .status-4{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}      /* Returned */
  .status-5{background:#eee;color:#6c757d;border:1px solid #ddd}            /* Cancelled */
  .table-sm td, .table-sm th{padding:.5rem .6rem}
  .small-muted{font-size:12px;color:#6c757d}

  /* Attachments UI (list + chips) */
  :root{
    --pri:#981a1c;
    --muted:#6c757d;
    --bd:#e9ecef;
  }
  .file-grid{display:grid;grid-template-columns:1fr;gap:10px}
  @media(min-width:768px){.file-grid{grid-template-columns:1fr 1fr}}
  .file-pill{display:flex;align-items:center;gap:10px;border:1px solid var(--bd);border-radius:12px;padding:10px;background:#fff}
  .file-meta{flex:1 1 auto;min-width:0}
  .file-name{font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .file-sub{font-size:12px;color:var(--muted)}
  .file-badge{font-size:11px;border:1px solid var(--bd);padding:2px 8px;border-radius:999px;white-space:nowrap}
  .file-actions{display:flex;gap:6px}
  .btn-icon{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #dee2e6;border-radius:10px;background:#fff;cursor:pointer}
  .btn-icon:hover{background:#f8f9fa}
</style>

@php
  $s = $status ?? ['label'=>'—','cls'=>'status-5'];
  $currency = function($n){ $v = is_null($n) ? 0 : (float)$n; return number_format($v, 2, '.', ','); };

  // Label helpers from $label array
  $meta = $label['meta'] ?? null;
  $rate = null;
  if (!empty($label['rate_breakdown'])) {
      $rb = $label['rate_breakdown'];
      $rate = [
        'amount'   => $rb['amount']   ?? null,
        'currency' => $rb['currency'] ?? null,
      ];
  }
  $trackingUrl = $trackingUrl ?? null;
@endphp

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Shipment #{{ $shipment->id }}</h5>
    <div class="d-flex align-items-center" style="gap:8px">
      <span class="status-badge {{ $s['cls'] }}">{{ $s['label'] }}</span>

      {{-- Create/Update Shipping Label --}}
      <button id="openCreateLabelBtn" type="button" class="btn btn-success btn-sm"
              data-toggle="modal" data-target="#createLabelModal">
        <i class="fa fa-tag"></i> Create Shipping Label
      </button>
      <button onclick="window.print()" class="btn btn-dark btn-sm">
        <i class="fa fa-print"></i> Print
      </button>
        
      <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Back</a>
      <a href="{{ url('/shipment/'.$shipment->id.'/edit') }}" class="btn btn-outline-primary btn-sm">Edit</a>
   
    </div>
  </div>

  {{-- Label summary --}}
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Label</strong>
      <span class="small-muted">
        {{ strtoupper($label['provider'] ?? '—') }}
        {{ ($label['service_name'] ?? null) ? ' · '.$label['service_name'] : (($label['service_code'] ?? null) ? ' · '.$label['service_code'] : '') }}
      </span>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <div class="text-muted small">Carrier</div>
          <div class="fw-semibold">{{ strtoupper($label['provider'] ?? '—') }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Service</div>
          <div class="fw-semibold">{{ $label['service_name'] ?? $label['service_code'] ?? '—' }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Tracking #</div>
          <div class="fw-semibold">
            @if(!empty($label['tracking_number']))
              {{ $label['tracking_number'] }}
              @if($trackingUrl)
                <a href="{{ $trackingUrl }}" target="_blank" class="badge badge-info ml-1">Track</a>
              @endif
            @else
              —
            @endif
          </div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Label Format</div>
          <div class="fw-semibold">{{ $label['label_format'] ?? '—' }}</div>
        </div>

        <div class="col-md-3">
          <div class="text-muted small">Payer</div>
          <div class="fw-semibold">{{ $label['payer'] ?? '—' }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Account #</div>
          <div class="fw-semibold">{{ $label['account_number'] ?? '—' }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Signature</div>
          <div class="fw-semibold">{{ $label['signature_option'] ?? '—' }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Saturday Delivery</div>
          <div class="fw-semibold">{{ !empty($label['saturday_delivery']) ? 'Yes' : 'No' }}</div>
        </div>

        <div class="col-md-3">
          <div class="text-muted small">Declared Value</div>
          <div class="fw-semibold">{{ $currency($label['declared_value_total'] ?? 0) }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Estimated Rate</div>
          <div class="fw-semibold">
            @if(!empty($rate['amount']))
              {{ ($rate['currency'] ?? '') ? $rate['currency'].' ' : '' }}{{ $currency($rate['amount']) }}
            @else
              —
            @endif
          </div>
        </div>

        <div class="col-md-6 d-flex align-items-center">
          <div class="mr-2 text-muted small">Files</div>
          <div class="btn-group btn-group-sm" role="group" aria-label="Label Files">
            @if(!empty($label['label_url']))
              <a target="_blank" href="{{ $label['label_url'] }}" class="btn btn-outline-success">
                <i class="fa fa-download"></i> Label
              </a>
            @endif
            @if(!empty($label['invoice_url']))
              <a target="_blank" href="{{ $label['invoice_url'] }}" class="btn btn-outline-primary">
                <i class="fa fa-file"></i> Invoice
              </a>
            @endif
            @if(!empty($label['customs_docs_url']))
              <a target="_blank" href="{{ $label['customs_docs_url'] }}" class="btn btn-outline-secondary">
                <i class="fa fa-file-text-o"></i> Customs
              </a>
            @endif
            @if(empty($label['label_url']) && empty($label['invoice_url']) && empty($label['customs_docs_url']))
              <span class="small text-muted">—</span>
            @endif
          </div>
        </div>
      </div>

      @if(!empty($meta['notes']) || !empty($meta['reference']) || !empty($meta['currency']))
        <hr>
        <div class="row g-3">
          <div class="col-md-4">
            <div class="text-muted small">Reference</div>
            <div class="fw-semibold">{{ $meta['reference'] ?? '—' }}</div>
          </div>
          <div class="col-md-4">
            <div class="text-muted small">Currency</div>
            <div class="fw-semibold">{{ $meta['currency'] ?? '—' }}</div>
          </div>
          <div class="col-md-12">
            <div class="text-muted small">Notes</div>
            <div>{{ $meta['notes'] ?? '—' }}</div>
          </div>
        </div>
      @endif
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <div class="text-muted small">Reference</div>
          <div class="fw-semibold">{{ $shipment->reference_no ?? '—' }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">PO No</div>
          <div class="fw-semibold">{{ $shipment->po_no ?? '—' }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Buyer</div>
          <div class="fw-semibold">
            @if($shipment->customer)
              {{ $shipment->customer->name }} {{ $shipment->customer->company_name ? '(' . $shipment->customer->company_name . ')' : '' }}
            @else
              —
            @endif
          </div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Created</div>
          <div class="fw-semibold">{{ optional($shipment->created_at)->format('Y-m-d H:i') }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    {{-- From --}}
    <div class="col-lg-6">
      <div class="card mb-3 h-100">
        <div class="card-header"><strong>From (Shipper)</strong></div>
        <div class="card-body">
          <div>{{ $from ?: '—' }}</div>
          <div class="text-muted small mt-2">
            Contact: {{ $shipment->ship_from_contact ?? '—' }} •
            Email: {{ $shipment->ship_from_email ?? '—' }}
          </div>
        </div>
      </div>
    </div>
    {{-- To --}}
    <div class="col-lg-6">
      <div class="card mb-3 h-100">
        <div class="card-header"><strong>To (Recipient)</strong></div>
        <div class="card-body">
          <div>{{ $to ?: '—' }}</div>
          <div class="text-muted small mt-2">
            Contact: {{ $shipment->ship_to_contact ?? '—' }} •
            Email: {{ $shipment->ship_to_email ?? '—' }}
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Packages --}}
  <div class="card mb-3">
    <div class="card-header"><strong>Packages</strong></div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0">
          <thead>
            <tr>
              <th style="width:60px">#</th>
              <th>Packaging</th>
              <th>Weight</th>
              <th>Dimensions</th>
              <th>Dim Unit</th>
              <th class="text-end">Declared Value</th>
              <th>Note</th>
            </tr>
          </thead>
          <tbody>
          @forelse($shipment->packages as $i => $p)
            <tr>
              <td>{{ $i+1 }}</td>
              <td>{{ $p->packaging ?? '—' }}</td>
              <td>{{ ($p->weight ?? 0) . ' ' . ($p->weight_unit ?? 'kg') }}</td>
              <td>
                @php
                  $dims = array_filter([$p->length, $p->width, $p->height]);
                  echo $dims ? implode(' x ', $dims) : '—';
                @endphp
              </td>
              <td>{{ $p->dim_unit ?? 'cm' }}</td>
              <td class="text-end">{{ $currency($p->declared_value) }}</td>
              <td>{{ $p->dimensions_note ?? '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-muted">No packages</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Items --}}
  <div class="card mb-3">
    <div class="card-header"><strong>Items</strong></div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-sm mb-0">
          <thead>
            <tr>
              <th style="width:60px">#</th>
              <th>Product Code</th>
              <th>Qty</th>
              <th>Unit</th>
              <th class="text-end">Unit Cost</th>
              <th class="text-end">Discount</th>
              <th class="text-end">Subtotal</th>
            </tr>
          </thead>
          <tbody>
          @forelse($shipment->items as $i => $it)
            <tr>
              <td>{{ $i+1 }}</td>
              <td>{{ $it->product_code ?? '—' }}</td>
              <td>{{ $it->qty ?? 0 }}</td>
              <td>{{ $it->product_unit ?? '—' }}</td>
              <td class="text-end">{{ $currency($it->net_unit_cost) }}</td>
              <td class="text-end">{{ $currency($it->discount) }}</td>
              <td class="text-end">{{ $currency($it->subtotal) }}</td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-muted">No items</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Totals --}}
  <div class="card mb-3">
    <div class="card-header"><strong>Totals</strong></div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-sm-3">
          <div class="text-muted small">Items (count)</div>
          <div class="fw-semibold">{{ $shipment->item }}</div>
        </div>
        <div class="col-sm-3">
          <div class="text-muted small">Total Qty</div>
          <div class="fw-semibold">{{ $shipment->total_qty }}</div>
        </div>
        <div class="col-sm-3">
          <div class="text-muted small">Subtotal</div>
          <div class="fw-semibold">{{ $currency($shipment->total_cost) }}</div>
        </div>
        <div class="col-sm-3">
          <div class="text-muted small">Order Tax</div>
          <div class="fw-semibold">{{ $currency($shipment->order_tax) }}</div>
        </div>
        <div class="col-sm-3">
          <div class="text-muted small">Shipping</div>
          <div class="fw-semibold">{{ $currency($shipment->shipping_cost) }}</div>
        </div>
        <div class="col-sm-3">
          <div class="text-muted small">Discount</div>
          <div class="fw-semibold">{{ $currency($shipment->order_discount) }}</div>
        </div>
        <div class="col-sm-3">
          <div class="text-muted small">Grand Total</div>
          <div class="fw-bold">{{ $currency($shipment->grand_total) }}</div>
        </div>
      </div>
    </div>
  </div>

  {{-- Attachments (NEW) --}}
  <div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Attachments</strong>
      <span class="small-muted">
        {{ $shipment->attachments->count() }} file{{ $shipment->attachments->count() === 1 ? '' : 's' }}
      </span>
    </div>
    <div class="card-body">
      @if($shipment->attachments->isEmpty())
        <p class="text-muted mb-0">No attachments uploaded for this shipment.</p>
      @else
        {{-- Grid pills (nice visual) --}}
        <div class="file-grid mb-3">
          @foreach($shipment->attachments as $f)
            @php
              $ext = strtolower(pathinfo($f->filename, PATHINFO_EXTENSION));
              $size = $humanSize($f->size ?? null);
            @endphp
            <div class="file-pill">
              <div><i class="fa fa-file-o"></i></div>
              <div class="file-meta">
                <div class="file-name" title="{{ $f->original_name ?: $f->filename }}">
                  {{ \Illuminate\Support\Str::limit($f->original_name ?: $f->filename, 48) }}
                </div>
                <div class="file-sub">{{ strtoupper($f->mime ?? $ext) }} • {{ $size }}</div>
              </div>
              <span class="file-badge">.{{ $ext ?: 'file' }}</span>
              <div class="file-actions">
                <a href="{{ asset($f->path) }}" target="_blank" class="btn-icon" title="Open/Download">
                  <i class="fa fa-download"></i>
                </a>
                {{-- Optional: delete button (uncomment and define route)
                <form action="{{ route('shipment.attachment.delete', [$shipment->id, $f->id]) }}" method="POST" onsubmit="return confirm('Delete this file?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn-icon" title="Delete">
                    <i class="fa fa-trash"></i>
                  </button>
                </form>
                --}}
              </div>
            </div>
          @endforeach
        </div>

        {{-- Table view (compact summary) --}}
        <div class="table-responsive">
          <table class="table table-bordered table-sm mb-0 align-middle">
            <thead>
              <tr>
                <th style="width:50px">#</th>
                <th>File Name</th>
                <th>Type</th>
                <th class="text-end">Size</th>
                <th style="width:120px">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($shipment->attachments as $i => $file)
                @php
                  $ext = strtoupper(pathinfo($file->filename, PATHINFO_EXTENSION));
                  $sz  = $humanSize($file->size ?? null);
                @endphp
                <tr>
                  <td>{{ $i+1 }}</td>
                  <td title="{{ $file->original_name ?: $file->filename }}">
                    {{ \Illuminate\Support\Str::limit($file->original_name ?: $file->filename, 60) }}
                  </td>
                  <td>{{ $file->mime ? strtoupper($file->mime) : $ext }}</td>
                  <td class="text-end">{{ $sz }}</td>
                  <td>
                    <a href="{{ asset($file->path) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                      <i class="fa fa-download"></i> Open
                    </a>
                    {{-- Optional delete (uncomment if route exists)
                    <form action="{{ route('shipment.attachment.delete', [$shipment->id, $file->id]) }}" method="POST" style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
                    --}}
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>

  {{-- Comments --}}
  @if(!empty($shipment->comments))
    <div class="card mb-4">
      <div class="card-header"><strong>Comments / Instructions</strong></div>
      <div class="card-body">{{ $shipment->comments }}</div>
    </div>
  @endif

</div>

{{-- ===================== MODAL (Create/Update Label) ===================== --}}
<div class="modal fade" id="createLabelModal" tabindex="-1" role="dialog" aria-labelledby="createLabelLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form class="modal-content" method="POST" action="{{ route('shipment.label.store', $shipment->id) }}">
      @csrf

      <div class="modal-header">
        <h5 class="modal-title" id="createLabelLabel"><i class="fa fa-tag"></i> Create Shipping Label</h5>
        {{-- Bootstrap 4 close --}}
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Carrier *</label>
            <select name="provider" id="provider" class="form-control" required>
              <option value="" disabled {{ ($label['provider'] ?? null) ? '' : 'selected' }}>Select</option>
              <option value="dhl"   {{ ($label['provider'] ?? null)==='dhl'   ? 'selected' : '' }}>DHL</option>
              <option value="ups"   {{ ($label['provider'] ?? null)==='ups'   ? 'selected' : '' }}>UPS</option>
              <option value="fedex" {{ ($label['provider'] ?? null)==='fedex' ? 'selected' : '' }}>FedEx</option>
              <option value="other" {{ ($label['provider'] ?? null)==='other' ? 'selected' : '' }}>Other</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Service (Code)</label>
            <input type="text" name="service_code" value="{{ $label['service_code'] ?? '' }}" class="form-control" placeholder="e.g. EXPRESS_WORLDWIDE / 2ND_DAY">
          </div>

          <div class="col-md-4">
            <label class="form-label">Service Name</label>
            <input type="text" name="service_name" value="{{ $label['service_name'] ?? '' }}" class="form-control" placeholder="e.g. Express Worldwide">
          </div>

          <div class="col-md-4">
            <label class="form-label">Payer</label>
            <select name="payer" class="form-control">
              <option value="" {{ empty($label['payer']) ? 'selected' : '' }}>Select</option>
              <option value="shipper"     {{ ($label['payer'] ?? '')==='shipper'     ? 'selected' : '' }}>Shipper</option>
              <option value="receiver"    {{ ($label['payer'] ?? '')==='receiver'    ? 'selected' : '' }}>Receiver</option>
              <option value="third_party" {{ ($label['payer'] ?? '')==='third_party' ? 'selected' : '' }}>Third Party</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Account #</label>
            <input type="text" name="account_number" value="{{ $label['account_number'] ?? '' }}" class="form-control" placeholder="Carrier Account">
          </div>

          <div class="col-md-4">
            <label class="form-label">Signature Option</label>
            <select name="signature_option" class="form-control">
              <option value=""       {{ empty($label['signature_option']) ? 'selected' : '' }}>None</option>
              <option value="direct" {{ ($label['signature_option'] ?? '')==='direct' ? 'selected' : '' }}>Direct</option>
              <option value="adult"  {{ ($label['signature_option'] ?? '')==='adult'  ? 'selected' : '' }}>Adult</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Saturday Delivery?</label>
            <select name="saturday_delivery" class="form-control">
              <option value="0" {{ empty($label['saturday_delivery']) ? 'selected' : '' }}>No</option>
              <option value="1" {{ !empty($label['saturday_delivery']) ? 'selected' : '' }}>Yes</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Declared Value (Total)</label>
            <input type="number" step="0.01" name="declared_value_total" value="{{ $label['declared_value_total'] ?? '' }}" class="form-control" placeholder="0.00">
          </div>

          <div class="col-md-4">
            <label class="form-label">Currency</label>
            <input type="text" name="currency" value="{{ $meta['currency'] ?? '' }}" class="form-control" placeholder="e.g. USD / PKR">
          </div>

          <div class="col-md-6">
            <label class="form-label">Reference (on label)</label>
            <input type="text" name="reference" value="{{ $meta['reference'] ?? ($shipment->reference_no ?? '') }}" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Tracking # (optional)</label>
            <input type="text" name="tracking_number" value="{{ $label['tracking_number'] ?? '' }}" class="form-control" placeholder="If already assigned">
          </div>

          <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="2" class="form-control" placeholder="Any special instruction for label">{{ $meta['notes'] ?? '' }}</textarea>
          </div>

          <div class="col-md-6">
            <label class="form-label">Estimated Rate (optional)</label>
            <input type="number" step="0.01" name="rate_amount" value="{{ $rate['amount'] ?? '' }}" class="form-control" placeholder="e.g. 150.00">
          </div>

          <div class="col-md-6">
            <label class="form-label">Label Format</label>
            <select name="label_format" class="form-control">
              <option value=""    {{ empty($label['label_format']) ? 'selected' : '' }}>Auto</option>
              <option value="PDF" {{ ($label['label_format'] ?? '')==='PDF' ? 'selected' : '' }}>PDF</option>
              <option value="ZPL" {{ ($label['label_format'] ?? '')==='ZPL' ? 'selected' : '' }}>ZPL</option>
              <option value="PNG" {{ ($label['label_format'] ?? '')==='PNG' ? 'selected' : '' }}>PNG</option>
            </select>
          </div>
        </div>

        <p class="small-muted mt-2">
          This will save label fields on the shipment (tracking, label URL, etc.). You can hook your carrier API later.
        </p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success">
          <i class="fa fa-save"></i> Save Label
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Optional tiny fallback so it also works if the project is on Bootstrap 5 --}}
<script>
  (function(){
    var btn = document.getElementById('openCreateLabelBtn');
    if (btn && !btn.getAttribute('data-bs-toggle')) {
      btn.setAttribute('data-bs-toggle','modal');
      btn.setAttribute('data-bs-target','#createLabelModal');
    }
  })();
</script>

@endsection
