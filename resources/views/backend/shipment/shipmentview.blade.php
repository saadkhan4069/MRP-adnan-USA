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

  /* Image View Modal */
  #imageViewModal .modal-body {
    text-align: center;
    padding: 20px;
  }
  #imageViewModal .modal-body img {
    max-width: 100%;
    max-height: 80vh;
    border: 1px solid #ddd;
    border-radius: 5px;
  }
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
      @if(empty($label['provider']) && empty($label['tracking_number']))
        <button id="openCreateLabelBtn" type="button" class="btn btn-success btn-sm"
                data-toggle="modal" data-target="#createLabelModal">
          <i class="fa fa-tag"></i> Create Portal Shipping Label
        </button>
      @else
        <button id="openUpdateLabelBtn" type="button" class="btn btn-warning btn-sm"
                data-toggle="modal" data-target="#createLabelModal">
          <i class="fa fa-edit"></i> Update Portal Label
        </button>
      @endif
      <a href="{{ route('shipment.proforma-invoice', $shipment->id) }}" target="_blank" class="btn btn-primary btn-sm">
        <i class="fa fa-file-pdf-o"></i> Proforma Invoice
      </a>
      {{-- Bill of Lading Buttons --}}
      @php
        $hasBillOfLading = !empty($billOfLading) && (
          !empty($billOfLading['carrier_name']) || 
          !empty($billOfLading['third_party_name']) || 
          !empty($billOfLading['shipper_number']) || 
          !empty($billOfLading['quote_number'])
        );
      @endphp
      @if($hasBillOfLading)
        <button id="openUpdateBillOfLadingBtn" type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#billOfLadingModal">
          <i class="fa fa-edit"></i> Update Bill of Lading
        </button>
        <a href="{{ route('shipment.bill-of-lading', $shipment->id) }}" target="_blank" class="btn btn-info btn-sm">
          <i class="fa fa-file-pdf-o"></i> View Bill of Lading PDF
        </a>
      @else
        <button id="openCreateBillOfLadingBtn" type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#billOfLadingModal">
          <i class="fa fa-file-pdf-o"></i> Bill of Lading
        </button>
      @endif
        
      <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Back</a>
      <a href="{{ url('/shipment/'.$shipment->id.'/edit') }}" class="btn btn-outline-primary btn-sm">Edit</a>
   
    </div>
  </div>

  {{-- Label summary --}}
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Portal Shipping Label</strong>
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
          <div class="fw-semibold">{{ $label['payer'] == 'shipper' ? 'Shipper' : ($label['payer'] == 'receiver' ? 'Receiver' : ($label['payer'] == 'third_party' ? 'Third Party' : '—')) ?? '—' }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Account #</div>
          <div class="fw-semibold">{{ $label['account_number'] ?? '—' }}</div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Signature</div>
          <div class="fw-semibold">{{ $label['signature_option'] == 'direct' ? 'Direct' : ($label['signature_option'] == 'adult' ? 'Adult' : '—') || '—' }}</div>
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
              {{ ($rate['currency'] ?? '') ?? '' }}{{ $currency($rate['amount']) }}
            @else
              —
            @endif
          </div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Pickup Date & Time</div>
          <div class="fw-semibold">
            @if(!empty($label['pickup_date_time']))
              {{ \Carbon\Carbon::parse($label['pickup_date_time'])->format('Y-m-d H:i') }}
            @else
              —
            @endif
          </div>
        </div>
        <div class="col-md-3">
          <div class="text-muted small">Dropoff Date & Time</div>
          <div class="fw-semibold">
            @if(!empty($label['dropoff_date_time']))
              {{ \Carbon\Carbon::parse($label['dropoff_date_time'])->format('Y-m-d H:i') }}
            @else
              —
            @endif
          </div>
        </div>
        {{-- Files --}}
        <div class="col-md-6 d-flex align-items-center">
          <div class="mr-2 text-muted small">Files</div>
          <div class="btn-group btn-group-sm" role="group" aria-label="Label Files">
            @if(!empty($label['provider']) || !empty($label['tracking_number']))
              <a href="{{ route('shipment.portal-label-pdf', $shipment->id) }}" target="_blank" class="btn btn-outline-danger">
                <i class="fa fa-file-pdf-o"></i> Portal Label PDF
              </a>
            @endif
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
            @if(empty($label['provider']) && empty($label['tracking_number']) && empty($label['label_url']) && empty($label['invoice_url']) && empty($label['customs_docs_url']))
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
        <div class="card-header"><strong>Shipper</strong></div>
        <div class="card-body">
          <div>{{ $from ?: '—' }}</div>
          <div class="text-muted small mt-2">
            Contact: {{ $shipment->ship_from_contact ?? '—' }} •
            Email: {{ $shipment->ship_from_email ?? '—' }}
          </div>
          @if($shipment->ship_from_dock_hours)
            <div class="text-muted small mt-1"><strong>Dock Hours:</strong> {{ $shipment->ship_from_dock_hours }}</div>
          @endif
          @if($shipment->ship_from_lunch_hour)
            <div class="text-muted small mt-1"><strong>Lunch Hour:</strong> {{ $shipment->ship_from_lunch_hour }}</div>
          @endif
          @if($shipment->ship_from_pickup_delivery_instructions)
            <div class="text-muted small mt-1"><strong>Pick up / Delivery Instructions:</strong> {{ $shipment->ship_from_pickup_delivery_instructions }}</div>
          @endif
          @if($shipment->ship_from_appointment)
            <div class="text-muted small mt-1"><strong>Appointment:</strong> {{ $shipment->ship_from_appointment }}</div>
          @endif
          @if($shipment->ship_from_accessorial)
            <div class="text-muted small mt-1"><strong>Accessorial:</strong> {{ $shipment->ship_from_accessorial }}</div>
          @endif
        </div>
      </div>
    </div>
    {{-- To --}}
    <div class="col-lg-6">
      <div class="card mb-3 h-100">
        <div class="card-header"><strong>Consignee</strong></div>
        <div class="card-body">
          <div>{{ $to ?: '—' }}</div>
          <div class="text-muted small mt-2">
            Contact: {{ $shipment->ship_to_contact ?? '—' }} •
            Email: {{ $shipment->ship_to_email ?? '—' }}
          </div>
          @if($shipment->ship_to_dock_hours)
            <div class="text-muted small mt-1"><strong>Dock Hours:</strong> {{ $shipment->ship_to_dock_hours }}</div>
          @endif
          @if($shipment->ship_to_lunch_hour)
            <div class="text-muted small mt-1"><strong>Lunch Hour:</strong> {{ $shipment->ship_to_lunch_hour }}</div>
          @endif
          @if($shipment->ship_to_pickup_delivery_instructions)
            <div class="text-muted small mt-1"><strong>Pick up / Delivery Instructions:</strong> {{ $shipment->ship_to_pickup_delivery_instructions }}</div>
          @endif
          @if($shipment->ship_to_appointment)
            <div class="text-muted small mt-1"><strong>Appointment:</strong> {{ $shipment->ship_to_appointment }}</div>
          @endif
          @if($shipment->ship_to_accessorial)
            <div class="text-muted small mt-1"><strong>Accessorial:</strong> {{ $shipment->ship_to_accessorial }}</div>
          @endif
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
              <th>Qty</th>
              <th>Class</th>
              <th>NMFC</th>
              <th>Commodity Name</th>
              <th>single Weight</th>
              <th>total Weight</th>
              <th>Dimensions</th>
              <th>Dim Unit</th>
              <th class="text-end">Declared Value</th>
              <th>Note</th>
            </tr>
          </thead>
          <tbody>
          @php
            $totalQty = 0;
            $totalWeight = 0;
          @endphp
          @forelse($shipment->packages as $i => $p)
            @php
              $totalQty += ($p->qty ?? 1);
              $totalWeight += (float)( $p->qty * $p->weight ?? 0);
            @endphp
            <tr>
              <td>{{ $i+1 }}</td>
              <td>{{ $p->packaging ?? '—' }}</td>
              <td>{{ $p->qty ?? 1 }}</td>
              <td>{{ $p->package_class ?? '—' }}</td>
              <td>{{ $p->package_nmfc ?? '—' }}</td>
              <td>{{ $p->commodity_name ?? '—' }}</td>
              <td>{{ ($p->weight ?? 0) . ' ' . ($p->weight_unit ?? 'kg') }}</td>
              <td>{{ ($p->qty * $p->weight ?? 0) . ' ' . ($p->weight_unit ?? 'kg') }}</td>
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
            <tr><td colspan="11" class="text-muted">No packages</td></tr>
          @endforelse
          @if($shipment->packages->isNotEmpty())
            <tr class="table-info font-weight-bold">
              <td colspan="2" class="text-right"><strong>Total:</strong></td>
              <td><strong>{{ $totalQty }}</strong></td>
              <td colspan="4"></td>
              <td><strong>{{ number_format($totalWeight, 2) }} </strong></td>
              <td colspan="4"></td>
            </tr>
          @endif
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
              $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
              $isExcel = in_array($ext, ['xls', 'xlsx']);
              $isDoc = in_array($ext, ['doc', 'docx']);
              
              // Generate correct URL
              $disk = $f->disk ?? 'public';
              $path = $f->path ?? '';
              if ($path) {
                if ($disk === 'public') {
                  // Check if path starts with 'shipment/' (new format) or 'shipment_attachments/' (old format)
                  if (strpos($path, 'shipment/') === 0) {
                    $fileUrl = asset($path);
                  } else {
                    $fileUrl = asset('storage/' . $path);
                  }
                } else {
                  $fileUrl = \Illuminate\Support\Facades\Storage::disk($disk)->exists($path) 
                    ? \Illuminate\Support\Facades\Storage::disk($disk)->url($path) 
                    : null;
                }
              } else {
                $fileUrl = null;
              }
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
                @if($fileUrl)
                  @if($isImage)
                    <button type="button" class="btn-icon view-image-btn" data-image-url="{{ $fileUrl }}" data-image-name="{{ $f->original_name ?: $f->filename }}" title="View Image">
                      <i class="fa fa-eye"></i>
                    </button>
                  @else
                    <a href="{{ $fileUrl }}" target="_blank" class="btn-icon" title="Open/Download {{ $isExcel ? 'Excel' : ($isDoc ? 'Word' : 'File') }}">
                      <i class="fa fa-{{ $isExcel ? 'file-excel-o' : ($isDoc ? 'file-word-o' : 'download') }}"></i>
                    </a>
                  @endif
                @else
                  <span class="btn-icon text-muted" title="File not found">
                    <i class="fa fa-exclamation-circle"></i>
                  </span>
                @endif
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
                  $ext = strtolower(pathinfo($file->filename, PATHINFO_EXTENSION));
                  $extUpper = strtoupper($ext);
                  $sz  = $humanSize($file->size ?? null);
                  $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                  $isExcel = in_array($ext, ['xls', 'xlsx']);
                  $isDoc = in_array($ext, ['doc', 'docx']);
                  
                  // Generate correct URL
                  $disk = $file->disk ?? 'public';
                  $path = $file->path ?? '';
                  if ($path) {
                    if ($disk === 'public') {
                      // Check if path starts with 'shipment/' (new format) or 'shipment_attachments/' (old format)
                      if (strpos($path, 'shipment/') === 0) {
                        $fileUrl = asset($path);
                      } else {
                        $fileUrl = asset('storage/' . $path);
                      }
                    } else {
                      $fileUrl = \Illuminate\Support\Facades\Storage::disk($disk)->exists($path) 
                        ? \Illuminate\Support\Facades\Storage::disk($disk)->url($path) 
                        : null;
                    }
                  } else {
                    $fileUrl = null;
                  }
                @endphp
                <tr>
                  <td>{{ $i+1 }}</td>
                  <td title="{{ $file->original_name ?: $file->filename }}">
                    {{ \Illuminate\Support\Str::limit($file->original_name ?: $file->filename, 60) }}
                  </td>
                  <td>{{ $file->mime ? strtoupper($file->mime) : $extUpper }}</td>
                  <td class="text-end">{{ $sz }}</td>
                  <td>
                    @if($fileUrl)
                      @if($isImage)
                        <button type="button" class="btn btn-outline-primary btn-sm view-image-btn" data-image-url="{{ $fileUrl }}" data-image-name="{{ $file->original_name ?: $file->filename }}">
                          <i class="fa fa-eye"></i> View
                        </button>
                      @else
                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary btn-sm" title="Open {{ $isExcel ? 'Excel' : ($isDoc ? 'Word' : 'File') }}">
                          <i class="fa fa-{{ $isExcel ? 'file-excel-o' : ($isDoc ? 'file-word-o' : 'download') }}"></i> Open
                        </a>
                      @endif
                    @else
                      <span class="text-muted small">File not found</span>
                    @endif
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
    <form id="createLabelForm" class="modal-content" method="POST" action="{{ route('shipment.label.store', $shipment->id) }}">
      @csrf

      <div class="modal-header">
        <h5 class="modal-title" id="createLabelLabel">
          <i class="fa fa-tag"></i> 
          <span id="labelModalTitle">{{ empty($label['provider']) && empty($label['tracking_number']) ? 'Create' : 'Update' }} Portal Shipping Label</span>
        </h5>
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

          <div class="col-md-6">
            <label class="form-label">Pickup Date & Time</label>
            <input type="datetime-local" name="pickup_date_time" value="{{ $label['pickup_date_time'] ? \Carbon\Carbon::parse($label['pickup_date_time'])->format('Y-m-d\TH:i') : '' }}" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Dropoff Date & Time</label>
            <input type="datetime-local" name="dropoff_date_time" value="{{ $label['dropoff_date_time'] ? \Carbon\Carbon::parse($label['dropoff_date_time'])->format('Y-m-d\TH:i') : '' }}" class="form-control">
          </div>

          <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="2" class="form-control" placeholder="Any special instruction for label">{{ $meta['notes'] ?? '' }}</textarea>
          </div>

          <div class="col-md-6">
            <label class="form-label">Estimated Rate (optional)</label>
            @php
              $rateAmount = null;
              if (!empty($rateBreakdown)) {
                $rateAmount = $rateBreakdown['estimated_rate'] ?? $rateBreakdown['amount'] ?? null;
              }
            @endphp
            <input type="number" step="0.01" name="rate_amount" value="{{ $rateAmount ?? '' }}" class="form-control" placeholder="e.g. 150.00">
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
          <strong>Note:</strong> This is a Portal-generated shipping label. Third-party carrier labels (FEDEX, DHL, UPS, Skynet, etc.) will be available separately when carrier APIs are integrated.
        </p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="saveLabelBtn">
          <i class="fa fa-save"></i> <span id="saveLabelBtnText">Save Portal Label</span>
        </button>
      </div>
    </form>
  </div>
</div>

{{-- Bill of Lading Modal --}}
<div class="modal fade" id="billOfLadingModal" tabindex="-1" role="dialog" aria-labelledby="billOfLadingModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="billOfLadingForm" method="POST" action="{{ route('shipment.bill-of-lading.store', $shipment->id) }}" enctype="multipart/form-data">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="billOfLadingModalLabel">
            <i class="fa fa-file-pdf-o"></i> Bill of Lading - Additional Information
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <h6 class="font-weight-bold">Carrier Information</h6>
              <div class="form-group">
                <label>Carrier Name</label>
                <input type="text" name="carrier_name" class="form-control" placeholder="e.g. TForce Freight" value="{{ $billOfLading['carrier_name'] ?? '' }}">
              </div>
              <div class="form-group">
                <label>Carrier Phone</label>
                <input type="text" name="carrier_phone" class="form-control" placeholder="e.g. 800-333-7400" value="{{ $billOfLading['carrier_phone'] ?? '' }}">
              </div>
              <div class="form-group">
                <label>Carrier Address</label>
                <textarea name="carrier_address" class="form-control" rows="2" placeholder="Carrier full address">{{ $billOfLading['carrier_address'] ?? '' }}</textarea>
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="font-weight-bold">Third Party Information</h6>
              <div class="form-group">
                <label>Third Party Name</label>
                <input type="text" name="third_party_name" class="form-control" placeholder="e.g. InXpress" value="{{ $billOfLading['third_party_name'] ?? '' }}">
              </div>
              <div class="form-group">
                <label>Third Party Account #</label>
                <input type="text" name="third_party_account" class="form-control" placeholder="e.g. 5EX266" value="{{ $billOfLading['third_party_account'] ?? '' }}">
              </div>
              <div class="form-group">
                <label>Third Party Address</label>
                <textarea name="third_party_address" class="form-control" rows="2" placeholder="Third party full address">{{ $billOfLading['third_party_address'] ?? '' }}</textarea>
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="font-weight-bold">Pickup Schedule</h6>
              <div class="form-group">
                <label>Pickup Date</label>
                <input type="date" name="pickup_date" class="form-control" value="{{ $billOfLading['pickup_date'] ?? $shipment->created_at->format('Y-m-d') }}">
              </div>
              <div class="form-group">
                <label>Ready Time</label>
                <input type="text" name="ready_time" class="form-control" placeholder="e.g. 12:00" value="{{ $billOfLading['ready_time'] ?? '12:00' }}">
              </div>
              <div class="form-group">
                <label>Closing Time</label>
                <input type="text" name="closing_time" class="form-control" placeholder="e.g. 04:00" value="{{ $billOfLading['closing_time'] ?? '04:00' }}">
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="font-weight-bold">Reference Information</h6>
              <div class="form-group">
                <label>BOL Number <small class="text-muted">(6 digits, Auto-generated if empty)</small></label>
                <input type="text" name="bol_number" id="bol_number" class="form-control" placeholder="Enter 6 digits (e.g. 123456)" value="{{ $billOfLading['bol_number'] ?? '' }}" maxlength="6" pattern="[0-9]{6}">
                <small class="text-muted" id="bol_number_hint">Leave empty for auto-generation (6 random digits)</small>
                <small class="text-danger d-none" id="bol_number_error"></small>
                <small class="text-success d-none" id="bol_number_success">✓ Available</small>
              </div>
              <div class="form-group">
                <label>Shipper #</label>
                <input type="text" name="shipper_number" class="form-control" value="{{ $billOfLading['shipper_number'] ?? $shipment->po_no ?? '' }}">
              </div>
              <div class="form-group">
                <label>Quote #</label>
                <input type="text" name="quote_number" class="form-control" value="{{ $billOfLading['quote_number'] ?? $shipment->reference_no ?? '' }}">
              </div>
              <div class="form-group">
                <label>Pro Number</label>
                <input type="text" name="pro_number" class="form-control" placeholder="Auto-generated if empty" value="{{ $billOfLading['pro_number'] ?? '' }}">
              </div>
              <div class="form-group">
                <label>Service Type</label>
                <input type="text" name="service_type" class="form-control" value="{{ $billOfLading['service_type'] ?? 'Volume' }}">
              </div>
            </div>
            <div class="col-md-12">
              <div class="form-group">
                <label>Freight Charge Terms</label>
                <input type="text" name="freight_charge_terms" class="form-control" value="{{ $billOfLading['freight_charge_terms'] ?? 'Freight Charges Are Prepaid' }}">
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info">
            <i class="fa fa-save"></i> Save & Generate PDF
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Image View Modal --}}
<div class="modal fade" id="imageViewModal" tabindex="-1" role="dialog" aria-labelledby="imageViewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="imageViewModalLabel">View Image</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <img id="modalImage" src="" alt="" style="display: none;">
        <div id="imageLoading" class="text-center">
          <i class="fa fa-spinner fa-spin fa-3x"></i>
          <p class="mt-2">Loading image...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <a id="downloadImageBtn" href="" download class="btn btn-primary">
          <i class="fa fa-download"></i> Download
        </a>
      </div>
    </div>
  </div>
