{{-- resources/views/backend/shipment/edit.blade.php --}}
@extends('backend.layout.main')

{{-- If your layout supports it, this collapses the sidebar immediately --}}
@section('sidebar_state', 'shrink')

@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />
<x-validation-error fieldName="product_code" />
<x-validation-error fieldName="qty" />

@php
  // decimals
  $dec = $general_setting->decimal ?? 2;

  // Units array for JS
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

  // Prefill: items
  $itemsInit = [];
  if ($shipment->relationLoaded('items') || method_exists($shipment, 'items')) {
      foreach ($shipment->items as $it) {
          // Get product name from relationship or fallback to code
          $productName = $it->product_code; // default fallback
          if ($it->product) {
              $productName = $it->product->name ?? $it->product_code;
          } elseif ($it->product_id) {
              // Fallback: find product from product lists
              $foundProduct = collect($lims_product_list_without_variant)->merge($lims_product_list_with_variant)
                  ->firstWhere('id', $it->product_id);
              if ($foundProduct) {
                  $productName = $foundProduct->name ?? $it->product_code;
              }
          }
          $itemsInit[] = [
              'id'           => $it->id,
              'product_id'   => $it->product_id,
              'product_code' => $it->product_code,
              'product_unit' => $it->product_unit,
              'qty'          => (float) $it->qty,
              'unit_price'   => (float) $it->net_unit_cost,  // per-unit
              'discount'     => (float) ($it->discount ?? 0),
              'subtotal'     => (float) ($it->subtotal ?? 0),
              'name'         => $productName, // Use actual product name
          ];
      }
  }

  // Prefill: packages
  $packagesInit = [];
  if ($shipment->relationLoaded('packages') || method_exists($shipment, 'packages')) {
      foreach ($shipment->packages as $p) {
          $packagesInit[] = [
              'id'              => $p->id,
              'packaging'       => $p->packaging,
              'qty'             => $p->qty ?? 1,
              'package_class'   => $p->package_class ?? '',
              'package_nmfc'    => $p->package_nmfc ?? '',
              'declared_value'  => $p->declared_value,
              'weight'          => $p->weight,
              'weight_unit'     => $p->weight_unit ?? 'kg',
              'length'          => $p->length,
              'width'           => $p->width,
              'height'          => $p->height,
              'dim_unit'        => $p->dim_unit ?? 'cm',
              'dimensions_note' => $p->dimensions_note,
          ];
      }
  }

  // Existing attachments
  $attachments = $shipment->attachments ?? collect();
@endphp

