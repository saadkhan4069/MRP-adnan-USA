{{-- resources/views/backend/shipment/create.blade.php --}}
@extends('backend.layout.main')

{{-- If your layout supports it, this collapses the sidebar immediately --}}
@section('sidebar_state', 'shrink')

@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />
<x-validation-error fieldName="product_code" />
<x-validation-error fieldName="qty" />

@php
  // safe decimal fallback
  $dec = $general_setting->decimal ?? 2;

  // build units array (PHP 7.3 safe)
  $unitsArr = [];
  if (!empty($units)) {
      foreach ($units as $u) {
          $unitsArr[] = [
              'id'  => $u->id,
              'name'=> $u->name,
              'op'  => $u->operator,   // '*' or '/'
              'val' => (float) $u->operation_value,
          ];
      }
  }
@endphp

<style>
  /* PAGE-SCOPED: hide sidebar even before JS (fallback if layout doesn't yield) */
  .side-navbar{display:none!important}
  /* optional: if your content shifts due to sidebar width, neutralize common paddings */
  .content-inner, .page-content, .content-body { margin-left: 0 !important; }

  .wizard-steps{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
  .wizard-step{padding:6px 10px;border-radius:999px;border:1px solid #ddd;font-size:12px;background:#f8f9fa;color:#6c757d}
  .wizard-step.active{background:#981a1c;color:#fff;border-color:#981a1c}
  .wizard-step.done{background:#198754;color:#fff;border-color:#198754}
  .step-pane{display:none}
  .step-pane.active{display:block}
  .is-invalid{border-color:#dc3545}
  .bootstrap-select.is-invalid .dropdown-toggle{border-color:#dc3545!important;box-shadow:0 0 0 .2rem rgba(220,53,69,.25)}
  .card-step{border:1px solid #eee;border-radius:10px}
  .card-step .card-header{background:#fff;border-bottom:1px solid #eee}
  .table-fixed thead th{position:sticky;top:0;background:#fff;z-index:1}
  .right-summary{position:sticky;top:12px}
  .summary-card .key{color:#6c757d;font-size:12px}
  .summary-card .value{font-weight:600}
  .pos-relative{position:relative}
  .osm-suggestions{position:absolute;left:0;right:0;top:100%;z-index:1000;background:#fff;border:1px solid #ccc;max-height:220px;overflow:auto;display:none}
  .osm-suggestions .item{padding:8px;cursor:pointer}
  .osm-suggestions .item:hover{background:#f1f3f5}
  .table-items{table-layout:fixed}
  .table-items td,.table-items th{vertical-align:middle}
  .table-items .form-control{height:36px;padding:6px 10px}
  .table-items .form-control-sm{height:34px}
  .table-items input[type="number"]{text-align:right}
  .table-items .sub-total{font-weight:600;text-align:right}
  .table-items .del-row{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center}
  .text-truncate{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

  /* --- Packages: Pro UI --- */
  .pkg-grid{display:grid;grid-template-columns:1fr;gap:12px}
  .pkg-card{border:1px solid #e9ecef;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.03);overflow:hidden;background:#fff}
  .pkg-header{display:flex;align-items:center;justify-content:space-between;padding:12px 14px;border-bottom:1px solid #f1f3f5;background:#fafafa}
  .pkg-title{display:flex;align-items:center;gap:8px}
  .pkg-index{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:999px;background:#981a1c;color:#fff;font-weight:700}
  .pkg-badges{display:flex;gap:8px;flex-wrap:wrap}
  .pkg-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 8px;border:1px solid #e9ecef;border-radius:999px;font-size:12.5px;background:#fff;white-space:nowrap}
  .pkg-ctrls{display:flex;gap:6px}
  .pkg-btn{border:1px solid #dee2e6;background:#fff;border-radius:10px;padding:6px 10px;line-height:1;cursor:pointer}
  .pkg-btn:hover{background:#f8f9fa}
  .pkg-body{padding:16px}
  .pkg-row{display:grid;grid-template-columns:1fr;gap:14px}
  @media (min-width:768px){.pkg-row{grid-template-columns:1fr 1fr}}
  .pkg-field .form-label{font-size:13px;color:#6c757d;margin-bottom:4px}
  .pkg-inline{display:flex;gap:10px;align-items:center}
  .pkg-inline .form-control{flex:1 1 0;min-width:0}
  .pkg-inline select.form-control{max-width:110px}
  .pkg-note{font-size:12px;color:#6c757d;margin-top:6px}
  .pkg-handle{cursor:move}
  .input-icon{position:relative}
  .input-icon i{position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;opacity:.55}
  .input-icon input{padding-left:32px}

  /* FORCE packages grid to single column even on desktop */
  @media (min-width:768px){ .pkg-grid{ grid-template-columns: 1fr !important; gap:16px } }
  @media (min-width:1200px){ .pkg-grid{ grid-template-columns: 1fr !important; } }

  /* --- Attachments (Documents) UI --- */
  :root{
    --pri:#981a1c;
    --muted:#6c757d;
    --bd:#e9ecef;
  }
  .attach-card{border:1px solid var(--bd);border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,.045);overflow:hidden}
  .attach-card .card-header{background:#fff;border-bottom:1px solid var(--bd)}
  .attach-drop{border:1.5px dashed #cfd4da;border-radius:12px;padding:18px;text-align:center;background:#fafafa}
  .attach-drop.dragover{background:#f0f4ff;border-color:#b6c2ff}
  .file-list{display:grid;grid-template-columns:1fr;gap:10px;margin-top:12px}
  @media(min-width:768px){.file-list{grid-template-columns:1fr 1fr}}
  .file-pill{display:flex;align-items:center;gap:10px;border:1px solid var(--bd);border-radius:12px;padding:10px;background:#fff}
  .file-meta{flex:1 1 auto;min-width:0}
  .file-name{font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .file-sub{font-size:12px;color:var(--muted)}
  .file-badge{font-size:11px;border:1px solid var(--bd);padding:2px 8px;border-radius:999px}
  .file-actions{display:flex;gap:6px}
  .btn-icon{width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;border:1px solid #dee2e6;border-radius:10px;background:#fff;cursor:pointer}
  .btn-icon:hover{background:#f8f9fa}
  .text-danger-600{color:#d63384}
</style>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<!-- Ensure shrink class exists ASAP (in case CSS can't hide) -->
<script>
  (function(){
    var el = document.querySelector('.side-navbar');
    var pg = document.querySelector('.page');
    if (el && !el.classList.contains('shrink')) el.classList.add('shrink');
    if (pg && !pg.classList.contains('active')) pg.classList.add('active');
  })();
</script>

<section class="forms">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">

        <div class="wizard-steps" id="wizard-steps">
          <div class="wizard-step active" data-step="1">1) Shipper</div>
          <div class="wizard-step" data-step="2">2) Recipient</div>
          <div class="wizard-step" data-step="3">3) Packages</div>
          <div class="wizard-step" data-step="4">4) Items (optional)</div>
          <div class="wizard-step" data-step="5">5) Charges</div>
          <div class="wizard-step" data-step="6">6) Review</div>
        </div>

        <div class="card card-step">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0" style="color:#981a1c">Create Shipment</h5>
          </div>

          <div class="card-body">
            {!! Form::open(['route' => 'shipment.store', 'method' => 'post', 'files' => true, 'id' => 'shipment-form']) !!}
            @csrf

            <div class="row">
              {{-- LEFT --}}
              <div class="col-lg-8">

                {{-- STEP 1: SHIPPER --}}
                <div class="step-pane active" id="step-1">
                  <h6 class="mb-3" style="border-bottom:1px solid #981a1c;color:#981a1c">Shipper (From)</h6>
                  <div class="row">
                    <div class="col-md-3 mb-3">
                      <label>PO# (optional)</label>
                      <input type="text" name="po_no" class="form-control" placeholder="PO Number">
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>Reference No (optional)</label>
                      <input type="text" name="reference_no" class="form-control" placeholder="Reference # e.g. 123">
                      <x-validation-error fieldName="reference_no" />
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>Buyer</label>
                      <select name="customer_id" class="selectpicker form-control"
                              data-live-search="true" data-none-selected-text="Select Customer...">
                        <option value="" selected disabled>Select Customer...</option>
                        @foreach($lims_customer_list as $customer)
                          <option value="{{$customer->id}}">{{$customer->name}} ({{$customer->company_name}})</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>Shipment Status</label>
                      <select name="status" class="form-control">
                        <option value="1">Pending</option>
                        <option value="2">In Transit</option>
                        <option value="3">Delivered</option>
                        <option value="4">Returned</option>
                        <option value="5">Cancelled</option>
                      </select>
                    </div>

                    <div class="col-md-3 mb-3">
                      <label>Company (optional)</label>
                      <input type="text" name="ship_from_company" class="form-control" placeholder="Company Name">
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>Full Name *</label>
                      <input type="text" name="ship_from_first_name" class="form-control required-field" placeholder="Full name" required>
                    </div>

                    <div class="col-md-6 mb-3 pos-relative">
                      <label>Address *</label>
                      <input type="text" id="ship_from_address_1" name="ship_from_address_1" class="form-control required-field" placeholder="Street, number" required>
                      <div id="suggestions-from" class="osm-suggestions"></div>
                    </div>

                    <div class="col-md-3 mb-3">
                      <label>Country *</label>
                      <input type="text" name="ship_from_country" class="form-control required-field" placeholder="Country" required>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>State *</label>
                      <input type="text" name="ship_from_state" class="form-control required-field" placeholder="State / Region" required>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>City *</label>
                      <input type="text" name="ship_from_city" class="form-control required-field" placeholder="City" required>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>Postal Code *</label>
                      <input type="text" name="ship_from_zipcode" class="form-control required-field" placeholder="e.g. 10001" required>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>Contact *</label>
                      <input type="text" name="ship_from_contact" class="form-control required-field" placeholder="Phone" required>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>Email *</label>
                      <input type="email" name="ship_from_email" class="form-control required-field" placeholder="name@company.com" required>
                    </div>
                  </div>
                </div>

                {{-- STEP 2: RECIPIENT --}}
                <div class="step-pane" id="step-2">
                  <h6 class="mb-3" style="border-bottom:1px solid #981a1c;color:#981a1c">Recipient (To)</h6>
                  <div class="row">
                    <div class="col-md-3 mb-3">
                      <label>Company (optional)</label>
                      <input type="text" name="ship_to_company" class="form-control" placeholder="Company Name">
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>Full Name *</label>
                      <input type="text" name="ship_to_first_name" class="form-control required-field" placeholder="Full name" required>
                    </div>

                    <div class="col-md-6 mb-3 pos-relative">
                      <label>Address *</label>
                      <input type="text" id="ship_to_address_1" name="ship_to_address_1" class="form-control required-field" placeholder="Street, number" required>
                      <div id="suggestions-to" class="osm-suggestions"></div>
                    </div>

                    <div class="col-md-3 mb-3">
                      <label>Country *</label>
                      <input type="text" name="ship_to_country" class="form-control required-field" placeholder="Country" required>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>State *</label>
                      <input type="text" name="ship_to_state" class="form-control required-field" placeholder="State / Region" required>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>City *</label>
                      <input type="text" name="ship_to_city" class="form-control required-field" placeholder="City" required>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>Postal Code *</label>
                      <input type="text" name="ship_to_zipcode" class="form-control required-field" placeholder="e.g. 75008" required>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>Contact *</label>
                      <input type="text" name="ship_to_contact" class="form-control required-field" placeholder="Phone" required>
                    </div>
                    <div class="col-md-3 mb-3">
                      <label>Email *</label>
                      <input type="email" name="ship_to_email" class="form-control required-field" placeholder="name@company.com" required>
                    </div>

                    <div class="col-md-2 mb-3">
                      <label>Currency</label>
                      <select name="currency_id" id="currency-id" class="form-control selectpicker" data-none-selected-text="Select currency...">
                        <option value="" disabled selected>Select currency...</option>
                        @foreach($currency_list as $currency_data)
                          <option value="{{$currency_data->id}}" data-rate="{{$currency_data->exchange_rate}}">{{$currency_data->code}}</option>
                        @endforeach
                      </select>
                      <x-validation-error fieldName="currency_id" />
                    </div>
                    <div class="col-md-2 mb-3">
                      <label>Exchange Rate *</label>
                      <input class="form-control required-field" type="number" step="0.0001" id="exchange_rate" name="exchange_rate"
                             placeholder="Rate e.g. 278.50" value="{{ $currency->exchange_rate ?? 1 }}" required>
                      <x-validation-error fieldName="exchange_rate" />
                    </div>
                  </div>
                </div>

                {{-- STEP 3: PACKAGES (single column, pro UI) --}}
                <div class="step-pane" id="step-3">
                  <h6 class="mb-3" style="border-bottom:1px solid #981a1c;color:#981a1c">Packages</h6>
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="text-muted small">Drag handle se order change karein · Volumetric weight auto-calc</div>
                    <div>
                      <button type="button" id="add-package" class="btn btn-outline-primary">+ Add package</button>
                    </div>
                  </div>
                  <div id="packages" class="pkg-grid"></div>
                </div>

                {{-- STEP 4: ITEMS (optional) --}}
                <div class="step-pane" id="step-4">
                  <h6 class="mb-3" style="border-bottom:1px solid #981a1c;color:#981a1c">Items (optional)</h6>

                  <div class="mb-2">
                    <label>Quick add item</label>
                    <div class="input-group">
                      <button class="btn btn-secondary" type="button" title="Scan/Code"><i class="fa fa-barcode"></i></button>
                      <input type="text" id="lims_productcodeSearch" class="form-control" placeholder="Type product code or name, then select">
                    </div>
                  </div>

                  <div class="table-responsive">
                    <table class="table table-hover table-fixed table-items align-middle" id="items-table">
                      <colgroup>
                        <col style="width:44%">
                        <col style="width:38%">
                        <col style="width:14%">
                        <col style="width:4%">
                      </colgroup>
                      <thead>
                        <tr>
                          <th style="min-width:240px">Product</th>
                          <th>Qty / Unit / Unit Price</th>
                          <th>Subtotal</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                      <tfoot>
                        <tr>
                          <th>Totals</th>
                          <th id="total-qty">0</th>
                          <th id="total">{{ number_format(0, $dec, '.', '') }}</th>
                          <th></th>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                </div>

                {{-- STEP 5: CHARGES --}}
                <div class="step-pane" id="step-5">
                  <h6 class="mb-3" style="border-bottom:1px solid #981a1c;color:#981a1c">Charges</h6>
                  <div class="row">
                    <div class="col-md-4 mb-3">
                      <label>Order Tax</label>
                      <select class="form-control" name="order_tax_rate" id="order_tax_rate">
                        <option value="0">No Tax</option>
                        @foreach($lims_tax_list as $tax)
                          <option value="{{$tax->rate}}">{{$tax->name}}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4 mb-3">
                      <label><strong>Discount</strong></label>
                      <input type="number" step="0.1" name="order_discount" id="order_discount" class="form-control" placeholder="0.00" value="0">
                    </div>
                    <div class="col-md-4 mb-3">
                      <label><strong>Shipping Cost</strong></label>
                      <input type="number" step="0.1" name="shipping_cost" id="shipping_cost_input" class="form-control" placeholder="0.00" value="0">
                    </div>

                    <div class="col-md-12 mb-3">
                      <label>Comments / Shipping Instructions</label>
                      <textarea name="comments" rows="3" class="form-control" placeholder="Any special handling, references, etc."></textarea>
                    </div>
                  </div>

                  {{-- DOCUMENTS / ATTACHMENTS (NEW) --}}
                  <div class="attach-card card mt-2">
                    <div class="card-header d-flex align-items-center justify-content-between">
                      <strong>Documents / Attachments</strong>
                      <small class="text-muted">PDF, Images, Office docs · Max 10MB/file · up to 10 files</small>
                    </div>
                    <div class="card-body">
                      <div class="attach-drop" id="attach-drop">
                        <div class="mb-2"><i class="fa fa-cloud-upload"></i></div>
                        <div class="mb-2">Drag & drop files here or</div>
                        <div>
                          <label class="btn btn-outline-primary mb-0">
                            Browse…
                            <input type="file" id="attachments" name="attachments[]" class="d-none"
                                   multiple
                                   accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.csv,.txt">
                          </label>
                        </div>
                        <div class="small text-muted mt-2">Tip: Invoices, packing lists, CNIC/photo, airway bill, etc.</div>
                      </div>

                      <div id="file-list" class="file-list"></div>

                      <!-- Hidden store of valid File objects mirrored into a DataTransfer -->
                      <input type="hidden" id="attachments-count" value="0">
                    </div>
                  </div>
                </div>

                {{-- STEP 6: REVIEW --}}
                <div class="step-pane" id="step-6">
                  <h6 class="mb-3" style="border-bottom:1px solid #981a1c;color:#981a1c">Review & Submit</h6>
                  <div class="table-responsive">
                    <table class="table table-bordered table-condensed">
                      <tr>
                        <td><strong>Items</strong> <span class="pull-right" id="item">{{ number_format(0, $dec, '.', '') }}</span></td>
                        <td><strong>Total</strong> <span class="pull-right" id="subtotal">{{ number_format(0, $dec, '.', '') }}</span></td>
                        <td><strong>Order Tax</strong> <span class="pull-right" id="order_tax">{{ number_format(0, $dec, '.', '') }}</span></td>
                        <td><strong>Shipping Cost</strong> <span class="pull-right" id="shipping_cost">{{ number_format(0, $dec, '.', '') }}</span></td>
                        <td><strong>Grand Total</strong> <span class="pull-right" id="grand_total">{{ number_format(0, $dec, '.', '') }}</span></td>
                      </tr>
                    </table>
                  </div>

                  {{-- Hidden mirrors --}}
                  <input type="hidden" name="total_qty">
                  <input type="hidden" name="total_discount" value="{{ number_format(0, $dec, '.', '') }}">
                  <input type="hidden" name="total_tax">
                  <input type="hidden" name="total_cost">
                  <input type="hidden" name="item">
                  <input type="hidden" name="order_tax">
                  <input type="hidden" name="grand_total">
                  <input type="hidden" name="paid_amount" value="{{ number_format(0, $dec, '.', '') }}">
                  <input type="hidden" name="payment_status" value="1">

                  <button type="submit" class="btn btn-primary" id="submit-btn">Submit Shipment</button>
                </div>

              </div>

              {{-- RIGHT SUMMARY --}}
              <div class="col-lg-4">
                <div class="right-summary">
                  <div class="card summary-card mb-3">
                    <div class="card-header"><strong>Shipment Summary</strong></div>
                    <div class="card-body">
                      <div class="mb-2"><span class="key">Buyer:</span> <span class="value" id="sum-buyer">—</span></div>
                      <div class="mb-2"><span class="key">Currency/Rate:</span> <span class="value" id="sum-currency">—</span></div>
                      <hr>
                      <div class="mb-1"><span class="key">From</span></div>
                      <div id="sum-from" class="small"></div>
                      <div class="mb-1 mt-2"><span class="key">To</span></div>
                      <div id="sum-to" class="small"></div>
                      <hr>
                      <div class="d-flex justify-content-between"><span class="key">Items</span><span class="value" id="sum-items">0</span></div>
                      <div class="d-flex justify-content-between"><span class="key">Subtotal</span><span class="value" id="sum-subtotal">0.00</span></div>
                      <div class="d-flex justify-content-between"><span class="key">Order Tax</span><span class="value" id="sum-ordertax">0.00</span></div>
                      <div class="d-flex justify-content-between"><span class="key">Shipping</span><span class="value" id="sum-shipping">0.00</span></div>
                      <hr>
                      <div class="d-flex justify-content-between"><span class="key">Grand Total</span><span class="value" id="sum-grand">0.00</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {!! Form::close() !!}
          </div>

          <div class="card-footer d-flex justify-content-between">
            <button type="button" class="btn btn-outline-secondary" id="prev-step" disabled>Back</button>
            <button type="button" class="btn btn-outline-primary" id="next-step">Next</button>
          </div>
        </div>

      </div>
    </div>
  </div>
</section>

@push('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
/* ---------------- Inject UNITS from PHP ---------------- */
const UNITS = @json($unitsArr);

/* ---------------- Wizard ---------------- */
let currentStep=1,totalSteps=6;
const panes=[...document.querySelectorAll('.step-pane')];
const badges=[...document.querySelectorAll('.wizard-step')];
function showStep(n){
  panes.forEach((p,i)=>p.classList.toggle('active',(i+1)===n));
  badges.forEach((b,i)=>{b.classList.toggle('active',(i+1)===n);b.classList.toggle('done',(i+1)<n)});
  document.getElementById('prev-step').disabled=(n===1);
}
document.getElementById('next-step').addEventListener('click',()=>{ if(!validateStep(currentStep))return; if(currentStep<totalSteps){currentStep++;showStep(currentStep);} });
document.getElementById('prev-step').addEventListener('click',()=>{ if(currentStep>1){currentStep--;showStep(currentStep);} });
badges.forEach(b=>b.addEventListener('click',()=>{const to=parseInt(b.dataset.step||"0",10); if(to && to<=currentStep && validateStep(Math.min(currentStep,to))){currentStep=to;showStep(currentStep);}}));

/* ---------------- Currency / Rate (null-safe) ---------------- */
let exchangeRate = Number('{{ $currency->exchange_rate ?? 1 }}') || 1;
$('#currency-id').val('{{ $currency->id ?? '' }}').change();
$('#exchange_rate').val(exchangeRate);
$('#currency-id').on('change', function(){
  const rate = $(this).find(':selected').data('rate') || 1;
  $('#exchange_rate').val(rate); exchangeRate = rate;
  refreshAllRowCosts(); recalcTotals(); updateSummary();
});
$('#exchange_rate').on('input', function(){
  exchangeRate = parseFloat(this.value)||1;
  refreshAllRowCosts(); recalcTotals(); updateSummary();
});

/* ---------------- Required helpers ---------------- */
function normalizeValue($el){ let v=$el.val(); if(Array.isArray(v)) v=v.filter(Boolean)[0]||''; if(v==null)v=''; return String(v).trim(); }
function markInvalid(el){ $(el).addClass('is-invalid'); }
function clearInvalid(el){ $(el).removeClass('is-invalid'); }
$(document).on('input change blur','.required-field',function(){ if(normalizeValue($(this))) clearInvalid(this); });

/* ---------------- Items ---------------- */

/* Product search dataset */
<?php $productArray = []; ?>
var lims_product_code = [
@foreach($lims_product_list_without_variant as $product)
  <?php $productArray[] =
    htmlspecialchars($product->code).'|' .
    preg_replace('/[\n\r]/'," ",htmlspecialchars($product->name))." (".$product->title.")" . '|' .
    $product->id . '|' .
    (float)$product->cost; ?>
@endforeach
@foreach($lims_product_list_with_variant as $product)
  <?php $productArray[] =
    htmlspecialchars($product->item_code).'|' .
    preg_replace('/[\n\r]/'," ",htmlspecialchars($product->name))." (".$product->title.")" . '|' .
    $product->id . '|' .
    (float)$product->cost; ?>
@endforeach
{!! '"'.implode('","', $productArray).'"' !!}
];

$('#lims_productcodeSearch').autocomplete({
  source: function(req,res){
    const rx = new RegExp(".?"+$.ui.autocomplete.escapeRegex(req.term),"i");
    res($.grep(lims_product_code, function(it){ return rx.test(it); }));
  },
  minLength: 1,
  select: function(e, ui){ addFromCode(ui.item.value); $(this).val(''); return false; },
  response: function(e, ui){ if(ui.content.length===1){ addFromCode(ui.content[0].value); $('#lims_productcodeSearch').val(''); } },
  focus: function(){ return false; }
});

/* Items state */
let base_cost = []; // base cost in base unit (before unit op)

/* Helpers */
function unitOptionsHtml() {
  if (!Array.isArray(UNITS) || UNITS.length === 0) {
    return '<option value="0" data-op="*" data-val="1">Unit</option>';
  }
  return UNITS.map(function(u, i){
    return '<option value="'+i+'" data-op="'+u.op+'" data-val="'+u.val+'">'+u.name+'</option>';
  }).join('');
}
function addFromCode(val){
  const p = val.split('|');
  const code = p[0]||'';
  const name = (p[1]||'').trim();
  const id   = p[2]||'';
  const cost = parseFloat(p[3]||'0')||0;
  insertRow({id, code, name, cost});
}
function parseRowUnitCost(rowIdx, $row) {
  const unitIdx = parseInt($row.find('.unit-select').val(), 10) || 0;
  const op  = (UNITS[unitIdx] && UNITS[unitIdx].op) ? UNITS[unitIdx].op : '*';
  const val = Number((UNITS[unitIdx] && UNITS[unitIdx].val) ? UNITS[unitIdx].val : 1);
  const base = (base_cost[rowIdx] || 0) * (Number.isFinite(exchangeRate)?exchangeRate:1);
  const ui = (op === '*') ? (base * val) : (base / (val || 1));
  return Number.isFinite(ui) ? ui : base;
}
function insertRow(item){
  const $row = $(`
    <tr>
      <td>
        <div class="fw-semibold text-truncate" title="${item.name}">${item.name}</div>
        <div class="small text-muted">${item.code}</div>
        <input type="hidden" class="product-id"   name="product_id[]"   value="${item.id}">
        <input type="hidden" class="product-code" name="product_code[]" value="${item.code}">
        <input type="hidden" class="net_unit_cost" name="net_unit_cost[]">
      </td>
      <td>
        <div class="d-flex" style="gap:6px; align-items:center;">
          <input type="number" class="form-control form-control-sm qty" name="qty[]" value="1" min="1" style="max-width:90px">
          <select class="form-control form-control-sm unit-select" name="product_unit[]" style="min-width:120px">
            ${unitOptionsHtml()}
          </select>
          <input type="number" class="form-control form-control-sm row-unit-price" step="0.01" placeholder="0.00" style="max-width:140px">
        </div>
      </td>
      <td class="sub-total">0.00<input type="hidden" class="subtotal-value" name="subtotal[]"></td>
      <td class="text-end"><button type="button" class="btn btn-sm btn-danger del-row" title="Remove">&times;</button></td>
    </tr>
  `);

  $("#items-table tbody").prepend($row);
  const idx = $row.index();
  base_cost.splice(idx, 0, item.cost);

  let unitPrice = parseRowUnitCost(idx, $row);
  if (!Number.isFinite(unitPrice)) unitPrice = (item.cost || 0) * (Number.isFinite(exchangeRate)?exchangeRate:1);
  $row.find('.row-unit-price').val(unitPrice.toFixed({{ $dec }})).trigger('input');
  calculateRow(idx);
}

/* Row events */
$('#items-table').on('input', '.qty', function(){
  const i = $(this).closest('tr').index();
  if ((parseFloat(this.value)||0) < 1) { this.value = 1; }
  calculateRow(i);
});
$('#items-table').on('change', '.unit-select', function(){
  const i = $(this).closest('tr').index();
  const $row = $('#items-table tbody tr').eq(i);
  let unitPrice = parseRowUnitCost(i, $row);
  if (!Number.isFinite(unitPrice)) unitPrice = 0;
  $row.find('.row-unit-price').val(unitPrice.toFixed({{ $dec }})).trigger('input');
  calculateRow(i);
});
$('#items-table').on('input', '.row-unit-price', function(){
  const i = $(this).closest('tr').index();
  calculateRow(i);
});
$('#items-table').on('click', '.del-row', function(){
  const i = $(this).closest('tr').index();
  base_cost.splice(i,1);
  $(this).closest('tr').remove();
  recalcTotals(); updateSummary();
});

/* Calculations */
function calculateRow(i){
  const $row = $('#items-table tbody tr').eq(i);
  if ($row.length === 0) return;
  const qty  = parseFloat($row.find('.qty').val()) || 1;
  const price= parseFloat($row.find('.row-unit-price').val());
  const safePrice = Number.isFinite(price) ? price : 0;
  const sub_total = qty * safePrice;

  $row.find('.net_unit_cost').val(safePrice.toFixed({{ $dec }}));
  $row.find('.sub-total').contents().filter(function(){return this.nodeType===3;}).first()
      .replaceWith(sub_total.toFixed({{ $dec }}));
  $row.find('.subtotal-value').val(sub_total.toFixed({{ $dec }}));

  recalcTotals(); updateSummary();
}
function refreshAllRowCosts(){
  $('#items-table tbody tr').each(function(i){
    const $row = $(this);
    let unitPrice = parseRowUnitCost(i, $row);
    if (!Number.isFinite(unitPrice)) unitPrice = 0;
    $row.find('.row-unit-price').val(unitPrice.toFixed({{ $dec }}));
    calculateRow(i);
  });
}
function recalcTotals(){
  let totalQty=0, total=0, itemsCount=0;
  $('#items-table tbody tr').each(function(){
    const qty = parseFloat($(this).find('.qty').val())||0;
    const sub = parseFloat($(this).find('.sub-total').text())||0;
    totalQty += qty; total += sub; itemsCount++;
  });

  $('#total-qty').text(totalQty);
  $('#total').text(total.toFixed({{ $dec }}));

  const ordTaxRate = parseFloat($('#order_tax_rate').val())||0;
  const ordDiscount = parseFloat($('#order_discount').val())||0;
  const ship = parseFloat($('#shipping_cost_input').val())||0;

  const orderBase = Math.max(total - ordDiscount, 0);
  const orderTax = orderBase * (ordTaxRate/100);
  const grand = orderBase + orderTax + ship;

  $('#item').text(itemsCount+'('+totalQty+')');
  $('#subtotal').text(total.toFixed({{ $dec }}));
  $('#order_tax').text(orderTax.toFixed({{ $dec }}));
  $('#shipping_cost').text(ship.toFixed({{ $dec }}));
  $('#grand_total').text(grand.toFixed({{ $dec }}));

  $('input[name="item"]').val(itemsCount);
  $('input[name="total_qty"]').val(totalQty);
  $('input[name="total_cost"]').val(total.toFixed({{ $dec }}));
  $('input[name="total_tax"]').val(orderTax.toFixed({{ $dec }}));
  $('input[name="order_tax"]').val(orderTax.toFixed({{ $dec }}));
  $('input[name="grand_total"]').val(grand.toFixed({{ $dec }}));
  $('input[name="total_discount"]').val((ordDiscount||0).toFixed({{ $dec }}));
}

/* ---------------- Charges change HANDLERS ---------------- */
$('#order_tax_rate, #order_discount, #shipping_cost_input').on('input change', function(){
  recalcTotals(); updateSummary();
});

/* ---------------- Validation & submit ---------------- */
function validateStep(step){
  const pane=document.getElementById(`step-${step}`); let ok=true, firstBad=null;
  $(pane).find('.required-field').each(function(){
    if(!normalizeValue($(this))){ ok=false; markInvalid(this); if(!firstBad) firstBad=this; } else { clearInvalid(this); }
  });
  if(!ok && firstBad){ firstBad.focus(); }
  return ok;
}
$('#shipment-form').on('submit',function(e){
  if(!validateStep(currentStep)){ e.preventDefault(); return; }
  recalcTotals(); updateSummary();
  $('#submit-btn').prop('disabled',true);
});

/* ---------------- Summary ---------------- */
function updateSummary(){
  const buyer = $('select[name="customer_id"] option:selected').text() || '—';
  $('#sum-buyer').text(buyer.includes('Select Customer')?'—':buyer);

  const curr = $('#currency-id option:selected').text() || '—';
  const rate = $('#exchange_rate').val() || '—';
  $('#sum-currency').text(curr==='Select currency...'?'—':(curr+' @ '+rate));

  const from = [
    $('input[name="ship_from_first_name"]').val(),
    $('input[name="ship_from_address_1"]').val(),
    $('input[name="ship_from_city"]').val(),
    $('input[name="ship_from_state"]').val(),
    $('input[name="ship_from_zipcode"]').val(),
    $('input[name="ship_from_country"]').val()
  ].filter(Boolean).join(', ');
  const to = [
    $('input[name="ship_to_first_name"]').val(),
    $('input[name="ship_to_address_1"]').val(),
    $('input[name="ship_to_city"]').val(),
    $('input[name="ship_to_state"]').val(),
    $('input[name="ship_to_zipcode"]').val(),
    $('input[name="ship_to_country"]').val()
  ].filter(Boolean).join(', ');
  $('#sum-from').text(from || '—');
  $('#sum-to').text(to || '—');

  $('#sum-items').text($('#item').text());
  $('#sum-subtotal').text($('#subtotal').text());
  $('#sum-ordertax').text($('#order_tax').text());
  $('#sum-shipping').text($('#shipping_cost').text());
  $('#sum-grand').text($('#grand_total').text());
}
$(document).on('input change','input,select,textarea',updateSummary);

// Ensure first totals are calculated on load
recalcTotals();
updateSummary();

/* ---------------- OSM Autocomplete (UPDATED: address field excludes state/zipcode/country) ---------------- */
function bindOSM(inputSel, boxSel, map){
  const input = document.querySelector(inputSel);
  const box   = document.querySelector(boxSel);
  if(!input || !box) return;

  let timer=null;
  input.addEventListener('input', function(){
    const q = input.value.trim();
    if(timer) clearTimeout(timer);
    if(q.length < 3){ box.style.display='none'; box.innerHTML=''; return; }

    timer = setTimeout(()=> {
      fetch('https://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=8&q=' + encodeURIComponent(q))
        .then(r=>r.json())
        .then(list=>{
          box.innerHTML='';
          if(!list || !list.length){ box.style.display='none'; return; }
          list.forEach(item=>{
            const div=document.createElement('div');
            div.className='item';

            const a = item.address || {};
            const state     = a.state || a.region || a.state_district || '';
            const postcode  = a.postcode || '';
            const country   = a.country || '';

            // split full display and remove state/postcode/country parts
            const fullParts = item.display_name.split(',').map(p => p.trim());
            const filteredParts = fullParts.filter(part =>
              part !== state && part !== postcode && part !== country
            );

            div.textContent = filteredParts.join(', ');

            div.addEventListener('click', ()=>{
              // set filtered address only (NO state/zipcode/country)
              input.value = filteredParts.join(', ');

              // still fill individual fields
              if(map.city)     document.querySelector(map.city).value     = a.city || a.town || a.village || a.hamlet || a.suburb || '';
              if(map.state)    document.querySelector(map.state).value    = state;
              if(map.postcode) document.querySelector(map.postcode).value = postcode;
              if(map.country)  document.querySelector(map.country).value  = country;

              box.style.display='none'; box.innerHTML='';
              updateSummary();
            });
            box.appendChild(div);
          });
          box.style.display='block';
        })
        .catch(()=>{ box.style.display='none'; box.innerHTML=''; });
    }, 250);
  });

  document.addEventListener('click', (e)=>{
    if(e.target!==input && !box.contains(e.target)){ box.style.display='none'; }
  });
}
bindOSM('#ship_from_address_1', '#suggestions-from', {
  city:     'input[name="ship_from_city"]',
  state:    'input[name="ship_from_state"]',
  postcode: 'input[name="ship_from_zipcode"]',
  country:  'input[name="ship_from_country"]'
});
bindOSM('#ship_to_address_1', '#suggestions-to', {
  city:     'input[name="ship_to_city"]',
  state:    'input[name="ship_to_state"]',
  postcode: 'input[name="ship_to_zipcode"]',
  country:  'input[name="ship_to_country"]'
});

/* ---------------- Packages (single column, pro UI) ---------------- */
let pkgIndex = 0;

/* helpers */
function vwDivisor(dimUnit){ return dimUnit === 'in' ? 139 : 5000; } // common carriers: cm->5000, in->139
function fmt(n, d=2){ n = parseFloat(n); return Number.isFinite(n) ? n.toFixed(d) : (0).toFixed(d); }

/* Card template */
function packageCard(i){
  return `
  <div class="pkg-card package" data-index="${i}">
    <div class="pkg-header">
      <div class="pkg-title">
        <span class="pkg-index">${i+1}</span>
        <strong>Package</strong>
        <span class="text-muted small pkg-handle" title="Drag to reorder" style="margin-left:6px"><i class="fa fa-grip-vertical"></i></span>
      </div>
      <div class="pkg-badges">
        <span class="pkg-badge"><i class="fa fa-weight-hanging"></i> <span class="b-wt" data-unit="kg">0.000 kg</span></span>
        <span class="pkg-badge"><i class="fa fa-cube"></i> <span class="b-dims">L×W×H</span> <span class="text-muted">(Vol:</span> <span class="b-vw">0.00</span><span class="text-muted">)</span></span>
      </div>
      <div class="pkg-ctrls">
        <button type="button" class="pkg-btn btn-duplicate" title="Duplicate"><i class="fa fa-copy"></i></button>
        <button type="button" class="pkg-btn btn-remove" title="Remove"><i class="fa fa-times"></i></button>
      </div>
    </div>

    <div class="pkg-body">
      <div class="pkg-row">
        <div class="pkg-field">
          <label class="form-label">Packaging</label>
          <select name="packages[${i}][packaging]" class="form-control">
            <option value="">Select</option>
            <option value="your_packaging">Your Packaging</option>
            <option value="box">Box</option>
            <option value="envelope">Envelope</option>
            <option value="tube">Tube</option>
          </select>
        </div>

        <div class="pkg-field">
          <label class="form-label">Declared Value</label>
          <div class="input-icon">
            <i class="fa fa-dollar-sign"></i>
            <input type="number" step="0.01" name="packages[${i}][declared_value]" class="form-control" placeholder="0.00">
          </div>
        </div>

        <div class="pkg-field">
          <label class="form-label">Weight</label>
          <div class="pkg-inline">
            <input type="number" step="0.001" name="packages[${i}][weight]" class="form-control pkg-weight" placeholder="0.000">
            <select name="packages[${i}][weight_unit]" class="form-control pkg-weight-unit" style="max-width:110px">
              <option value="kg" selected>kg</option>
              <option value="lb">lb</option>
            </select>
          </div>
        </div>

        <div class="pkg-field">
          <label class="form-label">Dimensions</label>
          <div class="pkg-inline">
            <input type="number" step="0.1" name="packages[${i}][length]" class="form-control pkg-l" placeholder="L">
            <input type="number" step="0.1" name="packages[${i}][width]"  class="form-control pkg-w" placeholder="W">
            <input type="number" step="0.1" name="packages[${i}][height]" class="form-control pkg-h" placeholder="H">
            <select name="packages[${i}][dim_unit]" class="form-control pkg-dim-unit" style="max-width:110px">
              <option value="cm" selected>cm</option>
              <option value="in">in</option>
            </select>
          </div>
          <div class="pkg-note">Volumetric = (L×W×H) / <span class="vw-div">5000</span> → <strong class="vw-out">0.00</strong> <span class="vw-unit">kg</span></div>
        </div>

        <div class="pkg-field" style="grid-column:1/-1">
          <label class="form-label">Dimensions Note</label>
          <input type="text" name="packages[${i}][dimensions_note]" class="form-control pkg-note-input" placeholder="e.g., 30×20×10 cm / 2.50 kg">
        </div>
      </div>
    </div>
  </div>`;
}

/* add, duplicate, remove */
function addPackage(prefill){
  const i = pkgIndex++;
  $('#packages').append(packageCard(i));
  const $card = $('#packages .package').last();

  if (prefill){
    Object.entries(prefill).forEach(([sel,val])=>{
      $card.find(sel).val(val);
    });
  }
  updatePkgComputed($card);
  reIndexPackages();
}

$('#add-package').on('click', ()=> addPackage());

$('#packages').on('click', '.btn-remove', function(){
  $(this).closest('.package').remove();
  reIndexPackages();
  recalcTotals(); updateSummary();
});

$('#packages').on('click', '.btn-duplicate', function(){
  const $c = $(this).closest('.package');
  const p = collectPkgValues($c);
  addPackage(p);
});

/* collect values for duplicate */
function collectPkgValues($c){
  return {
    'select[name$="[packaging]"]': $c.find('select[name$="[packaging]"]').val(),
    'input[name$="[declared_value]"]': $c.find('input[name$="[declared_value]"]').val(),
    'input[name$="[weight]"]': $c.find('input[name$="[weight]"]').val(),
    'select[name$="[weight_unit]"]': $c.find('select[name$="[weight_unit]"]').val(),
    'input[name$="[length]"]': $c.find('input[name$="[length]"]').val(),
    'input[name$="[width]"]':  $c.find('input[name$="[width]"]').val(),
    'input[name$="[height]"]': $c.find('input[name$="[height]"]').val(),
    'select[name$="[dim_unit]"]': $c.find('select[name$="[dim_unit]"]').val(),
    'input[name$="[dimensions_note]"]': $c.find('input[name$="[dimensions_note]"]').val()
  };
}

/* live updates */
$('#packages').on('input change', '.pkg-weight, .pkg-weight-unit, .pkg-l, .pkg-w, .pkg-h, .pkg-dim-unit, .pkg-note-input', function(){
  const $card = $(this).closest('.package');
  updatePkgComputed($card);
});

/* compute and paint badges/notes */
function updatePkgComputed($card){
  const wt = parseFloat($card.find('.pkg-weight').val())||0;
  const wtUnit = $card.find('.pkg-weight-unit').val()||'kg';
  const L = parseFloat($card.find('.pkg-l').val())||0;
  const W = parseFloat($card.find('.pkg-w').val())||0;
  const H = parseFloat($card.find('.pkg-h').val())||0;
  const dUnit = $card.find('.pkg-dim-unit').val()||'cm';

  let kgWeight = wt;
  if (wtUnit === 'lb') kgWeight = wt * 0.45359237;

  const divisor = vwDivisor(dUnit);
  const vol = (L>0 && W>0 && H>0) ? ((L*W*H)/divisor) : 0;

  $card.find('.b-wt').text(fmt(kgWeight,3)+' kg');
  $card.find('.b-dims').text(`${fmt(L,1)}×${fmt(W,1)}×${fmt(H,1)} ${dUnit}`);
  $card.find('.b-vw').text(fmt(vol,2)+' kg');

  $card.find('.vw-div').text(divisor);
  $card.find('.vw-out').text(fmt(vol,2));
  $card.find('.vw-unit').text('kg');

  const dispWt = wt ? `${fmt(wt,3)} ${wtUnit}` : '—';
  const dispDims = (L||W||H) ? `${fmt(L,1)}×${fmt(W,1)}×${fmt(H,1)} ${dUnit}` : '—';
  const autoNote = `${dispDims} / ${dispWt}`;
  const $note = $card.find('.pkg-note-input');
  if(!$note.is(':focus') && !$note.val()){ $note.val(autoNote); }
}

/* re-index names after remove/reorder so backend arrays stay clean */
function reIndexPackages(){
  $('#packages .package').each(function(idx){
    $(this).attr('data-index', idx);
    $(this).find('.pkg-index').text(idx+1);
    $(this).find('input, select, textarea').each(function(){
      const name = $(this).attr('name');
      if(!name) return;
      const newName = name.replace(/packages\[\d+\]/, `packages[${idx}]`);
      $(this).attr('name', newName);
    });
  });
}

/* make list sortable via jQuery UI (already included) */
$('#packages').sortable({
  handle: '.pkg-handle',
  items: '.package',
  placeholder: 'pkg-card placeholder',
  forcePlaceholderSize: true,
  update: function(){ reIndexPackages(); }
});

/* initial one */
addPackage();

/* ---------------- Attachments: professional UX ---------------- */
const MAX_FILES = 10;        // total files allowed
const MAX_SIZE  = 10 * 1024 * 1024; // 10MB per file
const ACCEPTED  = ['pdf','jpg','jpeg','png','webp','doc','docx','xls','xlsx','csv','txt'];

const inputFiles   = document.getElementById('attachments');
const dropZone     = document.getElementById('attach-drop');
const fileListWrap = document.getElementById('file-list');
const countEl      = document.getElementById('attachments-count');

// internal: keep a DataTransfer so we can remove items
let dt = new DataTransfer();

function extOf(name){
  const m = (name||'').toLowerCase().match(/\.([a-z0-9]+)$/);
  return m? m[1] : '';
}
function fmtBytes(b){
  if(!b && b!==0) return '—';
  const u = ['B','KB','MB','GB']; let i=0, n=b;
  while(n>=1024 && i<u.length-1){ n/=1024; i++; }
  return n.toFixed(n<10 && i>0 ? 1 : 0)+' '+u[i];
}
function iconFor(ext){
  const map = { pdf:'fa-file-pdf', jpg:'fa-file-image', jpeg:'fa-file-image', png:'fa-file-image', webp:'fa-file-image',
                doc:'fa-file-word', docx:'fa-file-word', xls:'fa-file-excel', xlsx:'fa-file-excel', csv:'fa-file-csv', txt:'fa-file-lines' };
  return map[ext] || 'fa-file';
}
function validateFile(file){
  const e = extOf(file.name);
  if(!ACCEPTED.includes(e)) return {ok:false, msg:`${file.name}: unsupported type .${e}`};
  if(file.size > MAX_SIZE)  return {ok:false, msg:`${file.name}: exceeds 10MB`};
  if(dt.files.length >= MAX_FILES) return {ok:false, msg:`Max ${MAX_FILES} files allowed`};
  return {ok:true};
}
function toast(msg, type='danger'){
  // tiny inline toast using alert; replace with your toaster if present
  const el = document.createElement('div');
  el.className = `alert alert-${type} mt-2`;
  el.textContent = msg;
  dropZone.insertAdjacentElement('afterend', el);
  setTimeout(()=>el.remove(), 3000);
}
function renderList(){
  fileListWrap.innerHTML = '';
  Array.from(dt.files).forEach((f, idx)=>{
    const e = extOf(f.name);
    const pill = document.createElement('div');
    pill.className = 'file-pill';

    pill.innerHTML = `
      <div><i class="fa ${iconFor(e)}"></i></div>
      <div class="file-meta">
        <div class="file-name" title="${f.name}">${f.name}</div>
        <div class="file-sub">${fmtBytes(f.size)}</div>
      </div>
      <span class="file-badge">.${e || 'file'}</span>
      <div class="file-actions">
        <button type="button" class="btn-icon btn-remove-file" title="Remove" data-idx="${idx}"><i class="fa fa-times"></i></button>
      </div>
    `;
    fileListWrap.appendChild(pill);
  });

  // sync to actual input so Laravel receives it
  inputFiles.files = dt.files;
  countEl.value = dt.files.length;
}

function addFiles(files){
  let anyError = false;
  Array.from(files||[]).forEach(f=>{
    const v = validateFile(f);
    if(!v.ok){ anyError = true; toast(v.msg,'warning'); return; }
    dt.items.add(f);
  });
  renderList();
}

/* input change */
if (inputFiles){
  inputFiles.addEventListener('change', (e)=> addFiles(e.target.files));
}

/* drag & drop */
if (dropZone){
  ;['dragenter','dragover'].forEach(evt=>{
    dropZone.addEventListener(evt, (e)=>{ e.preventDefault(); e.stopPropagation(); dropZone.classList.add('dragover'); });
  });
  ;['dragleave','drop'].forEach(evt=>{
    dropZone.addEventListener(evt, (e)=>{ e.preventDefault(); e.stopPropagation(); dropZone.classList.remove('dragover'); });
  });
  dropZone.addEventListener('drop', (e)=>{
    const files = e.dataTransfer && e.dataTransfer.files;
    addFiles(files);
  });
}

/* remove a single file */
fileListWrap.addEventListener('click', (e)=>{
  const btn = e.target.closest('.btn-remove-file');
  if(!btn) return;
  const idx = parseInt(btn.dataset.idx,10);
  const next = new DataTransfer();
  Array.from(dt.files).forEach((f,i)=>{ if(i!==idx) next.items.add(f); });
  dt = next;
  renderList();
});

/* guard: on submit, prevent too many */
$('#shipment-form').on('submit', function(e){
  if(dt.files.length > MAX_FILES){
    e.preventDefault();
    toast(`Please keep attachments ≤ ${MAX_FILES}.`,'danger');
    return;
  }
});
</script>
@endpush
@endsection