</div>

@push('scripts')
{{-- AJAX form submission for label creation/update (NO PAGE RELOAD) --}}
<script>
  // Wait for jQuery to be loaded
  (function(){
    function initLabelModal() {
      // Check if jQuery is available
      if (typeof jQuery === 'undefined') {
        setTimeout(initLabelModal, 100);
        return;
      }
      
      var $ = jQuery; // Use jQuery instead of $
      
      var btn = document.getElementById('openCreateLabelBtn') || document.getElementById('openUpdateLabelBtn');
      if (btn && !btn.getAttribute('data-bs-toggle')) {
        btn.setAttribute('data-bs-toggle','modal');
        btn.setAttribute('data-bs-target','#createLabelModal');
      }

      $(document).ready(function() {
    // Update modal title when opening
    $('#createLabelModal').on('show.bs.modal', function() {
      const hasLabel = $('#openUpdateLabelBtn').length > 0;
      $('#labelModalTitle').text(hasLabel ? 'Update Portal Shipping Label' : 'Create Portal Shipping Label');
      $('#saveLabelBtnText').text(hasLabel ? 'Update Portal Label' : 'Save Portal Label');
    });

    // Function to submit form via AJAX (NO PAGE RELOAD)
    function submitLabelForm() {
      var $ = jQuery; // Use jQuery instead of $
      const $form = $('#createLabelForm');
      const $btn = $('#saveLabelBtn');
      const $btnText = $('#saveLabelBtnText');
      const originalBtnText = $btnText.text();
      
      // Validate required fields
      const provider = $form.find('[name="provider"]').val();
      if (!provider) {
        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please select a carrier.',
            confirmButtonText: 'OK'
          });
        } else {
          alert('Please select a carrier.');
        }
        return;
      }
      
      // Disable button and show loading
      $btn.prop('disabled', true);
      $btnText.html('<i class="fa fa-spinner fa-spin"></i> Saving...');
      
      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: $form.serialize(),
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        success: function(response) {
          // Show success message
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: response.message || 'Portal shipping label saved successfully.',
              timer: 2000,
              showConfirmButton: false
            });
          } else {
            alert(response.message || 'Portal shipping label saved successfully.');
          }
          
          // Close modal
          $('#createLabelModal').modal('hide');
          
          // Update UI without page reload
          updateLabelUI($form);
          
          // Re-enable button
          $btn.prop('disabled', false);
          $btnText.text(originalBtnText);
        },
        error: function(xhr) {
          let errorMsg = 'Failed to save label details.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMsg = xhr.responseJSON.message;
          } else if (xhr.responseJSON && xhr.responseJSON.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat();
            errorMsg = errors.join('<br>');
          }
          
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              html: errorMsg,
              confirmButtonText: 'OK'
            });
          } else {
            alert(errorMsg);
          }
          
          // Re-enable button
          $btn.prop('disabled', false);
          $btnText.text(originalBtnText);
        }
      });
    }
    
    // Button click handler (since button type is 'button', not 'submit')
    $('#saveLabelBtn').on('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      submitLabelForm();
    });
    
    // Also handle form submit (in case user presses Enter)
    $('#createLabelForm').on('submit', function(e) {
      e.preventDefault();
      e.stopPropagation();
      submitLabelForm();
    });
    
    // Function to update label UI without page reload
    function updateLabelUI($form) {
      var $ = jQuery; // Use jQuery instead of $
      const formData = $form.serializeArray();
      const data = {};
      formData.forEach(function(item) {
        data[item.name] = item.value;
      });
      
      // Update button: Hide Create, Show Update
      const $createBtn = $('#openCreateLabelBtn');
      const $updateBtn = $('#openUpdateLabelBtn');
      
      if ($createBtn.length && (data.provider || data.tracking_number)) {
        // Hide create button
        $createBtn.hide();
        
        // Show update button if not exists
        if (!$updateBtn.length) {
          $createBtn.after(
            '<button id="openUpdateLabelBtn" type="button" class="btn btn-warning btn-sm" ' +
            'data-toggle="modal" data-target="#createLabelModal" style="margin-left: 8px;">' +
            '<i class="fa fa-edit"></i> Update Portal Label</button>'
          );
        } else {
          $updateBtn.show();
        }
      }
      
      // Update label card header
      const provider = data.provider ? data.provider.toUpperCase() : '—';
      const serviceName = data.service_name || data.service_code || '';
      $('.card-header strong').first().text('Portal Shipping Label');
      $('.card-header .small-muted').first().html(
        provider + (serviceName ? ' · ' + serviceName : '')
      );
      
      // Update label details in the card body
      updateLabelDetails(data);
    }
    
    // Function to update label details section
    function updateLabelDetails(data) {
      var $ = jQuery; // Use jQuery instead of $
      const $cardBody = $('.card-body').first();
      const $rows = $cardBody.find('.row');
      
      // Update Carrier (first row, first col)
      if (data.provider) {
        $rows.eq(0).find('.col-md-3').eq(0).find('.fw-semibold').text(data.provider.toUpperCase());
      }
      
      // Update Service (first row, second col)
      if (data.service_name || data.service_code) {
        $rows.eq(0).find('.col-md-3').eq(1).find('.fw-semibold').text(data.service_name || data.service_code || '—');
      }
      
      // Update Tracking # (first row, third col)
      if (data.tracking_number) {
        let trackingHtml = data.tracking_number;
        // Add tracking URL if provider is known
        if (data.provider) {
          const provider = data.provider.toLowerCase();
          let trackingUrl = '';
          if (provider === 'dhl') {
            trackingUrl = 'https://www.dhl.com/global-en/home/tracking/tracking-express.html?tracking-id=' + data.tracking_number;
          } else if (provider === 'ups') {
            trackingUrl = 'https://www.ups.com/track?loc=en_US&tracknum=' + data.tracking_number;
          } else if (provider === 'fedex') {
            trackingUrl = 'https://www.fedex.com/fedextrack/?tracknumbers=' + data.tracking_number;
          }
          if (trackingUrl) {
            trackingHtml += ' <a href="' + trackingUrl + '" target="_blank" class="badge badge-info ml-1">Track</a>';
          }
        }
        $rows.eq(0).find('.col-md-3').eq(2).find('.fw-semibold').html(trackingHtml);
      }
      
      // Update Label Format (first row, fourth col)
      if (data.label_format) {
        $rows.eq(0).find('.col-md-3').eq(3).find('.fw-semibold').text(data.label_format);
      }
      
      // Update Payer (second row, first col)
      if (data.payer) {
        const payerText = data.payer === 'shipper' ? 'Shipper' : 
                         (data.payer === 'receiver' ? 'Receiver' : 
                         (data.payer === 'third_party' ? 'Third Party' : '—'));
        $rows.eq(1).find('.col-md-3').eq(0).find('.fw-semibold').text(payerText);
      }
      
      // Update Account # (second row, second col)
      if (data.account_number) {
        $rows.eq(1).find('.col-md-3').eq(1).find('.fw-semibold').text(data.account_number);
      }
      
      // Update Signature (second row, third col)
      if (data.signature_option) {
        $rows.eq(1).find('.col-md-3').eq(2).find('.fw-semibold').text(data.signature_option);
      }
      
      // Update Saturday Delivery (second row, fourth col)
      if (data.saturday_delivery !== undefined) {
        $rows.eq(1).find('.col-md-3').eq(3).find('.fw-semibold').text(data.saturday_delivery == '1' ? 'Yes' : 'No');
      }
      
      // Update Declared Value (third row, first col)
      if (data.declared_value_total) {
        $rows.eq(2).find('.col-md-3').eq(0).find('.fw-semibold').text(parseFloat(data.declared_value_total).toFixed(2));
      }
      
      // Update Estimated Rate (third row, second col)
      if (data.rate_amount) {
        const currency = data.currency || '';
        $rows.eq(2).find('.col-md-3').eq(1).find('.fw-semibold').text(
          (currency ? currency + ' ' : '') + parseFloat(data.rate_amount).toFixed(2)
        );
      }
      
      // Update Pickup Date & Time (fourth row, first col)
      if (data.pickup_date_time) {
        const pickupDate = new Date(data.pickup_date_time);
        const pickupFormatted = pickupDate.getFullYear() + '-' + 
          String(pickupDate.getMonth() + 1).padStart(2, '0') + '-' + 
          String(pickupDate.getDate()).padStart(2, '0') + ' ' +
          String(pickupDate.getHours()).padStart(2, '0') + ':' +
          String(pickupDate.getMinutes()).padStart(2, '0');
        $rows.eq(3).find('.col-md-3').eq(0).find('.fw-semibold').text(pickupFormatted);
      }
      
      // Update Dropoff Date & Time (fourth row, second col)
      if (data.dropoff_date_time) {
        const dropoffDate = new Date(data.dropoff_date_time);
        const dropoffFormatted = dropoffDate.getFullYear() + '-' + 
          String(dropoffDate.getMonth() + 1).padStart(2, '0') + '-' + 
          String(dropoffDate.getDate()).padStart(2, '0') + ' ' +
          String(dropoffDate.getHours()).padStart(2, '0') + ':' +
          String(dropoffDate.getMinutes()).padStart(2, '0');
        $rows.eq(3).find('.col-md-3').eq(1).find('.fw-semibold').text(dropoffFormatted);
      }
      
      // Update meta section (Reference, Currency, Notes)
      if (data.reference || data.currency || data.notes) {
        let metaHtml = '<hr><div class="row g-3">';
        if (data.reference) {
          metaHtml += '<div class="col-md-4"><div class="text-muted small">Reference</div><div class="fw-semibold">' + data.reference + '</div></div>';
        }
        if (data.currency) {
          metaHtml += '<div class="col-md-4"><div class="text-muted small">Currency</div><div class="fw-semibold">' + data.currency + '</div></div>';
        }
        if (data.notes) {
          metaHtml += '<div class="col-md-12"><div class="text-muted small">Notes</div><div>' + data.notes + '</div></div>';
        }
        metaHtml += '</div>';
        
        // Remove existing meta section if any
        $cardBody.find('hr').next('.row').remove();
        $cardBody.find('hr').remove();
        
        // Add new meta section before closing card-body
        $rows.last().after(metaHtml);
      }
    }
      });
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initLabelModal);
    } else {
      initLabelModal();
    }
  })();

  // Image view functionality for attachments
  $(document).ready(function() {
    $(document).on('click', '.view-image-btn', function() {
      const imageUrl = $(this).data('image-url');
      const imageName = $(this).data('image-name') || 'image';
      
      $('#modalImage').attr('src', imageUrl).attr('alt', imageName);
      $('#modalImage').attr('style', 'display: none;');
      $('#imageLoading').show();
      $('#downloadImageBtn').attr('href', imageUrl).attr('download', imageName);
      
      $('#imageViewModal').modal('show');
      
      // Show image when loaded
      $('#modalImage').off('load error').on('load', function() {
        $('#imageLoading').hide();
        $(this).show();
      }).on('error', function() {
        $('#imageLoading').html('<p class="text-danger">Failed to load image. Please try downloading instead.</p>');
      });
    });
    
    // Reset modal when closed
    $('#imageViewModal').on('hidden.bs.modal', function() {
      $('#modalImage').attr('src', '').hide();
      $('#imageLoading').show().html('<i class="fa fa-spinner fa-spin fa-3x"></i><p class="mt-2">Loading image...</p>');
    });
  });

  // Bill of Lading Form AJAX Submission
  $(document).ready(function() {
    // BOL Number duplicate check on input
    let bolCheckTimeout;
    $('#bol_number').on('input', function() {
      const $input = $(this);
      const bolNumber = $input.val().trim();
      const $hint = $('#bol_number_hint');
      const $error = $('#bol_number_error');
      const $success = $('#bol_number_success');
      
      // Hide previous messages
      $error.addClass('d-none');
      $success.addClass('d-none');
      
      // Only check if 6 digits entered
      if (bolNumber.length === 6 && /^\d{6}$/.test(bolNumber)) {
        clearTimeout(bolCheckTimeout);
        $hint.text('Checking availability...');
        
        bolCheckTimeout = setTimeout(function() {
          $.ajax({
            url: '{{ route("shipment.bill-of-lading.check", $shipment->id) }}',
            method: 'POST',
            data: {
              bol_number: bolNumber,
              _token: '{{ csrf_token() }}'
            },
            success: function(response) {
              if (response.available) {
                $hint.addClass('d-none');
                $error.addClass('d-none');
                $success.removeClass('d-none').text('✓ Available');
                $input.removeClass('is-invalid').addClass('is-valid');
              } else {
                $hint.addClass('d-none');
                $success.addClass('d-none');
                $error.removeClass('d-none').text('✗ This BOL number already exists');
                $input.removeClass('is-valid').addClass('is-invalid');
              }
            },
            error: function() {
              $hint.text('Leave empty for auto-generation (6 random digits)');
            }
          });
        }, 500); // Debounce 500ms
      } else if (bolNumber.length > 0) {
        $hint.text('Please enter exactly 6 digits');
        $input.removeClass('is-valid is-invalid');
      } else {
        $hint.text('Leave empty for auto-generation (6 random digits)');
        $input.removeClass('is-valid is-invalid');
      }
    });
    
    $('#billOfLadingForm').on('submit', function(e) {
      e.preventDefault();
      var $form = $(this);
      var $btn = $form.find('button[type="submit"]');
      var originalBtnText = $btn.html();
      
      // Disable button
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
      
      // Create FormData for file uploads
      var formData = new FormData(this);
      
      $.ajax({
        url: $form.attr('action'),
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: response.message || 'Bill of Lading information saved successfully.',
              timer: 2000,
              showConfirmButton: false
            }).then(function() {
              // Open PDF in new tab
              if (response.pdf_url) {
                window.open(response.pdf_url, '_blank');
              } else {
                window.open('{{ route("shipment.bill-of-lading", $shipment->id) }}', '_blank');
              }
              // Reload page to show updated data
              location.reload();
            });
          } else {
            alert(response.message || 'Bill of Lading information saved successfully.');
            window.open('{{ route("shipment.bill-of-lading", $shipment->id) }}', '_blank');
            location.reload();
          }
        },
        error: function(xhr) {
          let errorMsg = 'Failed to save Bill of Lading information.';
          if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMsg = xhr.responseJSON.message;
          } else if (xhr.responseJSON && xhr.responseJSON.errors) {
            const errors = Object.values(xhr.responseJSON.errors).flat();
            errorMsg = errors.join('<br>');
          }
          
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              html: errorMsg,
              confirmButtonText: 'OK'
            });
          } else {
            alert(errorMsg);
          }
          
          // Re-enable button
          $btn.prop('disabled', false).html(originalBtnText);
        }
      });
    });
  });
</script>
@endpush

@endsection
