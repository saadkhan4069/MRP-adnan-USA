{{-- resources/views/purchases/create.blade.php --}}

@extends('backend.layout.main')

@section('content')

{{-- Flash messages --}}
@if(session('message'))
  <div class="alert alert-success">{{ session('message') }}</div>
@endif
@if(session('not_permitted'))
  <div class="alert alert-danger">{{ session('not_permitted') }}</div>
@endif

{{-- Validation error summary (stay on page until all ok) --}}
@if ($errors->any())
  <div class="alert alert-danger">
    <strong>Please fix the errors below and submit again:</strong>
    <ul class="mb-0 mt-2">
      @foreach ($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
@endif

<style>
  .is-invalid{border-color:#dc3545!important}
  .bootstrap-select.is-invalid .dropdown-toggle{
    border-color:#dc3545!important;
    box-shadow:0 0 0 .2rem rgba(220,53,69,.25)
  }
</style>

<section class="forms">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">

        <div class="card">
          <div class="card-header d-flex align-items-center">
            <h4>{{ __('db.Add Purchase') }}</h4>
          </div>

          <div class="card-body">
            <p class="italic">
              <small>
                {{ __('db.The field labels marked with * are required input fields') }}.
                &nbsp; <strong>ETS, ETA, Lead Time are important fields.</strong>
              </small>
            </p>

            {!! Form::open(['route' => 'purchases.store', 'method' => 'post', 'files' => true, 'id' => 'purchase-form', 'novalidate']) !!}

            <div class="row">
              {{-- PO# --}}
              <div class="col-md-4">
                <div class="form-group">
                  <label>PO# *</label>
                  <input type="text" name="po_no"
                    class="form-control @error('po_no') is-invalid @enderror"
                    value="{{ old('po_no') }}" placeholder="Please Enter PO#" required>
                  @error('po_no') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              {{-- Date --}}
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('db.date') }}</label>
                  <input type="text" name="created_at"
                    class="form-control date @error('created_at') is-invalid @enderror"
                    value="{{ old('created_at') }}" placeholder="{{ __('db.Choose date') }}">
                  @error('created_at') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              {{-- Reference --}}
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('db.Reference No') }}</label>
                  <input type="text" name="reference_no"
                    class="form-control @error('reference_no') is-invalid @enderror"
                    value="{{ old('reference_no') }}">
                  @error('reference_no') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              {{-- Warehouse --}}
              <div class="col-md-4" id="select1">
                <div class="form-group">
                  <label>{{ __('Warehouse / Production') }} *</label>
                  <select name="warehouse_id"
                    class="selectpicker form-control @error('warehouse_id') is-invalid @enderror"
                    data-live-search="true" title="Select warehouse..." required>
                    @foreach($lims_warehouse_list as $warehouse)
                      <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                        {{ $warehouse->company }}
                      </option>
                    @endforeach
                  </select>
                  @error('warehouse_id') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>
              </div>

              {{-- Customer (fixed duplicate name attr) --}}
              <div class="col-md-4">
                <div class="form-group">
                  <label>Customer *</label>
                  <select name="customer_id"
                    class="selectpicker form-control @error('customer_id') is-invalid @enderror"
                    data-live-search="true" title="Select Customer..." required>
                    @foreach($lims_customer_list as $customer)
                      <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name .' ('. $customer->company_name .')' }}
                      </option>
                    @endforeach
                  </select>
                  @error('customer_id') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>
              </div>

              {{-- Status --}}
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('db.Purchase Status') }}</label>
                  <select name="status" class="form-control @error('status') is-invalid @enderror">
                    <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>{{ __('db.Recieved') }}</option>
                    <option value="2" {{ old('status') == 2 ? 'selected' : '' }}>{{ __('db.Partial') }}</option>
                    <option value="3" {{ old('status') == 3 ? 'selected' : '' }}>{{ __('db.Pending') }}</option>
                    <option value="4" {{ old('status') == 4 ? 'selected' : '' }}>{{ __('db.Ordered') }}</option>
                    <option value="5" {{ old('status') == 5 ? 'selected' : '' }}>{{ __('In Process') }}</option>
                    <option value="6" {{ old('status') == 6 ? 'selected' : '' }}>{{ __('Cancel') }}</option>
                    <option value="7" {{ old('status') == 7 ? 'selected' : '' }}>{{ __('Complete') }}</option>
                  </select>
                  @error('status') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              {{-- Document --}}
              <div class="col-md-4">
                <div class="form-group">
                  <label>
                    {{ __('db.Attach Document') }}
                    <i class="dripicons-question" data-toggle="tooltip"
                      title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt (max 10MB)"></i>
                  </label>
                  <input type="file" name="document" class="form-control @error('document') is-invalid @enderror">
                  @error('document') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              {{-- Currency --}}
              <div class="col-md-2">
                <div class="form-group">
                  <label>{{ __('db.Currency') }} *</label>
                  <select name="currency_id" id="currency-id"
                          class="form-control selectpicker @error('currency_id') is-invalid @enderror"
                          data-live-search="true" required>
                    @foreach($currency_list as $currency_data)
                      <option value="{{ $currency_data->id }}"
                        data-rate="{{ $currency_data->exchange_rate }}"
                        {{ (old('currency_id', $currency->id ?? null) == $currency_data->id) ? 'selected' : '' }}>
                        {{ $currency_data->code }}
                      </option>
                    @endforeach
                  </select>
                  @error('currency_id') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              {{-- Exchange rate --}}
              <div class="col-md-2">
                <div class="form-group mb-0">
                  <label>{{ __('db.Exchange Rate') }} *</label>
                </div>
                <div class="form-group d-flex">
                  <input class="form-control @error('exchange_rate') is-invalid @enderror" type="text"
                         id="exchange_rate" name="exchange_rate"
                         value="{{ old('exchange_rate', $currency->exchange_rate ?? 1) }}" required>
                  <div class="input-group-append">
                    <span class="input-group-text" data-toggle="tooltip" title="Currency exchange rate">i</span>
                  </div>
                  @error('exchange_rate') <small class="text-danger d-block">{{ $message }}</small> @enderror
                </div>
              </div>

              {{-- Custom fields (preserved) --}}
              @foreach($custom_fields as $field)
                @if(!$field->is_admin || \Auth::user()->role_id == 1)
                  @php $fname = str_replace(' ', '_', strtolower($field->name)); @endphp
                  <div class="{{ 'col-md-'.$field->grid_value }}">
                    <div class="form-group">
                      <label>{{ $field->name }} @if($field->is_required)*@endif</label>
                      @if($field->type == 'text')
                        <input type="text" name="{{ $fname }}" value="{{ old($fname, $field->default_value) }}"
                               class="form-control" @if($field->is_required) required @endif>
                      @elseif($field->type == 'number')
                        <input type="number" name="{{ $fname }}" value="{{ old($fname, $field->default_value) }}"
                               class="form-control" @if($field->is_required) required @endif>
                      @elseif($field->type == 'textarea')
                        <textarea rows="5" name="{{ $fname }}" class="form-control" @if($field->is_required) required @endif>{{ old($fname, $field->default_value) }}</textarea>
                      @elseif($field->type == 'checkbox')
                        <br>
                        @foreach(explode(',', $field->option_value) as $value)
                          <label class="mr-2">
                            <input type="checkbox" name="{{ $fname }}[]"
                                   value="{{ $value }}"
                                   {{ in_array($value, (array) old($fname, $field->default_value ? [$field->default_value] : [])) ? 'checked' : '' }}
                                   @if($field->is_required) required @endif> {{ $value }}
                          </label>
                        @endforeach
                      @elseif($field->type == 'radio_button')
                        <br>
                        @foreach(explode(',', $field->option_value) as $value)
                          <label class="radio-inline mr-2">
                            <input type="radio" name="{{ $fname }}" value="{{ $value }}"
                                   {{ old($fname, $field->default_value) == $value ? 'checked' : '' }}
                                   @if($field->is_required) required @endif> {{ $value }}
                          </label>
                        @endforeach
                      @elseif($field->type == 'select')
                        <select class="form-control" name="{{ $fname }}" @if($field->is_required) required @endif>
                          @foreach(explode(',', $field->option_value) as $value)
                            <option value="{{ $value }}" {{ old($fname, $field->default_value) == $value ? 'selected' : '' }}>
                              {{ $value }}
                            </option>
                          @endforeach
                        </select>
                      @elseif($field->type == 'multi_select')
                        <select class="form-control" name="{{ $fname }}[]" multiple @if($field->is_required) required @endif>
                          @foreach(explode(',', $field->option_value) as $value)
                            <option value="{{ $value }}" {{ in_array($value, (array) old($fname, $field->default_value ? [$field->default_value] : [])) ? 'selected' : '' }}>
                              {{ $value }}
                            </option>
                          @endforeach
                        </select>
                      @elseif($field->type == 'date_picker')
                        <input type="text" name="{{ $fname }}" value="{{ old($fname, $field->default_value) }}"
                               class="form-control date" @if($field->is_required) required @endif>
                      @endif
                    </div>
                  </div>
                @endif
              @endforeach

              {{-- Product selector --}}
              <div class="col-md-12 mt-3">
                <label>{{ __('db.Select Product') }} *</label>
                <div class="search-box input-group">
                  <button class="btn btn-secondary" type="button"><i class="fa fa-barcode"></i></button>
                  <input type="text" name="product_code_name" id="lims_productcodeSearch"
                         placeholder="{{ __('db.Please type product code and select') }}"
                         class="form-control" />
                </div>
                @error('product_id') <small class="text-danger d-block">{{ $message }}</small> @enderror
              </div>
            </div>

            {{-- Order table (same columns as your version) --}}
            <div class="row mt-4">
              <div class="col-md-12">
                <h5>{{ __('db.Order Table') }} *</h5>
                <div class="table-responsive mt-3">
                  <table id="myTable" class="table table-hover order-list">
                    <thead>
                      <tr>
                        <th>{{ __('db.name') }}</th>
                        <th>{{ __('db.Code') }}</th>
                        <th>{{ __('db.Quantity') }}</th>
                        <th class="recieved-product-qty d-none">{{ __('db.Recieved') }}</th>
                        <th>{{ __('db.Batch No') }}</th>
                        <th>{{ __('Lot No') }}</th>
                        <th>{{ __('db.Expired Date') }}</th>
                        <th>{{ __('db.Supplier') }}</th>
                        <th>{{ __('db.Net Unit Cost') }}</th>
                        <th>{{ __('Shipping') }}</th>
                        <th style="display:none;">{{ __('db.Discount') }}</th>
                        <th>{{ __('db.Tax') }}</th>
                        <th>{{ __('db.Subtotal') }}</th>
                        <th><i class="dripicons-trash"></i></th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot class="tfoot active">
                      <th colspan="2">{{ __('db.Total') }}</th>
                      <th id="total-qty">0</th>
                      <th class="recieved-product-qty d-none"></th>
                      <th></th>
                      <th></th>
                      <th></th>
                      <th style="display:none;" id="total-discount">{{ number_format(0, $general_setting->decimal, '.', '') }}</th>
                      <th id="total-tax">{{ number_format(0, $general_setting->decimal, '.', '') }}</th>
                      <th id="total">{{ number_format(0, $general_setting->decimal, '.', '') }}</th>
                      <th><i class="dripicons-trash"></i></th>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>

            {{-- Hidden totals --}}
            <input type="hidden" name="total_qty">
            <input type="hidden" name="total_discount">
            <input type="hidden" name="total_tax">
            <input type="hidden" name="total_cost">
            <input type="hidden" name="item">
            <input type="hidden" name="order_tax">
            <input type="hidden" name="grand_total">
            <input type="hidden" name="paid_amount" value="{{ number_format(0, $general_setting->decimal, '.', '') }}">
            <input type="hidden" name="payment_status" value="1">

            {{-- Order-level fields --}}
            <div class="row mt-3">
              <div class="col-md-4">
                <div class="form-group">
                  <label>{{ __('db.Order Tax') }}</label>
                  <select class="form-control" name="order_tax_rate">
                    <option value="0">{{ __('db.No Tax') }}</option>
                    @foreach($lims_tax_list as $tax)
                      <option value="{{ $tax->rate }}" {{ old('order_tax_rate') == $tax->rate ? 'selected' : '' }}>
                        {{ $tax->name }}
                      </option>
                    @endforeach
                  </select>
                  @error('order_tax_rate') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label><strong>{{ __('db.Discount') }}</strong></label>
                  <input type="number" name="order_discount" step="0.1"
                         class="form-control @error('order_discount') is-invalid @enderror"
                         value="{{ old('order_discount') }}">
                  @error('order_discount') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>
              <div class="col-md-4" style="display:none;">
                <div class="form-group">
                  <label><strong>{{ __('db.Shipping Cost') }}</strong></label>
                  <input type="number" name="shipping_cost" step="0.1"
                         class="form-control @error('shipping_cost') is-invalid @enderror"
                         value="{{ old('shipping_cost') }}">
                  @error('shipping_cost') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
              </div>

              <div class="col-md-4">
                <div class="form-group">
                  <label><strong>{{ __('Signature') }}
                    <small>This is an electronic signature for company use only</small>
                  </strong></label>
                  <input type="text" name="signature" class="form-control" value="{{ old('signature') }}">
                </div>
              </div>
            </div>

            {{-- Comments --}}
            <div class="row">
              <div class="col-md-12">
                <div class="form-group">
                  <label>{{ __('Internal Comments') }}</label>
                  <textarea rows="5" class="form-control" name="note">{{ old('note') }}</textarea>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <label>{{ __('Comment / Instrutions') }}</label>
                  <textarea rows="5" class="form-control" name="comments">{{ old('comments') }}</textarea>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-group">
                  <label>{{ __('Shipping / Instrutions') }}</label>
                  <textarea rows="4" class="form-control" name="ship_instruction">{{ old('ship_instruction') }}</textarea>
                </div>
              </div>
            </div>

            <div class="form-group">
              <button type="submit" class="btn btn-primary" id="submit-btn">{{ __('db.submit') }}</button>
            </div>

            {!! Form::close() !!}
          </div>
        </div>

        {{-- Totals summary --}}
        <div class="container-fluid">
          <table class="table table-bordered table-condensed totals">
            <td><strong>{{ __('db.Items') }}</strong>
              <span class="pull-right" id="item">{{ number_format(0, $general_setting->decimal, '.', '') }}</span>
            </td>
            <td><strong>{{ __('db.Total') }}</strong>
              <span class="pull-right" id="subtotal">{{ number_format(0, $general_setting->decimal, '.', '') }}</span>
            </td>
            <td><strong>{{ __('db.Order Tax') }}</strong>
              <span class="pull-right" id="order_tax">{{ number_format(0, $general_setting->decimal, '.', '') }}</span>
            </td>
            <td><strong>{{ __('db.Shipping Cost') }}</strong>
              <span class="pull-right" id="shipping_cost">{{ number_format(0, $general_setting->decimal, '.', '') }}</span>
            </td>
            <td><strong>{{ __('db.grand total') }}</strong>
              <span class="pull-right" id="grand_total">{{ number_format(0, $general_setting->decimal, '.', '') }}</span>
            </td>
          </table>
        </div>

        {{-- Edit Modal (same UI as your original) --}}
        <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
          <div role="document" class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 id="modal-header" class="modal-title"></h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
              </div>
              <div class="modal-body">
                <form>
                  <div class="row modal-element">
                    <div class="col-md-4 form-group">
                      <label>{{ __('db.Quantity') }}</label>
                      <input type="number" step="0.0001" name="edit_qty" class="form-control" step="any">
                    </div>
                    <div style="display:none" class="col-md-4 form-group">
                      <label>{{ __('db.Unit Discount') }}</label>
                      <input type="number" name="edit_discount" class="form-control" step="any">
                    </div>
                    <div class="col-md-4 form-group">
                      <label>{{ __('db.Unit Cost') }}</label>
                      <input type="number" name="edit_unit_cost" class="form-control" step="any">
                    </div>
                    @php
                      $tax_name_all[] = 'No Tax'; $tax_rate_all[] = 0;
                      foreach($lims_tax_list as $tax){ $tax_name_all[] = $tax->name; $tax_rate_all[] = $tax->rate; }
                    @endphp
                    <div class="col-md-4 form-group">
                      <label>{{ __('db.Tax Rate') }}</label>
                      <select name="edit_tax_rate" class="form-control selectpicker">
                        @foreach($tax_name_all as $key => $name)
                          <option value="{{ $key }}">{{ $name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4 form-group">
                      <label>{{ __('db.Product Unit') }}</label>
                      <select name="edit_unit" class="form-control selectpicker"></select>
                    </div>

                    <div class="col-md-4 form-group">
                      <label>{{ __('ETS') }}</label>
                      <input type="text" name="ets_date" class="form-control date" placeholder="{{ __('db.Choose date') }}"/>
                    </div>
                    <div class="col-md-4 form-group">
                      <label>{{ __('ETA') }}</label>
                      <input type="text" name="eta_date" class="form-control date" placeholder="{{ __('db.Choose date') }}"/>
                    </div>
                    <div class="col-md-4 form-group">
                      <label>{{ __('Lead Time') }}</label>
                      <input type="text" name="etd_date" class="form-control" placeholder="{{ __('Lead TIme') }}"/>
                    </div>
                    <div class="col-md-4 form-group">
                      <label>{{ __('MOQ') }}</label>
                      <input type="text" name="moq" class="form-control moq" placeholder="{{ __('Enter Minimum Order Quantity') }}" />
                    </div>
                    <div class="col-md-4 form-group">
                      <label>{{ __('Shipping Cost') }}</label>
                      <input type="text" name="ship_cost" class="form-control ship_cost" placeholder="{{ __('Enter Shipping Cost') }}" />
                    </div>
                    <div class="col-md-4 form-group">
                      <label>{{ __('Shipping Term') }}</label>
                      <input type="text" name="ship_term" class="form-control ship_term" placeholder="{{ __('Pre-Paid OR Post-Paid') }}" />
                    </div>
                  </div>
                  <button type="button" name="update_btn" class="btn btn-primary">{{ __('db.update') }}</button>
                </form>
              </div>
            </div>
          </div>
        </div>

      </div> {{-- col --}}
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">
  // ===== Keep menu state =====
  $("ul#purchase").siblings('a').attr('aria-expanded','true');
  $("ul#purchase").addClass("show");
  $("ul#purchase #purchase-create-menu").addClass("active");

  // ===== Init pickers =====
  $('.selectpicker').selectpicker({style:'btn-link'}).selectpicker('refresh');
  $('[data-toggle="tooltip"]').tooltip();

  // ===== Currency wiring (your original logic compatible) =====
  var currency = @json($currency ?? []);
  var exchangeRate = parseFloat({{ json_encode(old('exchange_rate', $currency->exchange_rate ?? 1)) }});
  var currencyChange = false;
  if (currency && currency.id) { $('#currency-id').val(currency.id); $('.selectpicker').selectpicker('refresh'); }

  $('#currency-id').change(function(){
    var rate = $(this).find(':selected').data('rate') || 1;
    $('#exchange_rate').val(rate);
    exchangeRate = parseFloat(rate);
    currencyChange = true;
    $("table.order-list tbody .qty").each(function(index) {
      rowindex = index;
      checkQuantity($(this).val(), true);
    });
  });

  // ====== Everything below is your original JS (lightly cleaned) ======
  // (Copied from your snippet; only minor safety tweaks—structure preserved)

  var product_code = [], product_name = [], product_qty = [];
  var product_cost = [], product_discount = [], tax_rate = [], tax_name = [], tax_method = [];
  var unit_name = [], unit_operator = [], unit_operation_value = [], is_imei = [];
  var temp_unit_name = [], temp_unit_operator = [], temp_unit_operation_value = [];
  var eta_date = [], ets_date = [], etd_date = [], moq = [], ship_cost = [], ship_term = [];
  var rowindex, customer_group_rate, row_product_cost;

  // Status toggles
  $('select[name="status"]').on('change', function() {
    if($(this).val() == 2){
      $(".recieved-product-qty").removeClass("d-none");
      $(".qty").each(function() {
        rowindex = $(this).closest('tr').index();
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val($(this).val());
      });
    }
    else if($(this).val() == 3 || $(this).val() == 4) {
      $(".recieved-product-qty").addClass("d-none");
      $(".recieved").each(function(){ $(this).val(0); });
    }
    else {
      $(".recieved-product-qty").addClass("d-none");
      $(".qty").each(function() {
        rowindex = $(this).closest('tr').index();
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val($(this).val());
      });
    }
  });

  // Autocomplete source (same as your build)
  <?php $productArray = []; ?>
  var lims_product_code = [
    @foreach($lims_product_list_without_variant as $product)
      <?php $productArray[] = htmlspecialchars($product->code) . '|' . preg_replace('/[\n\r]/', "<br>", htmlspecialchars($product->name))." | (".$product->title.")"; ?>
    @endforeach
    @foreach($lims_product_list_with_variant as $product)
      <?php $productArray[] = htmlspecialchars($product->item_code) . '|' . preg_replace('/[\n\r]/', "<br>", htmlspecialchars($product->name))." | (".$product->title.")"; ?>
    @endforeach
    <?php echo  '"'.implode('","', $productArray).'"'; ?>
  ];
  var lims_productcodeSearch = $('#lims_productcodeSearch');

  lims_productcodeSearch.autocomplete({
    source: function(request, response) {
      var matcher = new RegExp(".?" + $.ui.autocomplete.escapeRegex(request.term), "i");
      response($.grep(lims_product_code, function(item) { return matcher.test(item); }));
    },
    response: function(event, ui) {
      if (ui.content.length == 1) {
        var data = ui.content[0].value;
        $(this).autocomplete("close");
        productSearch(data);
      }
    },
    select: function(event, ui) {
      var data = ui.item.value;
      productSearch(data);
    }
  });

  // Datepicker for per-row expiry
  $('body').on('focus',".expired-date", function() {
    $(this).datepicker({
      format: "yyyy-mm-dd",
      startDate: "{{ date('Y-m-d', strtotime('+ 1 days')) }}",
      autoclose: true,
      todayHighlight: true
    });
  });

  // ===== Table events =====
  $("#myTable").on('input', '.qty', function() {
    rowindex = $(this).closest('tr').index();
    if($(this).val() < 1 && $(this).val() !== '') {
      $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(1);
      alert("Quantity can't be less than 1");
    }
    checkQuantity($(this).val(), true);
  });

  $("table.order-list tbody").on("click", ".ibtnDel", function() {
    rowindex = $(this).closest('tr').index();
    product_cost.splice(rowindex,1); product_discount.splice(rowindex,1);
    tax_rate.splice(rowindex,1); tax_name.splice(rowindex,1); tax_method.splice(rowindex,1);
    unit_name.splice(rowindex,1); unit_operator.splice(rowindex,1); unit_operation_value.splice(rowindex,1);
    is_imei.splice(rowindex,1);
    $(this).closest("tr").remove();
    calculateTotal();
  });

  // ===== Edit modal wiring (same as your logic) =====
  $("table.order-list").on("click", ".edit-product", function() {
    $('button[name="update_btn"]').attr("id",$(this).attr('data-no'));
    var n = $(this).attr('data-no');
    var etsdate = $(".ets-date"+n).val(),
        etadate = $(".eta-date"+n).val(),
        etddate = $(".etd-date"+n).val(),
        moqv = $(".moq"+n).val(),
        ship_termv = $(".ship_term"+n).val(),
        ship_costv = $(".ship_cost"+n).val();

    $('input[name="ets_date"]').val(etsdate || '');
    $('input[name="eta_date"]').val(etadate || '');
    $('input[name="etd_date"]').val(etddate || '');
    $('input[name="moq"]').val(moqv || '');
    $('input[name="ship_term"]').val(ship_termv || '');
    $('input[name="ship_cost"]').val(ship_costv || '');

    rowindex = $(this).closest('tr').index();
    $(".imei-section").remove();

    if(is_imei[rowindex]) {
      var imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val();
      var htmlText = '';
      if(!imeiNumbers || !imeiNumbers.length) {
        htmlText = `<div class="col-md-8 form-group imei-section">
          <label>IMEI or Serial Numbers</label>
          <div class="table-responsive ml-2">
            <table id="imei-table" class="table table-hover"><tbody>
              <tr><td><input type="text" class="form-control imei-numbers" name="imei_numbers[]" /></td>
              <td><button type="button" class="imei-del btn btn-sm btn-danger">X</button></td></tr>
            </tbody></table>
          </div>
          <button type="button" class="btn btn-info btn-sm ml-2 mb-3" id="imei-add-more"><i class="ion-plus"></i> Add More</button>
        </div>`;
      } else {
        var imeiArrays = imeiNumbers.split(",");
        htmlText = `<div class="col-md-8 form-group imei-section">
          <label>IMEI or Serial Numbers</label>
          <div class="table-responsive ml-2">
            <table id="imei-table" class="table table-hover"><tbody>`;
        for (var i = 0; i < imeiArrays.length; i++) {
          htmlText += `<tr><td><input type="text" class="form-control imei-numbers" name="imei_numbers[]" value="${imeiArrays[i].trim()}" /></td>
            <td><button type="button" class="imei-del btn btn-sm btn-danger">X</button></td></tr>`;
        }
        htmlText += `</tbody></table></div>
          <button type="button" class="btn btn-info btn-sm ml-2 mb-3" id="imei-add-more"><i class="ion-plus"></i> Add More</button>
        </div>`;
      }
      $("#editModal .modal-element").append(htmlText);
    }

    var row_product_name = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').text();
    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
    $('#modal-header').text(row_product_name + '(' + row_product_code + ')');

    var qty = $(this).closest('tr').find('.qty').val();
    $('input[name="edit_qty"]').val(qty);
    $('input[name="edit_discount"]').val(parseFloat(product_discount[rowindex]).toFixed({{ $general_setting->decimal }}));

    unitConversion();
    $('input[name="edit_unit_cost"]').val(row_product_cost.toFixed({{ $general_setting->decimal }}));

    var tax_name_all = @json($tax_name_all ?? []);
    var pos = tax_name_all.indexOf(tax_name[rowindex]);
    $('select[name="edit_tax_rate"]').val(pos);

    temp_unit_name = (unit_name[rowindex]).split(','); temp_unit_name.pop();
    temp_unit_operator = (unit_operator[rowindex]).split(','); temp_unit_operator.pop();
    temp_unit_operation_value = (unit_operation_value[rowindex]).split(','); temp_unit_operation_value.pop();
    $('select[name="edit_unit"]').empty();
    $.each(temp_unit_name, function(key, value) {
      $('select[name="edit_unit"]').append('<option value="' + key + '">' + value + '</option>');
    });
    $('.selectpicker').selectpicker('refresh');
  });

  // IMEI add/remove
  $(document).on("click", "#imei-add-more", function() {
    var newRow = $("<tr>");
    var cols = '';
    cols += '<td><input type="text" class="form-control imei-numbers" name="imei_numbers[]" /></td>';
    cols += '<td><button type="button" class="imei-del btn btn-sm btn-danger">X</button></td>';
    newRow.append(cols);
    $("table#imei-table tbody").append(newRow);
    var edit_qty = parseFloat($('input[name="edit_qty"]').val()||0);
    $('input[name="edit_qty"]').val(edit_qty+1);
  });
  $(document).on("click", "table#imei-table tbody .imei-del", function() {
    $(this).closest("tr").remove();
    var edit_qty = parseFloat($('input[name="edit_qty"]').val()||1);
    $('input[name="edit_qty"]').val(Math.max(1, edit_qty-1));
  });

  // Update from modal
  $('button[name="update_btn"]').on("click", function() {
    var n = $(this).attr('id');
    var etsdate = $('input[name="ets_date"]').val(),
        etadate = $('input[name="eta_date"]').val(),
        etddate = $('input[name="etd_date"]').val(),
        moqv = $('input[name="moq"]').val(),
        ship_termv = $('input[name="ship_term"]').val(),
        ship_costv = $('input[name="ship_cost"]').val();

    $('.ets-date'+n).val(etsdate);
    $('.eta-date'+n).val(etadate);
    $('.etd-date'+n).val(etddate);
    $('.ship_term'+n).val(ship_termv);
    $('.ship_cost'+n).val(ship_costv);
    $('.moq'+n).val(moqv);

    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.ship_costt').text(ship_costv || '0');

    if(is_imei[rowindex]) {
      var imeiNumbers = '';
      $("#editModal .imei-numbers").each(function(i) {
        imeiNumbers += (i ? ',' : '') + $(this).val().trim();
      });
      $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val(imeiNumbers);
    }

    var edit_discount = parseFloat($('input[name="edit_discount"]').val()||0);
    var edit_qty = parseFloat($('input[name="edit_qty"]').val()||1);
    var edit_unit_cost = parseFloat($('input[name="edit_unit_cost"]').val()||0);
    if (edit_discount > edit_unit_cost) { alert('Invalid Discount Input!'); return; }
    if (edit_qty < 1) { $('input[name="edit_qty"]').val(1); edit_qty = 1; alert("Quantity can't be less than 1"); }

    var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
    var row_unit_operation_value = parseFloat(unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(",")));
    var tax_rate_all = @json($tax_rate_all ?? []);

    tax_rate[rowindex] = parseFloat(tax_rate_all[$('select[name="edit_tax_rate"]').val()] || 0);
    tax_name[rowindex] = $('select[name="edit_tax_rate"] option:selected').text();

    if (row_unit_operator == '*') product_cost[rowindex] = edit_unit_cost / row_unit_operation_value;
    else product_cost[rowindex] = edit_unit_cost * row_unit_operation_value;

    product_discount[rowindex] = edit_discount;

    var position = $('select[name="edit_unit"]').val();
    var temp_operator = temp_unit_operator[position];
    var temp_operation_value = temp_unit_operation_value[position];
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit').val(temp_unit_name[position]);

    temp_unit_name.splice(position,1); temp_unit_operator.splice(position,1); temp_unit_operation_value.splice(position,1);
    temp_unit_name.unshift($('select[name="edit_unit"] option:selected').text());
    temp_unit_operator.unshift(temp_operator);
    temp_unit_operation_value.unshift(temp_operation_value);
    unit_name[rowindex] = temp_unit_name.toString() + ',';
    unit_operator[rowindex] = temp_unit_operator.toString() + ',';
    unit_operation_value[rowindex] = temp_unit_operation_value.toString() + ',';
    checkQuantity(edit_qty, false);
  });

  // productSearch (AJAX) — same endpoint you already have
  flag2 = 1;
  function productSearch(data) {
    $.ajax({
      type: 'GET',
      url: 'lims_product_search',
      data: { data: data },
      success: function(data) {
        var flag = 1;
        $(".product-code").each(function(i) {
          if ($(this).val() == data[1]) {
            rowindex = i;
            var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) + 1;
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
            if($('select[name="status"]').val() == 1 || $('select[name="status"]').val() == 2)
              $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .recieved').val(qty);
            calculateRowProductData(qty);
            flag = 0;
          }
        });
        $("input[name='product_code_name']").val('');
        if(flag){
          var newRow = $("<tr>");
          var cols = '';
          temp_unit_name = (data[6]).split(',');
          cols += '<td>'+ data[0] +'<button type="button" class="edit-product btn btn-link" data-toggle="modal" data-no='+flag2+' data-target="#editModal"><i class="dripicons-document-edit"></i></button></td>';
          cols += '<td>'+ data[1] +'</td>';
          cols += '<td><input type="number" class="form-control qty" step="0.0001" name="qty[]" value="1" required/></td>';
          if($('select[name="status"]').val() == 1)
            cols += '<td class="recieved-product-qty d-none"><input type="number" step="0.0001" class="form-control recieved" name="recieved[]" value="1" /></td>';
          else if($('select[name="status"]').val() == 2)
            cols += '<td class="recieved-product-qty"><input type="number"  step="0.0001"class="form-control recieved" name="recieved[]" value="1" /></td>';
          else
            cols += '<td class="recieved-product-qty d-none"><input type="number" step="0.0001" class="form-control recieved" name="recieved[]" value="0" /></td>';

          if(data[10]) {
            cols += '<td><input type="text" class="form-control batch-no" name="batch_no[]" required/></td>';
            cols += '<td><input type="text" class="form-control lot-no" name="lot_no[]" required/></td>';
            cols += '<td><input type="text" class="form-control expired-date" name="expired_date[]" required/></td>';
          } else {
            cols += '<td><input type="text" class="form-control batch-no" name="batch_no[]" disabled/></td>';
            cols += '<td><input type="text" class="form-control lot-no" name="lot_no[]" disabled/></td>';
            cols += '<td><input type="text" class="form-control expired-date" name="expired_date[]" disabled/></td>';
          }

          let supplierOptions = `<td><select name="supplier_name[]" class="form-control" title="Select Supplier">
              @foreach($lims_supplier_list as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->company_name . ' (' . $supplier->name . ')' }}</option>
              @endforeach
            </select></td>`;

          cols += supplierOptions;
          cols += '<td class="net_unit_cost"></td>';
          cols += '<td style="display:none;" class="discount">{{ number_format(0, $general_setting->decimal, ".", "") }}</td>';
          cols += '<td class="ship_costt">{{ number_format(0, $general_setting->decimal, ".", "") }}</td>';
          cols += '<td class="tax"></td>';
          cols += '<td class="sub-total"></td>';
          cols += '<td><button type="button" class="ibtnDel btn btn-md btn-danger">{{ __("db.delete") }}</button></td>';
          cols += '<input type="hidden" class="product-code" name="product_code[]" value="'+ data[1] +'"/>';
          cols += '<input type="hidden" class="product-id" name="product_id[]" value="'+ data[9] +'"/>';
          cols += '<input type="hidden" class="purchase-unit" name="purchase_unit[]" value="'+ temp_unit_name[0] +'"/>';
          cols += '<input type="hidden" class="net_unit_cost" name="net_unit_cost[]" />';
          cols += '<input type="hidden" class="discount-value" name="discount[]" />';
          cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="'+ data[3] +'"/>';
          cols += '<input type="hidden" class="tax-value" name="tax[]" />';
          cols += '<input type="hidden" class="subtotal-value" name="subtotal[]" />';
          cols += '<input type="hidden" class="imei-number" name="imei_number[]" />';
          cols += '<input type="hidden" class="original-cost" value="'+ data[2] +'" />';
          cols += '<input type="hidden" class="eta-date'+flag2+'" name="eta_date[]" />';
          cols += '<input type="hidden" class="ets-date'+flag2+'" name="ets_date[]" />';
          cols += '<input type="hidden" class="etd-date'+flag2+'" name="etd_date[]" />';
          cols += '<input type="hidden" class="moq'+flag2+'" name="moq[]" />';
          cols += '<input type="hidden" class="ship_term'+flag2+'" name="ship_term[]" />';
          cols += '<input type="hidden" class="ship_cost'+flag2+'" name="ship_cost[]" />';

          newRow.append(cols);
          $("table.order-list tbody").prepend(newRow);
          rowindex = newRow.index();

          product_cost.splice(rowindex,0, parseFloat(data[2]));
          product_discount.splice(rowindex,0, '{{ number_format(0, $general_setting->decimal, ".", "") }}');
          tax_rate.splice(rowindex,0, parseFloat(data[3]));
          tax_name.splice(rowindex,0, data[4]);
          tax_method.splice(rowindex,0, data[5]);
          unit_name.splice(rowindex,0, data[6]);
          unit_operator.splice(rowindex,0, data[7]);
          unit_operation_value.splice(rowindex,0, data[8]);
          is_imei.splice(rowindex, 0, data[11]);

          checkQuantity(1, true);
          if(data[11]) {
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.edit-product').click();
          }
          flag2++;
        }
      }
    });
  }

  function checkQuantity(purchase_qty, flag) {
    $('#editModal').modal('hide');
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(purchase_qty);
    var status = $('select[name="status"]').val();
    if(status == '1' || status == '2')
      $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val(purchase_qty);
    else
      $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val(0);
    if(flag)
      product_cost[rowindex] = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.original-cost').val() * exchangeRate;
    calculateRowProductData(purchase_qty);
  }

  function calculateRowProductData(quantity) {
    unitConversion();
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount').text((product_discount[rowindex] * quantity).toFixed({{ $general_setting->decimal }}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[rowindex] * quantity).toFixed({{ $general_setting->decimal }}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex].toFixed({{ $general_setting->decimal }}));

    if (tax_method[rowindex] == 1) {
      var net_unit_cost = row_product_cost - product_discount[rowindex];
      var tax = net_unit_cost * quantity * (tax_rate[rowindex] / 100);
      var sub_total = (net_unit_cost * quantity) + tax;
    } else {
      var sub_total_unit = row_product_cost - product_discount[rowindex];
      var net_unit_cost = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
      var tax = (sub_total_unit - net_unit_cost) * quantity;
      var sub_total = sub_total_unit * quantity;
    }

    var $row = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')');
    $row.find('.net_unit_cost').text(net_unit_cost.toFixed({{ $general_setting->decimal }}));
    $row.find('.net_unit_cost').val(net_unit_cost.toFixed({{ $general_setting->decimal }}));
    $row.find('.tax').text(tax.toFixed({{ $general_setting->decimal }}));
    $row.find('.tax-value').val(tax.toFixed({{ $general_setting->decimal }}));
    $row.find('.sub-total').text(sub_total.toFixed({{ $general_setting->decimal }}));
    $row.find('.subtotal-value').val(sub_total.toFixed({{ $general_setting->decimal }}));

    calculateTotal();
  }

  function unitConversion() {
    var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
    var row_unit_operation_value = parseFloat(unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(",")));
    if (row_unit_operator == '*') row_product_cost = product_cost[rowindex] * row_unit_operation_value;
    else row_product_cost = product_cost[rowindex] / row_unit_operation_value;
  }

  function calculateTotal() {
    var total_qty = 0;
    $(".qty").each(function(){ total_qty += parseFloat($(this).val()||0); });
    $("#total-qty").text(total_qty);
    $('input[name="total_qty"]').val(total_qty);

    var total_discount = 0;
    $(".discount").each(function(){ total_discount += parseFloat($(this).text()||0); });
    $("#total-discount").text(total_discount.toFixed({{ $general_setting->decimal }}));
    $('input[name="total_discount"]').val(total_discount.toFixed({{ $general_setting->decimal }}));

    var total_tax = 0;
    $(".tax").each(function(){ total_tax += parseFloat($(this).text()||0); });
    $("#total-tax").text(total_tax.toFixed({{ $general_setting->decimal }}));
    $('input[name="total_tax"]').val(total_tax.toFixed({{ $general_setting->decimal }}));

    var total = 0;
    $(".sub-total").each(function(){ total += parseFloat($(this).text()||0); });
    $("#total").text(total.toFixed({{ $general_setting->decimal }}));
    $('input[name="total_cost"]').val(total.toFixed({{ $general_setting->decimal }}));
    calculateGrandTotal();
  }

  function calculateGrandTotal() {
    var itemCount = $('table.order-list tbody tr:last').index();
    var total_qty = parseFloat($('#total-qty').text()||0);
    var subtotal = parseFloat($('#total').text()||0);
    var order_tax_rate = parseFloat($('select[name="order_tax_rate"]').val()||0);

    var order_discount = parseFloat($('input[name="order_discount"]').val()||0);
    if(currencyChange) order_discount = order_discount * exchangeRate;

    // Sum of per-row shipping
    var totalShippingCost = 0;
    $('input[name="ship_cost[]"]').each(function() {
      var v = parseFloat($(this).val()); if(!isNaN(v)) totalShippingCost += v;
    });
    var shipping_cost = parseFloat(totalShippingCost || 0);
    if(currencyChange) shipping_cost = shipping_cost * exchangeRate;

    itemCount = ++itemCount + '(' + total_qty + ')';
    var order_tax = (subtotal - order_discount) * (order_tax_rate / 100);
    var grand_total = (subtotal + order_tax + shipping_cost) - order_discount;

    $('#item').text(itemCount);
    $('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
    $('#subtotal').text(subtotal.toFixed({{ $general_setting->decimal }}));
    $('#order_tax').text(order_tax.toFixed({{ $general_setting->decimal }}));
    $('input[name="order_tax"]').val(order_tax.toFixed({{ $general_setting->decimal }}));
    $('#shipping_cost').text(shipping_cost.toFixed({{ $general_setting->decimal }}));
    $('input[name="shipping_cost"]').val(shipping_cost);
    $('#grand_total').text(grand_total.toFixed({{ $general_setting->decimal }}));
    $('input[name="grand_total"]').val(grand_total.toFixed({{ $general_setting->decimal }}));
    currencyChange = false;
  }

  $('input[name="order_discount"]').on("input", calculateGrandTotal);
  $('input[name="shipping_cost"]').on("input", calculateGrandTotal);
  $('select[name="order_tax_rate"]').on("change", calculateGrandTotal);

  // prevent accidental form submit on Enter except textarea/buttons
  $(window).keydown(function(e){
    if (e.which == 13) {
      var $targ = $(e.target);
      if (!$targ.is("textarea") && !$targ.is(":button,:submit")) {
        var focusNext = false;
        $(this).find(":input:visible:not([disabled],[readonly]), a").each(function(){
          if (this === e.target) { focusNext = true; }
          else if (focusNext){ $(this).focus(); return false; }
        });
        return false;
      }
    }
  });

  // Client-side minimal guard; server will still validate and keep you on page
  $('#purchase-form').on('submit', function (e) {
    let errors = [];
    if (!$('input[name="po_no"]').val().trim()) errors.push('PO Number is required.');
    if (!$('select[name="warehouse_id"]').val()) errors.push('Warehouse is required.');
    if (!$('select[name="customer_id"]').val()) errors.push('Customer is required.');
    const rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) errors.push('Please insert product to the order table.');
    if (errors.length) { e.preventDefault(); alert(errors.join('\n')); return false; }
    $("#submit-btn").prop('disabled', true);
  });
</script>
<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
@endpush
