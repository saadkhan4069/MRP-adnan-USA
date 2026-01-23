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
  
  .info-card { border: 1px solid #e9ecef; border-radius: 8px; padding: 16px; margin-bottom: 16px; }
  .info-card h6 { color: #981a1c; margin-bottom: 12px; border-bottom: 1px solid #981a1c; padding-bottom: 8px; }
  .info-row { display: flex; justify-content: space-between; margin-bottom: 8px; }
  .info-label { font-weight: 600; color: #6c757d; }
  .info-value { color: #212529; }
  
  .log-entry { border-left: 3px solid #981a1c; padding-left: 12px; margin-bottom: 12px; }
  .log-time { font-size: 12px; color: #6c757d; }
  .log-action { font-weight: 600; color: #981a1c; }
  .log-description { margin-top: 4px; }
  
  .package-card { border: 1px solid #e9ecef; border-radius: 8px; padding: 12px; margin-bottom: 12px; }
  .package-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
  .package-number { background: #981a1c; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
  
  .item-row { border-bottom: 1px solid #f1f3f5; padding: 8px 0; }
  .item-row:last-child { border-bottom: none; }
</style>

<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      
      <!-- Header -->
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Shipment #{{ $shipment->id }}</h5>
          <div>
            <button id="openCreateLabelBtn" type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#createLabelModal">
              <i class="fa fa-tag"></i> Create Shipping Label
            </button>
            <a href="{{ route('shipment.edit', $shipment->id) }}" class="btn btn-warning btn-sm">
              <i class="fa fa-edit"></i> Edit
            </a>
            <a href="{{ route('shipment.index') }}" class="btn btn-secondary btn-sm">
              <i class="fa fa-arrow-left"></i> Back
            </a>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3">
              <div class="info-card">
                <h6>Status</h6>
                <span class="status-badge status-{{ $shipment->status }}">
                  {{ $shipment->status_text }}
                </span>
              </div>
            </div>
            <div class="col-md-3">
              <div class="info-card">
                <h6>Reference</h6>
                <div class="info-value">{{ $shipment->reference_no ?: '—' }}</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="info-card">
                <h6>PO Number</h6>
                <div class="info-value">{{ $shipment->po_no ?: '—' }}</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="info-card">
                <h6>Created</h6>
                <div class="info-value">{{ $shipment->created_at->format('M d, Y H:i') }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
          
          <!-- Customer & Financial Info -->
          <div class="card mb-3">
            <div class="card-header">
              <h6 class="mb-0">Customer & Financial Information</h6>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="info-card">
                    <h6>Customer</h6>
                    <div class="info-value">{{ $shipment->customer->name ?? '—' }}</div>
                    @if($shipment->customer && $shipment->customer->company_name)
                      <div class="text-muted small">{{ $shipment->customer->company_name }}</div>
                    @endif
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="info-card">
                    <h6>Currency & Rate</h6>
                    <div class="info-value">
                      {{ $shipment->currency->code ?? '—' }} @ {{ $shipment->exchange_rate }}
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-3">
                  <div class="info-card">
                    <h6>Items (Qty)</h6>
                    <div class="info-value">{{ $shipment->item }} ({{ $shipment->total_qty }})</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="info-card">
                    <h6>Subtotal</h6>
                    <div class="info-value">{{ number_format($shipment->total_cost, 2) }}</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="info-card">
                    <h6>Tax</h6>
                    <div class="info-value">{{ number_format($shipment->total_tax, 2) }}</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="info-card">
                    <h6>Grand Total</h6>
                    <div class="info-value fw-bold">{{ number_format($shipment->grand_total, 2) }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Addresses -->
          <div class="row">
            <div class="col-md-6">
              <div class="card mb-3">
                <div class="card-header">
                  <h6 class="mb-0">Shipper (From)</h6>
                </div>
                <div class="card-body">
                  @if($shipment->ship_from_company)
                    <div class="fw-bold">{{ $shipment->ship_from_company }}</div>
                  @endif
                  <div>{{ $shipment->ship_from_first_name }}</div>
                  <div>{{ $shipment->ship_from_address_1 }}</div>
                  <div>{{ $shipment->ship_from_city }}, {{ $shipment->ship_from_state }} {{ $shipment->ship_from_zipcode }}</div>
                  <div>{{ $shipment->ship_from_country }}</div>
                  <div class="mt-2">
                    <i class="fa fa-phone"></i> {{ $shipment->ship_from_contact }}<br>
                    <i class="fa fa-envelope"></i> {{ $shipment->ship_from_email }}
                  </div>
                  @if($shipment->ship_from_dock_hours)
                    <div class="mt-2"><strong>Dock Hours:</strong> {{ $shipment->ship_from_dock_hours }}</div>
                  @endif
                  @if($shipment->ship_from_lunch_hour)
                    <div class="mt-1"><strong>Lunch Hour:</strong> {{ $shipment->ship_from_lunch_hour }}</div>
                  @endif
                  @if($shipment->ship_from_pickup_delivery_instructions)
                    <div class="mt-1"><strong>Pick up / Delivery Instructions:</strong><br>{{ $shipment->ship_from_pickup_delivery_instructions }}</div>
                  @endif
                  @if($shipment->ship_from_appointment)
                    <div class="mt-1"><strong>Appointment:</strong> {{ $shipment->ship_from_appointment }}</div>
                  @endif
                  @if($shipment->ship_from_accessorial)
                    <div class="mt-1"><strong>Accessorial:</strong><br>{{ $shipment->ship_from_accessorial }}</div>
                  @endif
                </div>
              </div>
            </div>
            
            <div class="col-md-6">
              <div class="card mb-3">
                <div class="card-header">
                  <h6 class="mb-0">Recipient (To)</h6>
                </div>
                <div class="card-body">
                  @if($shipment->ship_to_company)
                    <div class="fw-bold">{{ $shipment->ship_to_company }}</div>
                  @endif
                  <div>{{ $shipment->ship_to_first_name }}</div>
                  <div>{{ $shipment->ship_to_address_1 }}</div>
                  <div>{{ $shipment->ship_to_city }}, {{ $shipment->ship_to_state }} {{ $shipment->ship_to_zipcode }}</div>
                  <div>{{ $shipment->ship_to_country }}</div>
                  <div class="mt-2">
                    <i class="fa fa-phone"></i> {{ $shipment->ship_to_contact }}<br>
                    <i class="fa fa-envelope"></i> {{ $shipment->ship_to_email }}
                  </div>
                  @if($shipment->ship_to_dock_hours)
                    <div class="mt-2"><strong>Dock Hours:</strong> {{ $shipment->ship_to_dock_hours }}</div>
                  @endif
                  @if($shipment->ship_to_lunch_hour)
                    <div class="mt-1"><strong>Lunch Hour:</strong> {{ $shipment->ship_to_lunch_hour }}</div>
                  @endif
                  @if($shipment->ship_to_pickup_delivery_instructions)
                    <div class="mt-1"><strong>Pick up / Delivery Instructions:</strong><br>{{ $shipment->ship_to_pickup_delivery_instructions }}</div>
                  @endif
                  @if($shipment->ship_to_appointment)
                    <div class="mt-1"><strong>Appointment:</strong> {{ $shipment->ship_to_appointment }}</div>
                  @endif
                  @if($shipment->ship_to_accessorial)
                    <div class="mt-1"><strong>Accessorial:</strong><br>{{ $shipment->ship_to_accessorial }}</div>
                  @endif
                </div>
              </div>
            </div>
          </div>

          <!-- Packages -->
          @if($shipment->packages->count() > 0)
          <div class="card mb-3">
            <div class="card-header">
              <h6 class="mb-0">Packages ({{ $shipment->packages->count() }})</h6>
            </div>
            <div class="card-body">
              @foreach($shipment->packages as $index => $package)
                <div class="package-card">
                  <div class="package-header">
                    <span class="package-number">Package {{ $index + 1 }}</span>
                    <span class="badge bg-secondary">{{ ucfirst($package->packaging) }}</span>
                  </div>
                  <div class="row">
                    <div class="col-md-3">
                      <small class="text-muted">Weight</small><br>
                      <strong>{{ $package->weight_formatted }}</strong>
                    </div>
                    <div class="col-md-3">
                      <small class="text-muted">Dimensions</small><br>
                      <strong>{{ $package->dimensions_formatted }}</strong>
                    </div>
                    <div class="col-md-3">
                      <small class="text-muted">Volume Weight</small><br>
                      <strong>{{ number_format($package->volume_weight, 2) }} kg</strong>
                    </div>
                    <div class="col-md-3">
                      <small class="text-muted">Declared Value</small><br>
                      <strong>{{ number_format($package->declared_value, 2) }}</strong>
                    </div>
                  </div>
                  @if($package->dimensions_note)
                    <div class="mt-2 text-muted small">{{ $package->dimensions_note }}</div>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
          @endif

          <!-- Items -->
          @if($shipment->items->count() > 0)
          <div class="card mb-3">
            <div class="card-header">
              <h6 class="mb-0">Items ({{ $shipment->items->count() }})</h6>
            </div>
            <div class="card-body">
              @foreach($shipment->items as $item)
                <div class="item-row">
                  <div class="row">
                    <div class="col-md-4">
                      <strong>{{ $item->product->name ?? $item->product_code }}</strong>
                      <div class="text-muted small">{{ $item->product_code }}</div>
                    </div>
                    <div class="col-md-2 text-center">
                      <strong>{{ $item->qty }}</strong>
                      <div class="text-muted small">{{ $item->product_unit }}</div>
                    </div>
                    <div class="col-md-3 text-end">
                      <strong>{{ number_format($item->net_unit_cost, 2) }}</strong>
                      <div class="text-muted small">Unit Cost</div>
                    </div>
                    <div class="col-md-3 text-end">
                      <strong>{{ number_format($item->subtotal, 2) }}</strong>
                      <div class="text-muted small">Subtotal</div>
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
          @endif

          <!-- Comments -->
          @if($shipment->comments)
          <div class="card mb-3">
            <div class="card-header">
              <h6 class="mb-0">Comments / Instructions</h6>
            </div>
            <div class="card-body">
              {{ $shipment->comments }}
            </div>
          </div>
          @endif

        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
          
          <!-- Tracking Info -->
          @if($shipment->tracking_number)
          <div class="card mb-3">
            <div class="card-header">
              <h6 class="mb-0">Tracking Information</h6>
            </div>
            <div class="card-body">
              <div class="info-card">
                <h6>Tracking Number</h6>
                <div class="info-value">{{ $shipment->tracking_number }}</div>
              </div>
              @if($shipment->provider)
                <div class="info-card">
                  <h6>Carrier</h6>
                  <div class="info-value">{{ $shipment->provider }}</div>
                </div>
              @endif
              @if($shipment->service_name)
                <div class="info-card">
                  <h6>Service</h6>
                  <div class="info-value">{{ $shipment->service_name }}</div>
                </div>
              @endif
            </div>
          </div>
          @endif

          <!-- Labels -->
          @if($shipment->labels->count() > 0)
          <div class="card mb-3">
            <div class="card-header">
              <h6 class="mb-0">Labels & Documents</h6>
            </div>
            <div class="card-body">
              @foreach($shipment->labels as $label)
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div>
                    <div class="fw-bold">{{ ucfirst($label->label_type) }} Label</div>
                    <div class="text-muted small">{{ $label->filename }}</div>
                  </div>
                  <a href="{{ route('shipment.label.download', $label->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-download"></i>
                  </a>
                </div>
              @endforeach
            </div>
          </div>
          @endif

          <!-- Activity Log -->
          <div class="card">
            <div class="card-header">
              <h6 class="mb-0">Activity Log</h6>
            </div>
            <div class="card-body">
              @forelse($shipment->logs->sortByDesc('created_at') as $log)
                <div class="log-entry">
                  <div class="log-time">{{ $log->created_at->format('M d, H:i') }}</div>
                  <div class="log-action">{{ $log->action_text }}</div>
                  <div class="log-description">{{ $log->description }}</div>
                  @if($log->user)
                    <div class="text-muted small">by {{ $log->user->name }}</div>
                  @endif
                </div>
              @empty
                <div class="text-muted text-center">No activity logged yet</div>
              @endforelse
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</div>

{{-- ===================== MODAL (Create/Update Label) ===================== --}}
<div class="modal fade" id="createLabelModal" tabindex="-1" role="dialog" aria-labelledby="createLabelLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form class="modal-content" method="POST" action="{{ route('shipment.label.store', $shipment->id) }}">
      @csrf

      <div class="modal-header">
        <h5 class="modal-title" id="createLabelLabel"><i class="fa fa-tag"></i> Create Shipping Label</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Carrier *</label>
            <select name="provider" id="provider" class="form-control" required>
              <option value="" disabled {{ $shipment->provider ? '' : 'selected' }}>Select</option>
              <option value="dhl"   {{ $shipment->provider==='dhl'   ? 'selected' : '' }}>DHL</option>
              <option value="ups"   {{ $shipment->provider==='ups'   ? 'selected' : '' }}>UPS</option>
              <option value="fedex" {{ $shipment->provider==='fedex' ? 'selected' : '' }}>FedEx</option>
              <option value="other" {{ $shipment->provider==='other' ? 'selected' : '' }}>Other</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Service (Code)</label>
            <input type="text" name="service_code" value="{{ $shipment->service_code ?? '' }}" class="form-control" placeholder="e.g. EXPRESS_WORLDWIDE / 2ND_DAY">
          </div>

          <div class="col-md-4">
            <label class="form-label">Service Name</label>
            <input type="text" name="service_name" value="{{ $shipment->service_name ?? '' }}" class="form-control" placeholder="e.g. Express Worldwide">
          </div>

          <div class="col-md-4">
            <label class="form-label">Payer</label>
            <select name="payer" class="form-control">
              <option value="" {{ empty($shipment->payer) ? 'selected' : '' }}>Select</option>
              <option value="shipper"     {{ $shipment->payer==='shipper'     ? 'selected' : '' }}>Shipper</option>
              <option value="receiver"    {{ $shipment->payer==='receiver'    ? 'selected' : '' }}>Receiver</option>
              <option value="third_party" {{ $shipment->payer==='third_party' ? 'selected' : '' }}>Third Party</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Account #</label>
            <input type="text" name="account_number" value="{{ $shipment->account_number ?? '' }}" class="form-control" placeholder="Carrier Account">
          </div>

          <div class="col-md-4">
            <label class="form-label">Signature Option</label>
            <select name="signature_option" class="form-control">
              <option value=""       {{ empty($shipment->signature_option) ? 'selected' : '' }}>None</option>
              <option value="direct" {{ $shipment->signature_option==='direct' ? 'selected' : '' }}>Direct</option>
              <option value="adult"  {{ $shipment->signature_option==='adult'  ? 'selected' : '' }}>Adult</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Saturday Delivery?</label>
            <select name="saturday_delivery" class="form-control">
              <option value="0" {{ empty($shipment->saturday_delivery) ? 'selected' : '' }}>No</option>
              <option value="1" {{ !empty($shipment->saturday_delivery) ? 'selected' : '' }}>Yes</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Declared Value (Total)</label>
            <input type="number" step="0.01" name="declared_value_total" value="{{ $shipment->declared_value_total ?? '' }}" class="form-control" placeholder="0.00">
          </div>

          <div class="col-md-4">
            <label class="form-label">Currency</label>
            <input type="text" name="currency" value="{{ $shipment->currency->code ?? '' }}" class="form-control" placeholder="e.g. USD / PKR">
          </div>

          <div class="col-md-6">
            <label class="form-label">Reference (on label)</label>
            <input type="text" name="reference" value="{{ $shipment->reference_no ?? '' }}" class="form-control">
          </div>

          <div class="col-md-6">
            <label class="form-label">Tracking # (optional)</label>
            <input type="text" name="tracking_number" value="{{ $shipment->tracking_number ?? '' }}" class="form-control" placeholder="If already assigned">
          </div>

          <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" rows="2" class="form-control" placeholder="Any special instruction for label">{{ $shipment->comments ?? '' }}</textarea>
          </div>

          <div class="col-md-6">
            <label class="form-label">Estimated Rate (optional)</label>
            <input type="number" step="0.01" name="rate_amount" value="{{ json_decode($shipment->rate_breakdown ?? '{}', true)['estimated_rate'] ?? '' }}" class="form-control" placeholder="e.g. 150.00">
          </div>

          <div class="col-md-6">
            <label class="form-label">Label Format</label>
            <select name="label_format" class="form-control">
              <option value=""    {{ empty($shipment->label_format) ? 'selected' : '' }}>Auto</option>
              <option value="PDF" {{ $shipment->label_format==='PDF' ? 'selected' : '' }}>PDF</option>
              <option value="ZPL" {{ $shipment->label_format==='ZPL' ? 'selected' : '' }}>ZPL</option>
              <option value="PNG" {{ $shipment->label_format==='PNG' ? 'selected' : '' }}>PNG</option>
            </select>
          </div>
        </div>

        <p class="small text-muted mt-2">
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