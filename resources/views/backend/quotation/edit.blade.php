@extends('backend.layout.main')
@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />
<x-validation-error fieldName="product_code" />
<x-validation-error fieldName="qty" />

<style>
  .is-invalid { border-color: red; }
  .bootstrap-select.is-invalid .dropdown-toggle {
    border-color: red !important;
    box-shadow: 0 0 0 0.2rem rgba(255,0,0,.25);
  }
</style>

<section class="forms">
  <div class="container-fluid">
    <div class="row"><div class="col-md-12">
      <div class="card">
        <div class="card-header d-flex align-items-center">
          <h4>{{ __('db.Update Quotation') }}</h4>
        </div>
        <div class="card-body">
          <p class="italic"><small>{{__('db.The field labels marked with * are required input fields')}}.</small></p>

          {!! Form::open(['route' => ['quotations.update', $lims_purchase_data->id], 'method' => 'put', 'files' => true, 'id' => 'purchase-form']) !!}

          <div class="row">
            <div class="col-md-12">
              <div class="row">

                <div class="col-md-4">
                  <div class="form-group">
                    <label>{{__('db.date')}}</label>
                    <input type="text" name="created_at" class="form-control date"
                      value="{{ date($general_setting->date_format, strtotime($lims_purchase_data->created_at->toDateString())) }}" />
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label>{{__('db.Reference No')}}</label>
                    <p><strong>{{ $lims_purchase_data->reference_no }}</strong></p>
                  </div>
                  <x-validation-error fieldName="reference_no" />
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label>{{__('db.Biller')}} *</label>
                    <input type="hidden" name="biller_id_hidden" value="{{ $lims_purchase_data->biller_id }}" />
                    <select required name="biller_id" class="selectpicker form-control" data-live-search="true"
                      title="Select Biller...">
                      @foreach($lims_biller_list as $biller)
                        <option value="{{ $biller->id }}" {{ $lims_purchase_data->biller_id == $biller->id ? 'selected' : '' }}>
                          {{ $biller->company_name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="col-md-4" id="select1">
                  <div class="form-group">
                    <label>{{__('Warehouse / Production')}} *</label>
                    <input type="hidden" name="warehouse_id_hidden" value="{{ $lims_purchase_data->warehouse_id }}" />
                    <select required name="warehouse_id" id="warehouse_id" class="selectpicker form-control" data-live-search="true" title="Select warehouse...">
                      @foreach($lims_warehouse_list as $warehouse)
                        <option value="{{ $warehouse->id }}" {{ $lims_purchase_data->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                          {{ $warehouse->company }}
                        </option>
                      @endforeach
                    </select>
                    <x-validation-error fieldName="warehouse_id" />
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label>Customer</label>
                    <input type="hidden" name="customer_id_hidden" value="{{ $lims_purchase_data->customer_id }}" />
                    <select name="customer_id" id="customer_id" class="selectpicker form-control" data-live-search="true" title="Select Customer...">
                      @foreach($lims_customer_list as $customer)
                        <option value="{{ $customer->id }}"
                          @if(isset($lims_purchase_data->customer_id) && $lims_purchase_data->customer_id == $customer->id) selected @endif>
                          {{ $customer->name .' ('. $customer->company_name .')' }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label>{{__('db.Quotation Status')}}</label>
                    <input type="hidden" name="status_hidden" value="{{ $lims_purchase_data->status }}">
                    <select name="status" class="form-control">
                      <option value="3">{{__('New')}}</option>
                      <option value="1">{{__('db.Pending')}}</option>
                      <option value="2">{{__('db.Sent')}}</option>
                      <option value="4">{{__('Accept')}}</option>
                      <option value="5">{{__('In Progress')}}</option>
                      <option value="6">{{__('Cancel')}}</option>
                    </select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label>{{__('db.Attach Document')}}</label>
                    <i class="dripicons-question" data-toggle="tooltip" title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
                    <input type="file" name="document" class="form-control">
                    @if($errors->has('extension'))
                      <span><strong>{{ $errors->first('extension') }}</strong></span>
                    @endif
                    <x-validation-error fieldName="document" />
                  </div>
                </div>

                <div class="col-md-12 mt-3">
                  <label>{{__('db.Select Product')}}</label>
                  <div class="search-box input-group">
                    <button type="button" class="btn btn-secondary"><i class="fa fa-barcode"></i></button>
                    <input type="text" name="product_code_name" id="lims_productcodeSearch" placeholder="{{__('db.Please type product code and select')}}" class="form-control" />
                  </div>
                </div>

              </div>

              <div class="row mt-5">
                <div class="col-md-12">
                  <h5>{{__('db.Order Table')}} *</h5>
                  <div class="table-responsive mt-3">
                    <table id="myTable" class="table table-hover order-list">
                      <thead>
                        <tr>
                          <th>{{__('db.name')}}</th>
                          <th>{{__('db.Code')}}</th>
                          <th>{{__('db.Quantity')}}</th>
                          <th class="recieved-product-qty d-none">{{__('db.Recieved')}}</th>
                          <th>{{__('db.Supplier')}}</th>
                          <th>{{__('db.Net Unit Cost')}}</th>
                          <th>{{__('Shipping')}}</th>
                          <th>{{__('db.Tax')}}</th>
                          <th>{{__('db.Subtotal')}}</th>
                          <th><i class="dripicons-trash"></i></th>
                        </tr>
                      </thead>
                      <tbody>
                        @php $flag2 = 1; @endphp
                        @foreach($lims_product_purchase_data as $product_purchase)
                          @php
                            $product_data = DB::table('products')->find($product_purchase->product_id);
                            if ($product_purchase->variant_id) {
                              $product_variant_data = \App\Models\ProductVariant::FindExactProduct($product_data->id, $product_purchase->variant_id)->select('item_code')->first();
                              if($product_variant_data) $product_data->code = $product_variant_data->item_code;
                            }
                            $tax = DB::table('taxes')->where('rate', $product_purchase->tax_rate)->first();
                            $units = DB::table('units')->where('base_unit', $product_data->unit_id)->orWhere('id', $product_data->unit_id)->get();
                            $unit_name = $unit_operator = $unit_operation_value = [];
                            foreach($units as $unit) {
                              if($product_purchase->sale_unit_id == $unit->id) {
                                array_unshift($unit_name, $unit->unit_name);
                                array_unshift($unit_operator, $unit->operator);
                                array_unshift($unit_operation_value, $unit->operation_value);
                              } else {
                                $unit_name[]  = $unit->unit_name;
                                $unit_operator[] = $unit->operator;
                                $unit_operation_value[] = $unit->operation_value;
                              }
                            }
                            if($product_data->tax_method == 1){
                              $product_cost = ($product_purchase->net_unit_price + ($product_purchase->discount / max(1,$product_purchase->qty))) / max(1,$unit_operation_value[0]);
                            } else {
                              $product_cost = (($product_purchase->total + ($product_purchase->discount / max(1,$product_purchase->qty))) / max(1,$product_purchase->qty)) / max(1,$unit_operation_value[0]);
                            }
                            $temp_unit_name = implode(",",$unit_name) . ',';
                            $temp_unit_operator = implode(",",$unit_operator) . ',';
                            $temp_unit_operation_value = implode(",",$unit_operation_value) . ',';
                          @endphp
                          <tr>
                            <td>{{ $product_data->name }}
                              <button type="button" class="edit-product btn btn-link" data-toggle="modal" data-no="{{ $flag2 }}" data-target="#editModal">
                                <i class="dripicons-document-edit"></i>
                              </button>
                            </td>
                            <td>{{ $product_data->code }}</td>
                            <td><input type="number" class="form-control qty" name="qty[]" value="{{ $product_purchase->qty }}" required step="any" /></td>
                            <td class="recieved-product-qty d-none">
                              <input type="number" class="form-control recieved" name="recieved[]" value="{{ $product_purchase->recieved }}" step="any"/>
                            </td>
                            <td>
                              <select name="supplier_name[]" class="form-control" title="Select Supplier">
                                <option value="">{{ __('Select Supplier') }}</option>
                                @foreach($lims_supplier_list as $supplier)
                                  <option value="{{ $supplier->id }}" @if(isset($product_purchase['supplier_id']) && $product_purchase['supplier_id'] == $supplier->id) selected @endif>
                                    {{ $supplier->company_name . ' (' . $supplier->name . ')' }}
                                  </option>
                                @endforeach
                              </select>
                            </td>
                            <td class="net_unit_cost">{{ number_format((float)$product_purchase->net_unit_price, $general_setting->decimal, '.', '') }}</td>
                            <td class="ship_costt">{{ number_format((float)$product_purchase->ship_cost, $general_setting->decimal, '.', '') }}</td>
                            <td class="tax">{{ number_format((float)$product_purchase->tax, $general_setting->decimal, '.', '') }}</td>
                            <td class="sub-total">{{ number_format((float)$product_purchase->total, $general_setting->decimal, '.', '') }}</td>
                            <td><button type="button" class="ibtnDel btn btn-md btn-danger">{{ __("db.delete") }}</button></td>

                            <input type="hidden" class="product-id" name="product_id[]" value="{{ $product_data->id }}"/>
                            <input type="hidden" class="product-code" name="product_code[]" value="{{ $product_data->code }}"/>
                            <input type="hidden" class="product-cost" name="product_cost[]" value="{{ $product_cost }}"/>

                            <!-- purchase unit -->
                            <input type="hidden" class="purchase-unit" name="sale_unit[]" value="{{ $temp_unit_name }}"/>
                            <input type="hidden" class="purchase-unit-operator" value="{{ $temp_unit_operator }}"/>
                            <input type="hidden" class="purchase-unit-operation-value" value="{{ $temp_unit_operation_value }}"/>

                            <input type="hidden" class="net_unit_cost" name="net_unit_price[]" value="{{ $product_purchase->net_unit_price }}" />
                            <input type="hidden" class="discount-value" name="discount[]" value="{{ $product_purchase->discount }}" />
                            <input type="hidden" class="tax-rate" name="tax_rate[]" value="{{ $product_purchase->tax_rate }}"/>
                            <input type="hidden" class="tax-name" value="{{ $tax ? $tax->name : 'No Tax' }}" />
                            <input type="hidden" class="tax-method" value="{{ $product_data->tax_method }}"/>
                            <input type="hidden" class="tax-value" name="tax[]" value="{{ $product_purchase->tax }}" />
                            <input type="hidden" class="subtotal-value" name="subtotal[]" value="{{ $product_purchase->total }}" />
                            <input type="hidden" class="is-imei" value="{{ $product_data->is_imei }}" />
                            <input type="hidden" class="imei-number" name="imei_number[]" value="{{ $product_purchase->imei_number }}" />
                            <input type="hidden" class="original-cost" value="{{ $product_data->cost }}" />

                            <input type="hidden" class="eta-date{{ $flag2 }}" name="eta_date[]" value="{{ $product_purchase->eta_date ? date('d-m-Y', strtotime($product_purchase->eta_date)) : '' }}" />
                            <input type="hidden" class="ets-date{{ $flag2 }}" name="ets_date[]" value="{{ $product_purchase->ets_date ? date('d-m-Y', strtotime($product_purchase->ets_date)) : '' }}" />
                            <input type="hidden" class="lt-date{{ $flag2 }}" name="lt_date[]" value="{{ $product_purchase->lt_date }}" />
                            <input type="hidden" class="moq{{ $flag2 }}" name="moq[]" value="{{ $product_purchase->moq }}" />
                            <input type="hidden" class="ship_cost{{ $flag2 }}" name="ship_cost[]" value="{{ $product_purchase->ship_cost }}" />
                          </tr>
                          @php $flag2++; @endphp
                        @endforeach
                      </tbody>

                      <tfoot class="tfoot active">
                        <th colspan="2">{{__('db.Total')}}</th>
                        <th id="total-qty">{{ $lims_purchase_data->total_qty }}</th>
                        <th></th><th></th><th></th>
                        <th class="recieved-product-qty d-none"></th>
                        <th id="total-discount">{{ number_format((float)$lims_purchase_data->total_discount, $general_setting->decimal, '.', '') }}</th>
                        <th id="total-tax">{{ number_format((float)$lims_purchase_data->total_tax, $general_setting->decimal, '.', '') }}</th>
                        <th id="total">{{ number_format((float)$lims_purchase_data->total_price, $general_setting->decimal, '.', '') }}</th>
                        <th><i class="dripicons-trash"></i></th>
                      </tfoot>

                    </table>
                  </div>
                </div>
              </div>

              <!-- Hidden totals -->
              <div class="row">
                <div class="col-md-2"><input type="hidden" name="total_qty" value="{{ $lims_purchase_data->total_qty }}" /></div>
                <div class="col-md-2"><input type="hidden" name="total_discount" value="{{ $lims_purchase_data->total_discount }}" /></div>
                <div class="col-md-2"><input type="hidden" name="total_tax" value="{{ $lims_purchase_data->total_tax }}" /></div>
                <div class="col-md-2"><input type="hidden" name="total_cost" value="{{ $lims_purchase_data->total_price }}" /></div>
                <div class="col-md-2">
                  <input type="hidden" name="item" value="{{ $lims_purchase_data->item }}" />
                  <input type="hidden" name="order_tax" value="{{ $lims_purchase_data->order_tax }}"/>
                </div>
                <div class="col-md-2">
                  <input type="hidden" name="grand_total" value="{{ $lims_purchase_data->grand_total }}" />
                  <input type="hidden" name="paid_amount" value="{{ $lims_purchase_data->paid_amount }}" />
                </div>
              </div>

              <div class="row mt-5">
                <div class="col-md-4">
                  <div class="form-group">
                    <label><strong>{{__('db.Order Tax')}}</strong></label>
                    <input type="hidden" name="order_tax_rate_hidden" value="{{ $lims_purchase_data->order_tax_rate }}">
                    <select class="form-control" name="order_tax_rate">
                      <option value="0">{{__('db.No Tax')}}</option>
                      @foreach($lims_tax_list as $tax)
                        <option value="{{ $tax->rate }}">{{ $tax->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label><strong>{{__('db.Discount')}}</strong></label>
                    <input type="number" name="order_discount" class="form-control" value="{{ $lims_purchase_data->order_discount }}" step="any" />
                  </div>
                </div>

                <div class="col-md-4" style="display:none;">
                  <div class="form-group">
                    <label><strong>{{__('db.Shipping Cost')}}</strong></label>
                    <input type="number" name="shipping_cost" class="form-control" value="{{ $lims_purchase_data->shipping_cost }}" step="any" />
                  </div>
                </div>

                <div class="col-md-4">
                  <div class="form-group">
                    <label><strong>{{__('Signature')}} <small>This Is Electronic Signature For Company Use Only</small></strong></label>
                    <input type="text" name="signature" class="form-control" value="{{ $lims_purchase_data->signature }}" />
                  </div>
                </div>
              </div>

              <div class="row"><div class="col-md-12">
                <div class="form-group">
                  <label>{{__('db.Note')}}</label>
                  <textarea rows="5" class="form-control" name="note">{{ $lims_purchase_data->note }}</textarea>
                </div>
              </div></div>

            </div>
          </div>

          <div class="form-group">
            <input type="submit" value="{{__('db.submit')}}" class="btn btn-primary" id="submit-button">
          </div>

          {!! Form::close() !!}
        </div>
      </div>
    </div></div>
  </div>

  <div class="container-fluid">
    <table class="table table-bordered table-condensed totals">
      <td><strong>{{__('db.Items')}}</strong><span class="pull-right" id="item">{{ number_format(0, $general_setting->decimal, '.', '') }}</span></td>
      <td><strong>{{__('db.Total')}}</strong><span class="pull-right" id="subtotal">{{ number_format(0, $general_setting->decimal, '.', '') }}</span></td>
      <td><strong>{{__('db.Order Tax')}}</strong><span class="pull-right" id="order_tax">{{ number_format(0, $general_setting->decimal, '.', '') }}</span></td>
      <td style="display:none;"><strong>{{__('db.Order Discount')}}</strong><span class="pull-right" id="order_discount">{{ number_format(0, $general_setting->decimal, '.', '') }}</span></td>
      <td><strong>{{__('db.Shipping Cost')}}</strong><span class="pull-right" id="shipping_cost">{{ number_format(0, $general_setting->decimal, '.', '') }}</span></td>
      <td><strong>{{__('db.grand total')}}</strong><span class="pull-right" id="grand_total">{{ number_format(0, $general_setting->decimal, '.', '') }}</span></td>
    </table>
  </div>

  {{-- Edit Modal --}}
  <div id="editModal" tabindex="-1" role="dialog" class="modal fade text-left">
    <div role="document" class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 id="modal_header" class="modal-title"></h5>
          <button type="button" data-dismiss="modal" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
        </div>
        <div class="modal-body">
          <form>
            <div class="row modal-element">
              <div class="col-md-4 form-group">
                <label>{{__('db.Quantity')}}</label>
                <input type="number" name="edit_qty" class="form-control" step="any">
              </div>
              <div class="col-md-4 form-group" style="display:none;">
                <label>{{__('db.Unit Discount')}}</label>
                <input type="number" name="edit_discount" class="form-control" step="any">
              </div>
              <div class="col-md-4 form-group">
                <label>{{__('db.Unit Cost')}}</label>
                <input type="number" name="edit_unit_cost" class="form-control" step="any">
              </div>

              @php
                $tax_name_all = ['No Tax']; $tax_rate_all = [0];
                foreach($lims_tax_list as $tax) { $tax_name_all[]=$tax->name; $tax_rate_all[]=$tax->rate; }
              @endphp

              <div class="col-md-4 form-group">
                <label>{{__('db.Tax Rate')}}</label>
                <select name="edit_tax_rate" class="form-control selectpicker">
                  @foreach($tax_name_all as $key => $name)
                    <option value="{{ $key }}">{{ $name }}</option>
                  @endforeach
                </select>
              </div>

              <div class="col-md-4 form-group" id="edit_unit_wrap">
                <label>{{__('db.Product Unit')}}</label>
                <select name="edit_unit" class="form-control selectpicker"></select>
              </div>

              <div class="col-md-4 form-group">
                <label>{{__('ETS')}}</label>
                <input type="text" name="ets_date" class="form-control date" placeholder="{{__('db.Choose date')}}"/>
              </div>
              <div class="col-md-4 form-group">
                <label>{{__('ETA')}}</label>
                <input type="text" name="eta_date" class="form-control date" placeholder="{{__('db.Choose date')}}"/>
              </div>
              <div class="col-md-4 form-group">
                <label>{{__('Lead Time')}}</label>
                <input type="text" name="lt_date" class="form-control" placeholder="{{__('Lead Time')}}"/>
              </div>
              <div class="col-md-4 form-group">
                <label>{{__('MOQ')}}</label>
                <input type="text" name="moq" class="form-control moq" placeholder="{{__('Minimum Order Quantity')}}"/>
              </div>
              <div class="col-md-4 form-group">
                <label>{{__('Shipping Cost')}}</label>
                <input type="text" name="ship_cost" class="form-control ship_cost" placeholder="{{__('Enter Shipping Cost')}}"/>
              </div>
            </div>

            <button type="button" name="update_btn" class="btn btn-primary">{{__('db.update')}}</button>
          </form>
        </div>
      </div>
    </div>
  </div>

</section>

@endsection

@push('scripts')
<script type="text/javascript">
  // ====== MENU HIGHLIGHT ======
  $("ul#purchase").siblings('a').addClass("active");
  $("ul#purchase").addClass("show");

  // ====== GLOBALS ======
  var currency = <?php echo json_encode($currency) ?>;
  var without_stock = <?php echo json_encode($general_setting->without_stock) ?>;

  // arrays (warehouse products)
  var lims_product_array = [];
  var product_code = [];
  var product_name = [];
  var product_qty = [];
  var product_type = [];
  var product_id = [];
  var product_list = [];
  var qty_list = [];
  var product_warehouse_price = []; // optional

  // arrays (row selections)
  var product_cost = [];
  var product_discount = [];
  var tax_rate = [];
  var tax_name = [];
  var tax_method = [];
  var unit_name = [];
  var unit_operator = [];
  var unit_operation_value = [];
  var is_imei = [];

  // temps
  var temp_unit_name = [];
  var temp_unit_operator = [];
  var temp_unit_operation_value = [];

  var rowindex, row_product_cost, pos;

  // ====== INIT SELECTPICKER/TOOLTIPS ======
  $('.selectpicker').selectpicker({ style: 'btn-link' });
  $('[data-toggle="tooltip"]').tooltip();

  // ====== ASSIGN INITIAL SELECTS ======
  $('select[name="warehouse_id"]').val($('input[name="warehouse_id_hidden"]').val());
  $('select[name="status"]').val($('input[name="status_hidden"]').val());
  $('select[name="order_tax_rate"]').val($('input[name="order_tax_rate_hidden"]').val());
  $('select[name="customer_id"]').val($('input[name="customer_id_hidden"]').val());
  $('.selectpicker').selectpicker('refresh');

  // ====== PRELOAD EXISTING ROWS ======
  var rownumber = $('table.order-list tbody tr:last').index();
  for (rowindex=0; rowindex<=rownumber; rowindex++) {
    product_cost.push(parseFloat($('table.order-list tbody tr:eq('+rowindex+') .product-cost').val()||0));
    var total_discount = parseFloat($('table.order-list tbody tr:eq('+rowindex+') .discount').text()||0);
    var quantity = parseFloat($('table.order-list tbody tr:eq('+rowindex+') .qty').val()||1);
    product_discount.push((total_discount / Math.max(1,quantity)).toFixed({{$general_setting->decimal}}));
    tax_rate.push(parseFloat($('table.order-list tbody tr:eq('+rowindex+') .tax-rate').val()||0));
    tax_name.push($('table.order-list tbody tr:eq('+rowindex+') .tax-name').val()||'No Tax');
    tax_method.push($('table.order-list tbody tr:eq('+rowindex+') .tax-method').val()||1);
    temp_unit_name = ($('table.order-list tbody tr:eq('+rowindex+') .purchase-unit').val()||'').split(',');
    unit_name.push($('table.order-list tbody tr:eq('+rowindex+') .purchase-unit').val()||'');
    unit_operator.push($('table.order-list tbody tr:eq('+rowindex+') .purchase-unit-operator').val()||'');
    unit_operation_value.push($('table.order-list tbody tr:eq('+rowindex+') .purchase-unit-operation-value').val()||'');
    is_imei.push($('table.order-list tbody tr:eq('+rowindex+') .is-imei').val()||0);
    $('table.order-list tbody tr:eq('+rowindex+') .purchase-unit').val(temp_unit_name[0]||'');
  }

  // ====== TOP TOTALS INIT ======
  $('#item').text($('input[name="item"]').val() + '(' + $('input[name="total_qty"]').val() + ')');
  $('#subtotal').text(parseFloat($('input[name="total_cost"]').val()||0).toFixed({{$general_setting->decimal}}));
  $('#order_tax').text(parseFloat($('input[name="order_tax"]').val()||0).toFixed({{$general_setting->decimal}}));
  if ($('select[name="status"]').val() == 2) { $(".recieved-product-qty").removeClass("d-none"); }
  if(!$('input[name="order_discount"]').val()) $('input[name="order_discount"]').val('{{ number_format(0, $general_setting->decimal, '.', '') }}');
  $('#order_discount').text(parseFloat($('input[name="order_discount"]').val()||0).toFixed({{$general_setting->decimal}}));
  (function recalcShip(){
    let totalShippingCost = 0;
    $('input[name="ship_cost[]"]').each(function(){ let v=parseFloat($(this).val()); if(!isNaN(v)) totalShippingCost+=v; });
    $('#shipping_cost').text(totalShippingCost.toFixed({{$general_setting->decimal}}));
  })();
  $('#grand_total').text(parseFloat($('input[name="grand_total"]').val()||0).toFixed({{$general_setting->decimal}}));

  // ====== LOAD PRODUCTS FOR SELECTED WAREHOUSE (like old page) ======
  function loadWarehouseProducts(warehouse_id){
    $.get('../getproduct/' + warehouse_id, function(data) {
      lims_product_array = [];
      product_code = data[0] || [];
      product_name = data[1] || [];
      product_qty = data[2] || [];
      product_type = data[3] || [];
      product_id = data[4] || [];
      product_list = data[5] || [];
      qty_list = data[6] || [];
      product_warehouse_price = data[7] || [];
      $.each(product_code, function(index) {
        lims_product_array.push(product_code[index] + ' (' + product_name[index] + ')');
      });
    });
  }

  // on load
  loadWarehouseProducts($('#warehouse_id').val());

  // on change
  $('select[name="warehouse_id"]').on('change', function() {
    var id = $(this).val();
    loadWarehouseProducts(id);
  });

  // ====== AUTOCOMPLETE (ported from old) ======
  var lims_productcodeSearch = $('#lims_productcodeSearch');

  // safety: require warehouse selected
  $('#lims_productcodeSearch').on('input', function(){
    var warehouse_id = $('select[name="warehouse_id"]').val();
    var temp_data = $('#lims_productcodeSearch').val();
    if(!warehouse_id){
      $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
      alert('Please select Warehouse!');
    }
  });

  lims_productcodeSearch.autocomplete({
    source: function(request, response) {
      var matcher = new RegExp(".?" + $.ui.autocomplete.escapeRegex(request.term), "i");
      response($.grep(lims_product_array, function(item) {
        return matcher.test(item);
      }));
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

  // ====== ADD PRODUCT (adapted to purchase fields) ======
  function productSearch(data) {
    $.ajax({
      type: 'GET',
      url: '../lims_product_search',
      data: { data: data },
      success: function (data) {
        var flag = 1;
        // if code exists -> increment qty
        $(".product-code").each(function(i) {
          if ($(this).val() == data[1]) {
            rowindex = i;
            var qty = parseFloat($('table.order-list tbody tr:eq('+rowindex+') .qty').val() || 0) + 1;
            $('table.order-list tbody tr:eq('+rowindex+') .qty').val(qty);
            checkQuantity(String(qty), true);
            flag = 0;
          }
        });
        $("input[name='product_code_name']").val('');
        if(flag) {
          var newRow = $("<tr>");
          var cols = '';
          temp_unit_name = (data[6]||'').split(',');

          // row number for hidden ETS/ETA etc.
          var rowNo = $('table.order-list tbody tr').length + 1;

          cols += '<td>' + data[0] + '<button type="button" class="edit-product btn btn-link" data-no="'+rowNo+'" data-toggle="modal" data-target="#editModal"><i class="dripicons-document-edit"></i></button></td>';
          cols += '<td>' + data[1] + '</td>';
          cols += '<td><input type="number" class="form-control qty" name="qty[]" value="1" step="any" required/></td>';
          cols += '<td class="recieved-product-qty d-none"><input type="number" class="form-control recieved" name="recieved[]" value="1" step="any"/></td>';
          cols += '<td><select name="supplier_name[]" class="form-control" title="Select Supplier"><option value="">{{__('Select Supplier')}}</option>@foreach($lims_supplier_list as $supplier)<option value="{{ $supplier->id }}">{{ $supplier->company_name . " (" . $supplier->name . ")" }}</option>@endforeach</select></td>';
          cols += '<td class="net_unit_cost"></td>';
          cols += '<td class="ship_costt">{{ number_format(0, $general_setting->decimal, ".", "") }}</td>';
          cols += '<td class="tax"></td>';
          cols += '<td class="sub-total"></td>';
          cols += '<td><button type="button" class="ibtnDel btn btn-md btn-danger">{{__("db.delete")}}</button></td>';

          // hiddens
          cols += '<input type="hidden" class="product-code" name="product_code[]" value="' + data[1] + '"/>';
          cols += '<input type="hidden" class="product-id" name="product_id[]" value="' + data[9] + '"/>';
          cols += '<input type="hidden" class="purchase-unit" name="sale_unit[]" value="' + (data[6]||"") + '"/>';
          cols += '<input type="hidden" class="net_unit_cost" name="net_unit_price[]"/>';
          cols += '<input type="hidden" class="discount-value" name="discount[]" value="{{ number_format(0, $general_setting->decimal, ".", "") }}"/>';
          cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + (data[3]||0) + '"/>';
          cols += '<input type="hidden" class="tax-name" value="' + (data[4]||"No Tax") + '"/>';
          cols += '<input type="hidden" class="tax-method" value="' + (data[5]||1) + '"/>';
          cols += '<input type="hidden" class="tax-value" name="tax[]"/>';
          cols += '<input type="hidden" class="subtotal-value" name="subtotal[]"/>';
          cols += '<input type="hidden" class="purchase-unit-operator" value="' + (data[7]||"") + '"/>';
          cols += '<input type="hidden" class="purchase-unit-operation-value" value="' + (data[8]||"") + '"/>';

          // extra fields used in edit modal
          cols += '<input type="hidden" class="is-imei" value="0" />';
          cols += '<input type="hidden" class="imei-number" name="imei_number[]" value="" />';
          cols += '<input type="hidden" class="original-cost" value="' + (data[2]||0) + '" />';

          cols += '<input type="hidden" class="eta-date'+rowNo+'" name="eta_date[]" value="" />';
          cols += '<input type="hidden" class="ets-date'+rowNo+'" name="ets_date[]" value="" />';
          cols += '<input type="hidden" class="lt-date'+rowNo+'" name="lt_date[]" value="" />';
          cols += '<input type="hidden" class="moq'+rowNo+'" name="moq[]" value="" />';
          cols += '<input type="hidden" class="ship_cost'+rowNo+'" name="ship_cost[]" value="{{ number_format(0, $general_setting->decimal, ".", "") }}" />';

          newRow.append(cols);
          $("table.order-list tbody").prepend(newRow);
          rowindex = newRow.index();
          pos = product_code.indexOf(data[1]);

          // base cost (use data[2] or warehouse override if present)
          var baseCost = (product_warehouse_price[pos]) ? parseFloat(product_warehouse_price[pos] * (currency['exchange_rate']||1)) : parseFloat((data[2]||0) * (currency['exchange_rate']||1));
          product_cost.splice(rowindex, 0, baseCost);

          product_discount.splice(rowindex, 0, '{{ number_format(0, $general_setting->decimal, ".", "") }}');
          tax_rate.splice(rowindex, 0, parseFloat(data[3]||0));
          tax_name.splice(rowindex, 0, data[4]||'No Tax');
          tax_method.splice(rowindex, 0, parseInt(data[5]||1));
          unit_name.splice(rowindex, 0, data[6]||'');
          unit_operator.splice(rowindex, 0, data[7]||'');
          unit_operation_value.splice(rowindex, 0, data[8]||'');
          is_imei.splice(rowindex, 0, 0);

          checkQuantity(1, true);
        }
      }
    });
  }

  // ====== EDIT PRODUCT (ported & adapted) ======
  $("table.order-list").on("click", ".edit-product", function() {
    rowindex = $(this).closest('tr').index();

    var row_product_name = $('table.order-list tbody tr:eq('+rowindex+') td:nth-child(1)').clone().children().remove().end().text().trim();
    var row_product_code = $('table.order-list tbody tr:eq('+rowindex+') td:nth-child(2)').text().trim();
    $('#modal_header').text(row_product_name + ' (' + row_product_code + ')');

    var qty = $('table.order-list tbody tr:eq('+rowindex+') .qty').val();
    $('input[name="edit_qty"]').val(qty);
    $('input[name="edit_discount"]').val(parseFloat(product_discount[rowindex]||0).toFixed({{$general_setting->decimal}}));

    // tax select
    var tax_name_all = <?php echo json_encode($tax_name_all) ?>;
    var taxIndex = tax_name_all.indexOf(tax_name[rowindex]) >= 0 ? tax_name_all.indexOf(tax_name[rowindex]) : 0;
    $('select[name="edit_tax_rate"]').val(taxIndex);

    // units
    var row_product_code_text = row_product_code;
    pos = product_code.indexOf(row_product_code_text);
    temp_unit_name = (unit_name[rowindex]||'').split(','); temp_unit_name.pop();
    temp_unit_operator = (unit_operator[rowindex]||'').split(','); temp_unit_operator.pop();
    temp_unit_operation_value = (unit_operation_value[rowindex]||'').split(','); temp_unit_operation_value.pop();

    $('select[name="edit_unit"]').empty();
    $.each(temp_unit_name, function(key, value) {
      $('select[name="edit_unit"]').append('<option value="'+key+'">'+value+'</option>');
    });

    // show unit selector
    $("#edit_unit_wrap").show();

    // compute current row_product_cost with first unit
    unitConversion();
    $('input[name="edit_unit_cost"]').val((row_product_cost||0).toFixed({{$general_setting->decimal}}));

    // Fill modal extra fields from the row hidden inputs
    var btnNo = $(this).data('no');
    $('input[name="eta_date"]').val($('.eta-date'+btnNo).val());
    $('input[name="ets_date"]').val($('.ets-date'+btnNo).val());
    $('input[name="lt_date"]').val($('.lt-date'+btnNo).val());
    $('input[name="moq"]').val($('.moq'+btnNo).val());
    $('input[name="ship_cost"]').val($('.ship_cost'+btnNo).val());

    $('.selectpicker').selectpicker('refresh');
  });

  // update from modal
  $('button[name="update_btn"]').on("click", function() {
    var edit_qty = parseFloat($('input[name="edit_qty"]').val()||0);
    if(edit_qty < 1) {
      $('input[name="edit_qty"]').val(1);
      edit_qty = 1;
      alert("Quantity can't be less than 1");
    }

    var tax_rate_all = <?php echo json_encode($tax_rate_all) ?>;
    var taxIndex = $('select[name="edit_tax_rate"]').val();
    tax_rate[rowindex] = parseFloat(tax_rate_all[taxIndex]||0);
    tax_name[rowindex] = $('select[name="edit_tax_rate"] option:selected').text();

    // unit apply
    var position = $('select[name="edit_unit"]').val();
    var temp_operator = temp_unit_operator[position];
    var temp_operation_value = temp_unit_operation_value[position];

    $('table.order-list tbody tr:eq('+rowindex+') .purchase-unit').val(temp_unit_name[position]);
    temp_unit_name.splice(position, 1);
    temp_unit_operator.splice(position, 1);
    temp_unit_operation_value.splice(position, 1);
    temp_unit_name.unshift($('select[name="edit_unit"] option:selected').text());
    temp_unit_operator.unshift(temp_operator);
    temp_unit_operation_value.unshift(temp_operation_value);
    unit_name[rowindex] = temp_unit_name.toString() + ',';
    unit_operator[rowindex] = temp_unit_operator.toString() + ',';
    unit_operation_value[rowindex] = temp_unit_operation_value.toString() + ',';

    // cost override from modal field (unit cost expressed in selected unit)
    // Convert modal unit cost back to base cost for storage if needed
    var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
    var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(","));
    var enteredUnitCost = parseFloat($('input[name="edit_unit_cost"]').val()||0);
    if (row_unit_operator == '*') {
      product_cost[rowindex] = enteredUnitCost / (parseFloat(row_unit_operation_value)||1);
    } else {
      product_cost[rowindex] = enteredUnitCost * (parseFloat(row_unit_operation_value)||1);
    }

    // set ETA/ETS/etc back to row hidden inputs
    var btnNo = $('table.order-list tbody tr:eq('+rowindex+') .edit-product').data('no');
    $('.eta-date'+btnNo).val($('input[name="eta_date"]').val());
    $('.ets-date'+btnNo).val($('input[name="ets_date"]').val());
    $('.lt-date'+btnNo).val($('input[name="lt_date"]').val());
    $('.moq'+btnNo).val($('input[name="moq"]').val());
    $('.ship_cost'+btnNo).val($('input[name="ship_cost"]').val());

    // update visible ship cell too
    $('table.order-list tbody tr:eq('+rowindex+') .ship_costt').text(parseFloat($('input[name="ship_cost"]').val()||0).toFixed({{$general_setting->decimal}}));

    checkQuantity(edit_qty, false);
  });

  // ====== QUANTITY CHANGE / DELETE ======
  $("#myTable").on('input', '.qty', function() {
    rowindex = $(this).closest('tr').index();
    if (parseFloat($(this).val()||0) < 1) {
      $('table.order-list tbody tr:eq('+rowindex+') .qty').val(1);
      alert("Quantity can't be less than 1");
    }
    checkQuantity($(this).val(), true);
  });

  $("table.order-list tbody").on("click", ".ibtnDel", function() {
    rowindex = $(this).closest('tr').index();
    product_cost.splice(rowindex,1); product_discount.splice(rowindex,1);
    tax_rate.splice(rowindex,1); tax_name.splice(rowindex,1);
    tax_method.splice(rowindex,1); unit_name.splice(rowindex,1);
    unit_operator.splice(rowindex,1); unit_operation_value.splice(rowindex,1);
    is_imei.splice(rowindex,1);
    $(this).closest("tr").remove();
    calculateTotal();
  });

  // ====== CORE CALC FUNCTIONS (ported/adapted) ======
  function unitConversion() {
    var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(",")) || '*';
    var row_unit_operation_value = parseFloat(unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(",")) || 1);
    row_product_cost = (row_unit_operator == '*') ? (product_cost[rowindex] * row_unit_operation_value) : (product_cost[rowindex] / Math.max(1,row_unit_operation_value));
  }

  function checkQuantity(purchase_qty, flag) {
    // purchase me stock-check skip kar rahe (same as your new page)
    if(!flag){ $('#editModal').modal('hide'); }
    $('table.order-list tbody tr:eq('+rowindex+') .qty').val(purchase_qty);
    $('table.order-list tbody tr:eq('+rowindex+') .recieved').val(purchase_qty);
    calculateRowProductData(purchase_qty);
  }

  function calculateRowProductData(quantity) {
    unitConversion();

    $('table.order-list tbody tr:eq('+rowindex+') .discount').text((product_discount[rowindex] * quantity).toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:eq('+rowindex+') .discount-value').val((product_discount[rowindex] * quantity).toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:eq('+rowindex+') .tax-rate').val(tax_rate[rowindex].toFixed({{$general_setting->decimal}}));

    var net_unit_cost, tax, sub_total;
    if (parseInt(tax_method[rowindex]) === 1) {
      net_unit_cost = row_product_cost;
      tax = net_unit_cost * quantity * (tax_rate[rowindex] / 100);
      sub_total = (net_unit_cost * quantity) + tax;
    } else {
      var sub_total_unit = row_product_cost - (product_discount[rowindex]||0);
      net_unit_cost = (100 / (100 + (tax_rate[rowindex]||0))) * sub_total_unit;
      tax = (sub_total_unit - net_unit_cost) * quantity;
      sub_total = sub_total_unit * quantity;
    }

    $('table.order-list tbody tr:eq('+rowindex+') .net_unit_cost').text(net_unit_cost.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:eq('+rowindex+') .net_unit_cost').val(net_unit_cost.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:eq('+rowindex+') .tax').text(tax.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:eq('+rowindex+') .tax-value').val(tax.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:eq('+rowindex+') .sub-total').text(sub_total.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:eq('+rowindex+') .subtotal-value').val(sub_total.toFixed({{$general_setting->decimal}}));

    calculateTotal();
  }

  function calculateTotal() {
    var total_qty = 0;
    $(".qty").each(function(){ total_qty += parseFloat($(this).val()||0); });
    $("#total-qty").text(total_qty);
    $('input[name="total_qty"]').val(total_qty);

    var total_discount = 0; $(".discount").each(function(){ total_discount += parseFloat($(this).text()||0); });
    $("#total-discount").text(total_discount.toFixed({{$general_setting->decimal}}));
    $('input[name="total_discount"]').val(total_discount.toFixed({{$general_setting->decimal}}));

    var total_tax = 0; $(".tax").each(function(){ total_tax += parseFloat($(this).text()||0); });
    $("#total-tax").text(total_tax.toFixed({{$general_setting->decimal}}));
    $('input[name="total_tax"]').val(total_tax.toFixed({{$general_setting->decimal}}));

    var total = 0; $(".sub-total").each(function(){ total += parseFloat($(this).text()||0); });
    $("#total").text(total.toFixed({{$general_setting->decimal}}));
    $('input[name="total_cost"]').val(total.toFixed({{$general_setting->decimal}}));

    calculateGrandTotal();
  }

  function calculateGrandTotal() {
    var itemCount = $('table.order-list tbody tr:last').index() + 1;
    var total_qty = parseFloat($('#total-qty').text()||0);
    var subtotal = parseFloat($('#total').text()||0);
    var order_tax_rate = parseFloat($('select[name="order_tax_rate"]').val()||0);
    var order_discount = parseFloat($('input[name="order_discount"]').val()||0) || 0;

    let totalShippingCost = 0;
    $('input[name="ship_cost[]"]').each(function(){ let v=parseFloat($(this).val()); if(!isNaN(v)) totalShippingCost+=v; });
    // PLUS: add visible ship cells (for newly added rows not yet copied)
    $('table.order-list tbody tr .ship_costt').each(function(){
      let v = parseFloat($(this).text()||0);
      if(!isNaN(v)) totalShippingCost += v;
    });

    var order_tax = (subtotal - order_discount) * (order_tax_rate / 100);
    var grand_total = (subtotal + order_tax + totalShippingCost) - order_discount;

    $('#item').text(itemCount + '(' + total_qty + ')');
    $('input[name="item"]').val(itemCount);
    $('#subtotal').text(subtotal.toFixed({{$general_setting->decimal}}));
    $('#order_tax').text(order_tax.toFixed({{$general_setting->decimal}}));
    $('input[name="order_tax"]').val(order_tax.toFixed({{$general_setting->decimal}}));
    $('#order_discount').text(order_discount.toFixed({{$general_setting->decimal}}));
    $('#shipping_cost').text(totalShippingCost.toFixed({{$general_setting->decimal}}));
    $('#grand_total').text(grand_total.toFixed({{$general_setting->decimal}}));
    $('input[name="grand_total"]').val(grand_total.toFixed({{$general_setting->decimal}}));
  }

  $('input[name="order_discount"]').on("input", calculateGrandTotal);
  $('input[name="shipping_cost"]').on("input", calculateGrandTotal);
  $('select[name="order_tax_rate"]').on("change", calculateGrandTotal);

  // ====== ENTER KEY NAV ======
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

  // ====== SUBMIT GUARD ======
  $('#purchase-form').on('submit', function(e){
    var rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) { alert("Please insert product to order table!"); e.preventDefault(); }
  });
</script>
@endpush