<style>
  /* PAGE-SCOPED: hide sidebar even before JS (fallback if layout doesn't yield) */
  .side-navbar{display:none!important}
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
  .table-items{table-layout:fixed;width:100%}
  .table-items td,.table-items th{vertical-align:middle;padding:10px 8px}
  .table-items .form-control{height:36px;padding:6px 10px;border:1px solid #ced4da}
  .table-items .form-control-sm{height:34px;font-size:13px}
  .table-items input[type="number"]{text-align:right}
  .table-items .sub-total{font-weight:600;text-align:right;font-size:14px}
  .table-items .del-row{width:36px;height:36px;display:inline-flex;align-items:center;justify-content:center;border:none;background:#dc3545;color:#fff;border-radius:4px;cursor:pointer}
  .table-items .del-row:hover{background:#c82333}
  .text-truncate{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .table-items tbody tr:hover{background-color:#f8f9fa}
  
  /* Autocomplete dropdown styling */
  .ui-autocomplete{max-height:300px;overflow-y:auto;overflow-x:hidden;z-index:9999!important;border:1px solid #ced4da;border-radius:4px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,.15)}
  .ui-autocomplete .ui-menu-item{padding:0;border:none}
  .ui-autocomplete .ui-menu-item-wrapper{padding:10px 12px;border-bottom:1px solid #f1f3f5;cursor:pointer}
  .ui-autocomplete .ui-menu-item-wrapper:hover,.ui-autocomplete .ui-menu-item-wrapper.ui-state-active{background:#f8f9fa;border-color:#dee2e6}

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

  /* Attachments table */
  .att-table td, .att-table th{vertical-align:middle}
  .att-pill{display:inline-block;padding:.15rem .5rem;border-radius:999px;background:#eef2ff;color:#4338ca;font-size:12px}

  /* FORCE packages grid to single column even on desktop */
  @media (min-width:768px){ .pkg-grid{ grid-template-columns: 1fr !important; gap:16px } }
  @media (min-width:1200px){ .pkg-grid{ grid-template-columns: 1fr !important; } }

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
          <div class="wizard-step" data-step="6">6) Attachments</div>
          <div class="wizard-step" data-step="7">7) Review</div>
        </div>

        <div class="card card-step">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0" style="color:#981a1c">Edit Shipment #{{ $shipment->id }}</h5>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-sm">Back</a>
          </div>

          <div class="card-body">
         <form method="POST" action="{{ route('shipments.update', $shipment) }}" id="shipment-form" enctype="multipart/form-data">
              @csrf
              @method('PUT')

              <div class="row">
                {{-- LEFT --}}
                <div class="col-lg-8">

                  {{-- STEP 1: SHIPPER --}}
                  <div class="step-pane active" id="step-1">
                    <h6 class="mb-3" style="border-bottom:1px solid #981a1c;color:#981a1c">Shipper (From)</h6>
                    <div class="row">
                      <div class="col-md-3 mb-3">
                        <label>PO# (optional)</label>
                        <input type="text" name="po_no" class="form-control" value="{{ old('po_no', $shipment->po_no) }}" placeholder="PO Number">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Reference No (optional)</label>
                        <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no', $shipment->reference_no) }}" placeholder="Reference # e.g. 123">
                        <x-validation-error fieldName="reference_no" />
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Buyer</label>
                        <select name="customer_id" class="selectpicker form-control"
                                data-live-search="true" data-none-selected-text="Select Customer...">
                          <option value="" disabled {{ old('customer_id', $shipment->customer_id) ? '' : 'selected' }}>Select Customer...</option>
                          @foreach($lims_customer_list as $customer)
                            <option value="{{$customer->id}}" {{ (string)old('customer_id', $shipment->customer_id) === (string)$customer->id ? 'selected' : '' }}>
                              {{$customer->name}} ({{$customer->company_name}})
                            </option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Shipment Status</label>
                        <select name="status" class="form-control">
                          @php $st = (string) old('status', $shipment->status); @endphp
                          <option value="1" {{ $st==='1' ? 'selected' : '' }}>Pending</option>
                          <option value="2" {{ $st==='2' ? 'selected' : '' }}>In Transit</option>
                          <option value="3" {{ $st==='3' ? 'selected' : '' }}>Delivered</option>
                          <option value="4" {{ $st==='4' ? 'selected' : '' }}>Returned</option>
                          <option value="5" {{ $st==='5' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                      </div>

                      <div class="col-md-3 mb-3">
                        <label>Company (optional)</label>
                        <input type="text" name="ship_from_company" value="{{ old('ship_from_company', $shipment->ship_from_company) }}" class="form-control" placeholder="Company Name">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Full Name *</label>
                        <input type="text" name="ship_from_first_name" value="{{ old('ship_from_first_name', $shipment->ship_from_first_name) }}" class="form-control required-field" placeholder="Full name" required>
                      </div>

                      <div class="col-md-6 mb-3 pos-relative">
                        <label>Address *</label>
                        <input type="text" id="ship_from_address_1" name="ship_from_address_1" value="{{ old('ship_from_address_1', $shipment->ship_from_address_1) }}" class="form-control required-field" placeholder="Street, number" required>
                        <div id="suggestions-from" class="osm-suggestions"></div>
                      </div>

                      <div class="col-md-3 mb-3">
                        <label>Country *</label>
                        <input type="text" name="ship_from_country" value="{{ old('ship_from_country', $shipment->ship_from_country) }}" class="form-control required-field" placeholder="Country" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>State *</label>
                        <input type="text" name="ship_from_state" value="{{ old('ship_from_state', $shipment->ship_from_state) }}" class="form-control required-field" placeholder="State / Region" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>City *</label>
                        <input type="text" name="ship_from_city" value="{{ old('ship_from_city', $shipment->ship_from_city) }}" class="form-control required-field" placeholder="City" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Postal Code *</label>
                        <input type="text" name="ship_from_zipcode" value="{{ old('ship_from_zipcode', $shipment->ship_from_zipcode) }}" class="form-control required-field" placeholder="e.g. 10001" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Contact *</label>
                        <input type="text" name="ship_from_contact" value="{{ old('ship_from_contact', $shipment->ship_from_contact) }}" class="form-control required-field" placeholder="Phone" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Email *</label>
                        <input type="email" name="ship_from_email" value="{{ old('ship_from_email', $shipment->ship_from_email) }}" class="form-control required-field" placeholder="name@company.com" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Dock Hours</label>
                        <input type="text" name="ship_from_dock_hours" value="{{ old('ship_from_dock_hours', $shipment->ship_from_dock_hours ?? '') }}" class="form-control" placeholder="e.g. 9:00 AM - 5:00 PM">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Lunch Hour</label>
                        <input type="text" name="ship_from_lunch_hour" value="{{ old('ship_from_lunch_hour', $shipment->ship_from_lunch_hour ?? '') }}" class="form-control" placeholder="e.g. 12:00 PM - 1:00 PM">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label>Pick up / Delivery Instructions</label>
                        <textarea name="ship_from_pickup_delivery_instructions" class="form-control" rows="2" placeholder="Special instructions for pickup">{{ old('ship_from_pickup_delivery_instructions', $shipment->ship_from_pickup_delivery_instructions ?? '') }}</textarea>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Appointment</label>
                        <input type="text" name="ship_from_appointment" value="{{ old('ship_from_appointment', $shipment->ship_from_appointment ?? '') }}" class="form-control" placeholder="e.g. Appointment required">
                      </div>
                      <div class="col-md-9 mb-3">
                        <label>Accessorial</label>
                        <textarea name="ship_from_accessorial" class="form-control" rows="2" placeholder="Additional services or requirements">{{ old('ship_from_accessorial', $shipment->ship_from_accessorial ?? '') }}</textarea>
                      </div>
                    </div>
                  </div>

                  {{-- STEP 2: RECIPIENT --}}
                  <div class="step-pane" id="step-2">
                    <h6 class="mb-3" style="border-bottom:1px solid #981a1c;color:#981a1c">Recipient (To)</h6>
                    <div class="row">
                      <div class="col-md-3 mb-3">
                        <label>Company (optional)</label>
                        <input type="text" name="ship_to_company" value="{{ old('ship_to_company', $shipment->ship_to_company) }}" class="form-control" placeholder="Company Name">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Full Name *</label>
                        <input type="text" name="ship_to_first_name" value="{{ old('ship_to_first_name', $shipment->ship_to_first_name) }}" class="form-control required-field" placeholder="Full name" required>
                      </div>

                      <div class="col-md-6 mb-3 pos-relative">
                        <label>Address *</label>
                        <input type="text" id="ship_to_address_1" name="ship_to_address_1" value="{{ old('ship_to_address_1', $shipment->ship_to_address_1) }}" class="form-control required-field" placeholder="Street, number" required>
                        <div id="suggestions-to" class="osm-suggestions"></div>
                      </div>

                      <div class="col-md-3 mb-3">
                        <label>Country *</label>
                        <input type="text" name="ship_to_country" value="{{ old('ship_to_country', $shipment->ship_to_country) }}" class="form-control required-field" placeholder="Country" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>State *</label>
                        <input type="text" name="ship_to_state" value="{{ old('ship_to_state', $shipment->ship_to_state) }}" class="form-control required-field" placeholder="State / Region" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>City *</label>
                        <input type="text" name="ship_to_city" value="{{ old('ship_to_city', $shipment->ship_to_city) }}" class="form-control required-field" placeholder="City" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Postal Code *</label>
                        <input type="text" name="ship_to_zipcode" value="{{ old('ship_to_zipcode', $shipment->ship_to_zipcode) }}" class="form-control required-field" placeholder="e.g. 75008" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Contact *</label>
                        <input type="text" name="ship_to_contact" value="{{ old('ship_to_contact', $shipment->ship_to_contact) }}" class="form-control required-field" placeholder="Phone" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Email *</label>
                        <input type="email" name="ship_to_email" value="{{ old('ship_to_email', $shipment->ship_to_email) }}" class="form-control required-field" placeholder="name@company.com" required>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Dock Hours</label>
                        <input type="text" name="ship_to_dock_hours" value="{{ old('ship_to_dock_hours', $shipment->ship_to_dock_hours ?? '') }}" class="form-control" placeholder="e.g. 9:00 AM - 5:00 PM">
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Lunch Hour</label>
                        <input type="text" name="ship_to_lunch_hour" value="{{ old('ship_to_lunch_hour', $shipment->ship_to_lunch_hour ?? '') }}" class="form-control" placeholder="e.g. 12:00 PM - 1:00 PM">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label>Pick up / Delivery Instructions</label>
                        <textarea name="ship_to_pickup_delivery_instructions" class="form-control" rows="2" placeholder="Special instructions for delivery">{{ old('ship_to_pickup_delivery_instructions', $shipment->ship_to_pickup_delivery_instructions ?? '') }}</textarea>
                      </div>
                      <div class="col-md-3 mb-3">
                        <label>Appointment</label>
                        <input type="text" name="ship_to_appointment" value="{{ old('ship_to_appointment', $shipment->ship_to_appointment ?? '') }}" class="form-control" placeholder="e.g. Appointment required">
                      </div>
                      <div class="col-md-9 mb-3">
                        <label>Accessorial</label>
                        <textarea name="ship_to_accessorial" class="form-control" rows="2" placeholder="Additional services or requirements">{{ old('ship_to_accessorial', $shipment->ship_to_accessorial ?? '') }}</textarea>
                      </div>

                      <div class="col-md-2 mb-3">
                        <label>Currency</label>
                        <select name="currency_id" id="currency-id" class="form-control selectpicker" data-none-selected-text="Select currency...">
                          <option value="" disabled {{ old('currency_id', $shipment->currency_id) ? '' : 'selected' }}>Select currency...</option>
                          @foreach($currency_list as $currency_data)
                            <option value="{{$currency_data->id}}" data-rate="{{$currency_data->exchange_rate}}"
                              {{ (string)old('currency_id', $shipment->currency_id) === (string)$currency_data->id ? 'selected' : '' }}>
                              {{$currency_data->code}}
                            </option>
                          @endforeach
                        </select>
                        <x-validation-error fieldName="currency_id" />
                      </div>
                      <div class="col-md-2 mb-3">
                        <label>Exchange Rate *</label>
                        <input class="form-control required-field" type="number" step="0.0001" id="exchange_rate" name="exchange_rate"
                               placeholder="Rate e.g. 278.50" value="{{ old('exchange_rate', $shipment->exchange_rate ?? ($currency->exchange_rate ?? 1)) }}" required>
                        <x-validation-error fieldName="exchange_rate" />
                      </div>
                    </div>
                  </div>

                  {{-- STEP 3: PACKAGES --}}
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
                            <th id="total">{{ number_format($shipment->total_cost ?? 0, $dec, '.', '') }}</th>
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
                          <option value="0" {{ (old('order_tax_rate', $shipment->order_tax_rate) ?? 0) == 0 ? 'selected' : '' }}>No Tax</option>
                          @foreach($lims_tax_list as $tax)
                            <option value="{{$tax->rate}}" {{ (string)old('order_tax_rate', $shipment->order_tax_rate) === (string)$tax->rate ? 'selected' : '' }}>
                              {{$tax->name}}
                            </option>
                          @endforeach
                        </select>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label><strong>Discount</strong></label>
                        <input type="number" step="0.1" name="order_discount" id="order_discount" class="form-control" placeholder="0.00"
                               value="{{ old('order_discount', $shipment->order_discount ?? 0) }}">
                      </div>
                      <div class="col-md-4 mb-3">
                        <label><strong>Shipping Cost</strong></label>
                        <input type="number" step="0.01" name="shipping_cost" id="shipping_cost_input" class="form-control" placeholder="0.00"
                               value="{{ old('shipping_cost', $shipment->shipping_cost ?? 0) }}">
                      </div>

                      <div class="col-md-12 mb-3">
                        <label>Comments / Shipping Instructions</label>
                        <textarea name="comments" rows="3" class="form-control" placeholder="Any special handling, references, etc.">{{ old('comments', $shipment->comments) }}</textarea>
                      </div>
                    </div>
                  </div>

                  {{-- STEP 6: ATTACHMENTS --}}
                  <div class="step-pane" id="step-6">
                    <h6 class="mb-3" style="border-bottom:1px solid #981a1c;color:#981a1c">Attachments</h6>

                    <div class="alert alert-info py-2">
                      Upload PDFs or Images (JPG/PNG/WebP). Max 5 MB per file.
                    </div>

                    {{-- Existing attachments --}}
                    <div class="table-responsive mb-3">
                      <table class="table table-bordered att-table">
                        <thead>
                          <tr>
                            <th style="width:44%">File</th>
                            <th style="width:18%">Type</th>
                            <th style="width:18%">Size</th>
                            <th style="width:20%">Actions</th>
                          </tr>
                        </thead>
                        <tbody>
                        @forelse($attachments as $att)
                          @php
                            $disk = $att->disk ?? 'public';
                            $path = $att->path ?? '';
                            $ext = strtolower(pathinfo($att->filename ?? '', PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $isExcel = in_array($ext, ['xls', 'xlsx']);
                            $isDoc = in_array($ext, ['doc', 'docx']);
                            
                            // Generate correct URL
                            if ($path) {
                              if ($disk === 'public') {
                                // Check if path starts with 'shipment/' (new format) or 'shipment_attachments/' (old format)
                                if (strpos($path, 'shipment/') === 0) {
                                  $url = asset($path);
                                } else {
                                  $url = asset('storage/' . $path);
                                }
                              } else {
                                $url = \Illuminate\Support\Facades\Storage::disk($disk)->exists($path) ? \Illuminate\Support\Facades\Storage::disk($disk)->url($path) : null;
                              }
                            } else {
                              $url = null;
                            }
                          @endphp
                          <tr>
                            <td>
                              <input type="text" class="form-control form-control-sm" name="attach_title[{{ $att->id }}]" value="{{ $att->title ?? ($att->original_name ?? 'Attachment') }}">
                              <div class="small text-muted mt-1">{{ $att->original_name ?? basename($att->path ?? '') }}</div>
                            </td>
                            <td>
                              <span class="att-pill">{{ strtoupper($att->type ?? 'other') }}</span>
                            </td>
                            <td>{{ number_format(($att->size ?? 0)/1024, 0) }} KB</td>
                            <td class="d-flex align-items-center" style="gap:.5rem">
                              @if($url)
                                @if($isImage)
                                  <button type="button" class="btn btn-outline-primary btn-sm view-image-btn" data-image-url="{{ $url }}" data-image-name="{{ $att->original_name ?? basename($att->path ?? '') }}">
                                    <i class="fa fa-eye"></i> View
                                  </button>
                                @else
                                  <a class="btn btn-outline-primary btn-sm" href="{{ $url }}" target="_blank" title="Open {{ $isExcel ? 'Excel' : ($isDoc ? 'Word' : 'File') }}">
                                    <i class="fa fa-{{ $isExcel ? 'file-excel-o' : ($isDoc ? 'file-word-o' : 'eye') }}"></i> View
                                  </a>
                                @endif
                                <a class="btn btn-outline-secondary btn-sm" href="{{ $url }}" download>
                                  <i class="fa fa-download"></i> Download
                                </a>
                              @else
                                <span class="text-muted">N/A</span>
                              @endif
                              <div class="form-check ml-2">
                                <input class="form-check-input" type="checkbox" name="delete_attachment_ids[]" value="{{ $att->id }}" id="delAtt{{ $att->id }}">
                                <label class="form-check-label small text-danger" for="delAtt{{ $att->id }}">Delete</label>
                              </div>
                            </td>
                          </tr>
                        @empty
                          <tr><td colspan="4" class="text-muted">No attachments yet.</td></tr>
                        @endforelse
                        </tbody>
                      </table>
                    </div>

                    {{-- Upload new --}}
                    <div class="card">
                      <div class="card-body">
                        <div class="form-group">
                          <label class="mb-1">Add files</label>
                          <input type="file" name="new_attachments[]" id="new_attachments" class="form-control" multiple
                                 accept="application/pdf,image/jpeg,image/png,image/webp,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                          <small class="text-muted">You can select multiple files (PDF, Images, Excel, Word).</small>
                        </div>

                        <div id="new-files-list" class="mt-2" style="display:none">
                          <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                              <thead>
                                <tr>
                                  <th style="width:40%">File</th>
                                  <th style="width:30%">Title (optional)</th>
                                  <th style="width:20%">Type</th>
                                  <th style="width:10%">Size</th>
                                </tr>
                              </thead>
                              <tbody></tbody>
                            </table>
                          </div>
                        </div>

                      </div>
                    </div>
                  </div>

                  {{-- STEP 7: REVIEW --}}
                  <div class="step-pane" id="step-7">
                    <h6 class="mb-3" style="border-bottom:1px solid #981a1c;color:#981a1c">Review & Update</h6>
                    <div class="table-responsive">
                      <table class="table table-bordered table-condensed">
                        <tr>
                          <td><strong>Items</strong> <span class="pull-right" id="item">{{ number_format($shipment->item ?? 0, 0) }}</span></td>
                          <td><strong>Total</strong> <span class="pull-right" id="subtotal">{{ number_format($shipment->total_cost ?? 0, $dec, '.', '') }}</span></td>
                          <td><strong>Order Tax</strong> <span class="pull-right" id="order_tax">{{ number_format($shipment->order_tax ?? 0, $dec, '.', '') }}</span></td>
                          <td><strong>Shipping Cost</strong> <span class="pull-right" id="shipping_cost">{{ number_format($shipment->shipping_cost ?? 0, $dec, '.', '') }}</span></td>
                          <td><strong>Grand Total</strong> <span class="pull-right" id="grand_total">{{ number_format($shipment->grand_total ?? 0, $dec, '.', '') }}</span></td>
                        </tr>
                      </table>
                    </div>

                    {{-- Hidden mirrors --}}
                    <input type="hidden" name="total_qty"        value="{{ old('total_qty', $shipment->total_qty) }}">
                    <input type="hidden" name="total_discount"   value="{{ number_format($shipment->total_discount ?? 0, $dec, '.', '') }}">
                    <input type="hidden" name="total_tax"        value="{{ number_format($shipment->total_tax ?? 0, $dec, '.', '') }}">
                    <input type="hidden" name="total_cost"       value="{{ number_format($shipment->total_cost ?? 0, $dec, '.', '') }}">
                    <input type="hidden" name="item"             value="{{ old('item', $shipment->item) }}">
                    <input type="hidden" name="order_tax"        value="{{ number_format($shipment->order_tax ?? 0, $dec, '.', '') }}">
                    <input type="hidden" name="grand_total"      value="{{ number_format($shipment->grand_total ?? 0, $dec, '.', '') }}">
                    <input type="hidden" name="paid_amount"      value="{{ number_format($shipment->paid_amount ?? 0, $dec, '.', '') }}">
                    <input type="hidden" name="payment_status"   value="{{ old('payment_status', $shipment->payment_status ?? 1) }}">

                    <button type="submit" class="btn btn-primary" id="submit-btn">Update Shipment</button>
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

            </form>
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
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
/* ---------------- Inject UNITS & Prefill from PHP ---------------- */
const UNITS = @json($unitsArr);
const ITEMS_INIT = @json($itemsInit);
const PACKAGES_INIT = @json($packagesInit);

/* ---------------- Wizard ---------------- */
let currentStep=1,totalSteps=7;
const panes=[...document.querySelectorAll('.step-pane')];
const badges=[...document.querySelectorAll('.wizard-step')];
function showStep(n){
  panes.forEach((p,i)=>p.classList.toggle('active',(i+1)===n));
  badges.forEach((b,i)=>{b.classList.toggle('active',(i+1)===n);b.classList.toggle('done',(i+1)<n)});
  document.getElementById('prev-step').disabled=(n===1);
  // Initialize autocomplete when step 4 (items) is shown
  if(n === 4) {
    console.log('Step 4 activated, initializing autocomplete...');
    setTimeout(function(){
      initProductAutocomplete();
      // Test if autocomplete is working
      setTimeout(function(){
        const $input = $('#lims_productcodeSearch');
        if ($input.hasClass('ui-autocomplete-input')) {
          console.log('Autocomplete initialized successfully');
        } else {
          console.error('Autocomplete failed to initialize');
        }
      }, 200);
    }, 200);
  }
}
document.getElementById('next-step').addEventListener('click',()=>{ if(!validateStep(currentStep))return; if(currentStep<totalSteps){currentStep++;showStep(currentStep);} });
document.getElementById('prev-step').addEventListener('click',()=>{ if(currentStep>1){currentStep--;showStep(currentStep);} });
badges.forEach(b=>b.addEventListener('click',()=>{const to=parseInt(b.dataset.step||"0",10); if(to && to<=currentStep && validateStep(Math.min(currentStep,to))){currentStep=to;showStep(currentStep);}}));

/* ---------------- Currency / Rate (prefill) ---------------- */
let exchangeRate = Number('{{ old('exchange_rate', $shipment->exchange_rate ?? ($currency->exchange_rate ?? 1)) }}') || 1;
$('#currency-id').val('{{ old('currency_id', $shipment->currency_id) }}').change();
$('#exchange_rate').val(exchangeRate);
$('#currency-id').on('change', function(){
  const rate = $(this).find(':selected').data('rate') || exchangeRate;
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

/* ---------------- Product search dataset (same as create) ---------------- */
<?php 
$productArray = []; 
// Debug: Check if product lists exist
$debugInfo = [
  'without_variant_count' => count($lims_product_list_without_variant ?? []),
  'with_variant_count' => count($lims_product_list_with_variant ?? [])
];

foreach($lims_product_list_without_variant ?? [] as $product) {
  if ($product && isset($product->code)) {
    $productArray[] = 
      htmlspecialchars($product->code ?? '').'|' .
      preg_replace('/[\n\r]/'," ",htmlspecialchars($product->name ?? ''))." (".($product->title ?? '').")" . '|' .
      ($product->id ?? '').'|' .
      (float)($product->price ?? $product->cost ?? 0);
  }
}
foreach($lims_product_list_with_variant ?? [] as $product) {
  if ($product && isset($product->item_code)) {
    $productArray[] =
      htmlspecialchars($product->item_code ?? '').'|' .
      preg_replace('/[\n\r]/'," ",htmlspecialchars($product->name ?? ''))." (".($product->brand_title ?? $product->title ?? '').")" . '|' .
      ($product->id ?? '').'|' .
      (float)($product->price ?? $product->cost ?? 0);
  }
}
?>
var lims_product_code = [
<?php if (!empty($productArray)): ?>
{!! '"'.implode('","', array_filter($productArray)).'"' !!}
<?php endif; ?>
];

// Debug PHP side
console.log('PHP Debug - Products without variant:', <?php echo $debugInfo['without_variant_count']; ?>);
console.log('PHP Debug - Products with variant:', <?php echo $debugInfo['with_variant_count']; ?>);
console.log('PHP Debug - Total in array:', <?php echo count($productArray); ?>);

// Debug: Log product count and sample
if (typeof lims_product_code !== 'undefined') {
  console.log('Products loaded for autocomplete:', lims_product_code.length);
  if (lims_product_code.length > 0) {
    console.log('Sample product:', lims_product_code[0]);
    console.log('Full array:', lims_product_code);
  } else {
    console.warn('Product array is empty!');
  }
} else {
  console.error('lims_product_code array is not defined!');
}

/* ---------------- Items (EDIT flavour) ---------------- */
let base_cost = []; // base cost in base unit (before unit op)

function unitOptionsHtml() {
  if (!Array.isArray(UNITS) || UNITS.length === 0) {
    return '<option value="0" data-op="*" data-val="1">Unit</option>';
  }
  return UNITS.map(function(u, i){
    return '<option value="'+i+'" data-op="'+u.op+'" data-val="'+u.val+'">'+u.name+'</option>';
  }).join('');
}
function unitIndexByName(name){
  if(!name || !Array.isArray(UNITS)) return 0;
  for (let i=0;i<UNITS.length;i++) {
    if ((UNITS[i].name||'').toLowerCase() === String(name).toLowerCase()) return i;
  }
  return 0;
}

function addFromCode(val){
  const p = val.split('|');
  const code = p[0]||'';
  const name = (p[1]||'').trim();
  const id   = p[2]||'';
  const cost = parseFloat(p[3]||'0')||0;
  insertRowAtEnd({id, code, name, cost});
}

function parseRowUnitCost(rowIdx, $row) {
  const unitIdx = parseInt($row.find('.unit-select').val(), 10) || 0;
  const op  = (UNITS[unitIdx] && UNITS[unitIdx].op) ? UNITS[unitIdx].op : '*';
  const val = Number((UNITS[unitIdx] && UNITS[unitIdx].val) ? UNITS[unitIdx].val : 1);
  const base = (base_cost[rowIdx] || 0) * (Number.isFinite(exchangeRate)?exchangeRate:1);
  const ui = (op === '*') ? (base * val) : (base / (val || 1));
  return Number.isFinite(ui) ? ui : base;
}

function insertRowAtEnd(item){
  const $row = $(`
    <tr>
      <td>
        <div class="fw-semibold text-truncate" title="${item.name}">${item.name}</div>
        <div class="small text-muted">${item.code}</div>
        <input type="hidden" class="item-id"       name="item_id[]">
        <input type="hidden" class="product-id"    name="product_id[]"   value="${item.id}">
        <input type="hidden" class="product-code" name="product_code[]" value="${item.code}">
        <input type="hidden" class="net_unit_cost" name="net_unit_cost[]">
        <input type="hidden" class="discount"      name="discount[]" value="0">
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

  $("#items-table tbody").append($row);
  base_cost.push(item.cost||0);
  const idx = $('#items-table tbody tr').length - 1;

  let unitPrice = parseRowUnitCost(idx, $row);
  if (!Number.isFinite(unitPrice)) unitPrice = (item.cost || 0) * (Number.isFinite(exchangeRate)?exchangeRate:1);
  $row.find('.row-unit-price').val(unitPrice.toFixed({{ $dec }})).trigger('input');
  calculateRow(idx);
}

function insertExistingRow(it){
  const displayName = it.name || it.product_code || ('#'+it.product_id);
  const displayCode = it.product_code || '';
  const $row = $(`
    <tr>
      <td>
        <div class="fw-semibold text-truncate" title="${displayName}">${displayName}</div>
        <div class="small text-muted">${displayCode}</div>
        <input type="hidden" class="item-id"       name="item_id[]">
        <input type="hidden" class="product-id"    name="product_id[]">
        <input type="hidden" class="product-code"  name="product_code[]">
        <input type="hidden" class="net_unit_cost" name="net_unit_cost[]">
        <input type="hidden" class="discount"      name="discount[]" value="0">
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
  
  $("#items-table tbody").append($row);
  base_cost.push(0); // not used for existing rows
  const idx = $('#items-table tbody tr').length - 1;

  // fill fields
  $row.find('.item-id').val(it.id||'');
  $row.find('.product-id').val(it.product_id||'');
  $row.find('.product-code').val(it.product_code||'');
  $row.find('.qty').val(Number(it.qty||1));
  $row.find('.row-unit-price').val(Number(it.unit_price||0).toFixed({{ $dec }}));
  const uIdx = unitIndexByName(it.product_unit||'');
  $row.find('.unit-select').val(String(uIdx));

  // subtotal comes from qty*unit_price (recalc to stay in sync)
  calculateRow(idx);
}

/* events */
$('#items-table').on('input', '.qty', function(){
  const i = $(this).closest('tr').index();
  if ((parseFloat(this.value)||0) < 1) { this.value = 1; }
  calculateRow(i);
});
$('#items-table').on('change', '.unit-select', function(){
  const i = $(this).closest('tr').index();
  const $row = $('#items-table tbody tr').eq(i);

  if ((base_cost[i]||0) > 0) {
    let unitPrice = parseRowUnitCost(i, $row);
    if (!Number.isFinite(unitPrice)) unitPrice = 0;
    $row.find('.row-unit-price').val(unitPrice.toFixed({{ $dec }})).trigger('input');
  }
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
    if ((base_cost[i]||0) > 0) {
      let unitPrice = parseRowUnitCost(i, $row);
      if (!Number.isFinite(unitPrice)) unitPrice = 0;
      $row.find('.row-unit-price').val(unitPrice.toFixed({{ $dec }}));
    }
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

/* Charges change */
$('#order_tax_rate, #order_discount, #shipping_cost_input').on('input change', function(){
  recalcTotals(); updateSummary();
});

/* Validate & submit */
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

/* Summary */
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

/* ---------------- OSM Autocomplete ---------------- */
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

            const fullParts = item.display_name.split(',').map(p => p.trim());
            const filteredParts = fullParts.filter(part => part !== state && part !== postcode && part !== country);

            div.textContent = filteredParts.join(', ');

            div.addEventListener('click', ()=>{
              input.value = filteredParts.join(', ');
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

/* ---------------- Packages (EDIT) ---------------- */
let pkgIndex = 0;

function vwDivisor(dimUnit){ return dimUnit === 'in' ? 139 : 5000; }
function fmt(n, d=2){ n = parseFloat(n); return Number.isFinite(n) ? n.toFixed(d) : (0).toFixed(d); }

function packageCard(i){
  return `
  <div class="pkg-card package" data-index="\${i}">
    <div class="pkg-header">
      <div class="pkg-title">
        <span class="pkg-index">\${i+1}</span>
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
          <select name="packaging[]" class="form-control">
            <option value="">Select</option>
            <option value="your_packaging">Your Packaging</option>
            <option value="box">Box</option>
            <option value="envelope">Envelope</option>
            <option value="tube">Tube</option>
            <option value="pallet">Pallet</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div class="pkg-field">
          <label class="form-label">Qty</label>
          <input type="number" step="1" min="1" name="package_qty[]" class="form-control" placeholder="1" value="1">
        </div>

        <div class="pkg-field">
          <label class="form-label">Package Class</label>
          <input type="text" name="package_class[]" class="form-control" placeholder="e.g. 70">
        </div>

        <div class="pkg-field">
          <label class="form-label">Package NMFC</label>
          <input type="text" name="package_nmfc[]" class="form-control" placeholder="e.g. 123456">
        </div>

        <div class="pkg-field">
          <label class="form-label">Commodity Name</label>
          <input type="text" name="commodity_name[]" class="form-control" placeholder="e.g. Electronics, Clothing">
        </div>

        <div class="pkg-field">
          <label class="form-label">Declared Value</label>
          <div class="input-icon">
            <i class="fa fa-dollar-sign"></i>
            <input type="number" step="0.01" name="declared_value[]" class="form-control" placeholder="0.00">
          </div>
        </div>

        <div class="pkg-field">
          <label class="form-label">Weight</label>
          <div class="pkg-inline">
            <input type="number" step="0.001" name="weight[]" class="form-control pkg-weight" placeholder="0.000">
            <select name="weight_unit[]" class="form-control pkg-weight-unit" style="max-width:110px">
              <option value="kg" selected>kg</option>
              <option value="lb">lb</option>
            </select>
          </div>
        </div>

        <div class="pkg-field">
          <label class="form-label">Dimensions</label>
          <div class="pkg-inline">
            <input type="number" step="0.1" name="length[]" class="form-control pkg-l" placeholder="L">
            <input type="number" step="0.1" name="width[]"  class="form-control pkg-w" placeholder="W">
            <input type="number" step="0.1" name="height[]" class="form-control pkg-h" placeholder="H">
            <select name="dim_unit[]" class="form-control pkg-dim-unit" style="max-width:110px">
              <option value="cm" selected>cm</option>
              <option value="in">in</option>
            </select>
          </div>
          <div class="pkg-note">Volumetric = (L×W×H) / <span class="vw-div">5000</span> → <strong class="vw-out">0.00</strong> <span class="vw-unit">kg</span></div>
        </div>

        <div class="pkg-field" style="grid-column:1/-1">
          <label class="form-label">Dimensions Note</label>
          <input type="text" name="dimensions_note[]" class="form-control pkg-note-input" placeholder="e.g., 30×20×10 cm / 2.50 kg">
        </div>

        {{-- keep IDs for update --}}
        <input type="hidden" name="package_id[]" class="package-id">
      </div>
    </div>
  </div>`;
}

function addPackage(prefill){
  const i = pkgIndex++;
  $('#packages').append(packageCard(i));
  const $card = $('#packages .package').last();

  if (prefill){
    $card.find('select[name="packaging[]"]').val(prefill.packaging ?? '');
    $card.find('input[name="package_qty[]"]').val(prefill.qty ?? prefill.package_qty ?? 1);
    $card.find('input[name="package_class[]"]').val(prefill.package_class ?? '');
    $card.find('input[name="package_nmfc[]"]').val(prefill.package_nmfc ?? '');
    $card.find('input[name="declared_value[]"]').val(prefill.declared_value ?? '');
    $card.find('input[name="weight[]"]').val(prefill.weight ?? '');
    $card.find('select[name="weight_unit[]"]').val(prefill.weight_unit ?? 'kg');
    $card.find('input[name="length[]"]').val(prefill.length ?? '');
    $card.find('input[name="width[]"]').val(prefill.width ?? '');
    $card.find('input[name="height[]"]').val(prefill.height ?? '');
    $card.find('select[name="dim_unit[]"]').val(prefill.dim_unit ?? 'cm');
    $card.find('input[name="dimensions_note[]"]').val(prefill.dimensions_note ?? '');
    $card.find('.package-id').val(prefill.id || '');
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

function collectPkgValues($c){
  return {
    packaging:        $c.find('select[name="packaging[]"]').val(),
    qty:              $c.find('input[name="package_qty[]"]').val() || 1,
    package_class:    $c.find('input[name="package_class[]"]').val(),
    package_nmfc:     $c.find('input[name="package_nmfc[]"]').val(),
    commodity_name:   $c.find('input[name="commodity_name[]"]').val(),
    declared_value:   $c.find('input[name="declared_value[]"]').val(),
    weight:           $c.find('input[name="weight[]"]').val(),
    weight_unit:      $c.find('select[name="weight_unit[]"]').val(),
    length:           $c.find('input[name="length[]"]').val(),
    width:            $c.find('input[name="width[]"]').val(),
    height:           $c.find('input[name="height[]"]').val(),
    dim_unit:         $c.find('select[name="dim_unit[]"]').val(),
    dimensions_note:  $c.find('input[name="dimensions_note[]"]').val(),
    id:               $c.find('.package-id').val()
  };
}

$('#packages').on('input change', '.pkg-weight, .pkg-weight-unit, .pkg-l, .pkg-w, .pkg-h, .pkg-dim-unit, .pkg-note-input', function(){
  const $card = $(this).closest('.package');
  updatePkgComputed($card);
});

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

function reIndexPackages(){
  $('#packages .package').each(function(idx){
    $(this).attr('data-index', idx);
    $(this).find('.pkg-index').text(idx+1);
  });
}

$('#packages').sortable({
  handle: '.pkg-handle',
  items: '.package',
  placeholder: 'pkg-card placeholder',
  forcePlaceholderSize: true,
  update: function(){ reIndexPackages(); }
});

/* -------- Prefill existing rows -------- */
(function prefill(){
  // ITEMS
  if (Array.isArray(ITEMS_INIT) && ITEMS_INIT.length) {
    ITEMS_INIT.forEach(it => insertExistingRow(it));
  }
  // PACKAGES
  if (Array.isArray(PACKAGES_INIT) && PACKAGES_INIT.length) {
    PACKAGES_INIT.forEach(p => addPackage(p));
  } else {
    addPackage(); // ensure at least one
  }

  // Set initial totals/summary
  recalcTotals();
  updateSummary();
})();

// Initialize autocomplete function
function initProductAutocomplete() {
  if (typeof $.ui !== 'undefined' && $.ui.autocomplete) {
    const $input = $('#lims_productcodeSearch');
    if ($input.length) {
      // Destroy existing autocomplete if any
      if ($input.hasClass('ui-autocomplete-input')) {
        $input.autocomplete('destroy');
      }
      
      // Check if lims_product_code array exists and has data
      if (typeof lims_product_code === 'undefined' || !Array.isArray(lims_product_code) || lims_product_code.length === 0) {
        console.warn('lims_product_code array is empty or not defined');
        return;
      }
      
      $input.autocomplete({
        source: function(req,res){
          if (!lims_product_code || lims_product_code.length === 0) {
            console.log('No products available');
            res([]);
            return;
          }
          const term = req.term || '';
          console.log('Search term:', term, 'Products array length:', lims_product_code);
          
          // Debug: Show first product
          if (lims_product_code.length > 0) {
            console.log('First product in array:', lims_product_code[0]);
          }
          
          // Use simpler regex pattern that matches anywhere in the string
          const escapedTerm = $.ui.autocomplete.escapeRegex(term);
          const rx = new RegExp(escapedTerm, "i");
          
          const matches = $.grep(lims_product_code, function(it){ 
            const matched = rx.test(it);
            if (matched) {
              console.log('Match found:', it);
            }
            return matched;
          });
          
          console.log('Matches found:', matches.length);
          if (matches.length > 0) {
            console.log('First match:', matches[0]);
          }
          res(matches);
        },
        minLength: 1,
        select: function(e, ui){ 
          if (ui.item && ui.item.value) {
            addFromCode(ui.item.value); 
            $(this).val(''); 
          }
          return false; 
        },
        response: function(e, ui){ 
          if(ui.content && ui.content.length === 1 && ui.content[0].value){ 
            addFromCode(ui.content[0].value); 
            $('#lims_productcodeSearch').val(''); 
          } 
        },
        focus: function(){ return false; }
      });
    }
  } else {
    console.warn('jQuery UI Autocomplete not loaded');
  }
}

// Initialize autocomplete when document is ready and step 4 might be active
$(document).ready(function(){
  console.log('Document ready, checking for step 4...');
  console.log('Products array available:', typeof lims_product_code !== 'undefined' ? lims_product_code.length : 'undefined');
  // Check if step 4 is active on page load
  if ($('#step-4').hasClass('active')) {
    console.log('Step 4 is active on page load, initializing autocomplete...');
    setTimeout(function(){
      initProductAutocomplete();
    }, 500);
  } else {
    console.log('Step 4 not active, will initialize when user navigates to it');
  }
});

/* -------- Attachments: show selected files table -------- */
const fileInput = document.getElementById('new_attachments');
const listWrap  = document.getElementById('new-files-list');
const listBody  = listWrap ? listWrap.querySelector('tbody') : null;

if (fileInput && listWrap && listBody) {
  fileInput.addEventListener('change', function(){
    listBody.innerHTML = '';
    const files = Array.from(this.files || []);
    if (!files.length) { listWrap.style.display='none'; return; }
    files.forEach((f, i) => {
      const type = (f.type || '').split('/')[1] || 'file';
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td class="text-truncate" title="${f.name}">${f.name}</td>
        <td><input type="text" name="new_titles[]" class="form-control form-control-sm" placeholder="Optional title"></td>
        <td>${type.toUpperCase()}</td>
        <td>${Math.round(f.size/1024)} KB</td>
      `;
      listBody.appendChild(tr);
    });
    listWrap.style.display='block';
  });
}

// Image view functionality
$(document).ready(function() {
  // Use event delegation for dynamically added buttons
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
</script>
@endpush
@endsection
