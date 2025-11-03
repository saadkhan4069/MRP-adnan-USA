@extends('backend.layout.main') @section('content')

<x-error-message key="not_permitted" />
<x-validation-error fieldName="product_code" />
<x-validation-error fieldName="qty" />
<style>
    .is-invalid  {
    border-color: red;
}

.bootstrap-select.is-invalid .dropdown-toggle {
    border-color: red !important;
    box-shadow: 0 0 0 0.2rem rgba(255, 0, 0, 0.25);
}

.rm-specs-card {
  border: 1px solid #e9ecef;
  border-radius: .75rem;
  padding: 1rem 1rem .5rem;
  background: #fff;
  box-shadow: 0 4px 18px rgba(17, 24, 39, .06);
}
.rm-specs-header {
  display:flex; align-items:center; justify-content:space-between;
  margin-bottom:.75rem;
}
.rm-title { font-weight:700; font-size:1.05rem; }
.rm-subtitle { color:#6c757d; font-size:.875rem; }
.rm-table thead th {
  background:#f8f9fa; border-bottom:1px solid #e9ecef; font-weight:600;
}
.rm-table td, .rm-table th { vertical-align:middle; }
.rm-datalist { display:grid; grid-template-columns: 180px 1fr; grid-row-gap:.35rem; }
.rm-datalist dt { font-weight:600; color:#495057; }
.rm-datalist dd { margin:0; color:#212529; }
.rm-note { white-space:pre-line; border:1px dashed #e9ecef; border-radius:.5rem; padding:.5rem .75rem; background:#fafafa; }


</style>
<section class="forms">
<div class="container-fluid">
<div class="row">
<div class="col-md-12">
<div class="card">
<div class="card-header d-flex align-items-center">
<h4>{{__('db.Add Purchase')}}</h4>
</div>
<div class="card-body">
<p class="italic"><small>{{__('db.The field labels marked with * are required input fields')}}.  & <strong>ETS,ETA,ETD Most Require Feilds Marked  </strong></small><br>
<div id="form-error-message" class="text-danger mt-2"></div>
</p>
{!! Form::open(['route' => 'purchases.store', 'method' => 'post', 'files' => true, 'id' => 'purchase-form']) !!}
<div class="row">
<div class="col-md-12">
<div class="row">
<div class="col-md-4">
<div class="form-group">
<!-- <label>{{__('db.PO#')}}</label> -->
<label>PO#</label>
<input type="text" name="po_no" class="form-control required-field" placeholder="Please Enter PO#"/>
</div>
</div>
<div class="col-md-4">
<div class="form-group">
<label>{{__('db.date')}}</label>
<input type="text" name="created_at" class="form-control date" placeholder="{{__('db.Choose date')}}"/>
</div>
</div>
<div class="col-md-4">
<div class="form-group">
<label>
{{__('db.Reference No')}}
</label>
<input type="text" name="reference_no" class="form-control" />
</div>
<x-validation-error fieldName="reference_no" />
</div>

<!-- <div class="col-md-4" >
<div class="form-group"> -->
<!-- <button type="button"  id="btn1" class="btn btn-primary">Warehouse</button>
<button type="button" id="btn2"  class="btn btn-primary">Production</button> -->
<!-- </div>
</div> -->

<div class="col-md-4" id="select1" >
<div class="form-group">
<label>{{__('Warehouse / Production')}} *</label>
<select name="warehouse_id" class="selectpicker form-control required-field" data-live-search="true" title="Select warehouse..." >
@foreach($lims_warehouse_list as $warehouse)
<option value="{{$warehouse->id}}">{{$warehouse->company}}</option>
@endforeach
</select>
<x-validation-error fieldName="warehouse_id" />
</div>
</div>

<!-- <div class="col-md-4" id="select2" style="display: none;">
<div class="form-group">
<label>{{__('Production')}} *</label>
<select name="production_id" class="selectpicker form-control" data-live-search="true" title="Select Production...">
@foreach($lims_production_list as $production)
<option value="{{$production->id}}">{{$production->company}}</option>
@endforeach
</select>
<x-validation-error fieldName="production_id" />
</div>
</div> -->

<div class="col-md-4">
<div class="form-group">
<label>Customer</label>
<select name="customer_id" name="customer_id" class="selectpicker form-control required-field" data-live-search="true" title="Select Customer...">

@foreach($lims_customer_list as $customer)
<option value="{{$customer->id}}">{{$customer->name .' ('. $customer->company_name .')'}}</option>
@endforeach
</select>
</div>
</div>
<div class="col-md-4">
<div class="form-group">
<label>{{__('db.Purchase Status')}}</label>
<select name="status" class="form-control">
<option value="1">{{__('db.Recieved')}}</option>
<option value="2">{{__('db.Partial')}}</option>
<option value="3">{{__('db.Pending')}}</option>
<option value="4">{{__('db.Ordered')}}</option>
<option value="5">{{__('In Process')}}</option>
<option value="6">{{__('Cancel')}}</option>
<option value="7">{{__('Complete')}}</option>

</select>
</div>
</div>
<div class="col-md-4">
<div class="form-group">
<label>{{__('db.Attach Document')}}</label> <i class="dripicons-question" data-toggle="tooltip" title="Only jpg, jpeg, png, gif, pdf, csv, docx, xlsx and txt file is supported"></i>
<input type="file" name="document" class="form-control" >
@if($errors->has('extension'))
<span>
<strong>{{ $errors->first('extension') }}</strong>
</span>
@endif
<x-validation-error fieldName="document" />
</div>
</div>
<div class="col-md-2">
<div class="form-group">
<label>{{__('db.Currency')}} *</label>
<select name="currency_id" id="currency-id" class="form-control selectpicker" data-toggle="tooltip" title="">
@foreach($currency_list as $currency_data)
<option value="{{$currency_data->id}}" data-rate="{{$currency_data->exchange_rate}}" @if($currency_data->exchange_rate == 1){{'checked'}}@endif>{{$currency_data->code}}</option>
@endforeach
</select>
<x-validation-error fieldName="currency_id" />
</div>
</div>
<div class="col-md-2">
<div class="form-group mb-0">
<label>{{__('db.Exchange Rate')}} *</label>
</div>
<div class="form-group d-flex">
<input class="form-control" type="text" id="exchange_rate" name="exchange_rate" value="{{$currency->exchange_rate}}">
<div class="input-group-append">
<span class="input-group-text" data-toggle="tooltip" title="" data-original-title="currency exchange rate">i</span>
</div>
<x-validation-error fieldName="exchange_rate" />
</div>
</div>
@foreach($custom_fields as $field)
@if(!$field->is_admin || \Auth::user()->role_id == 1)
<div class="{{'col-md-'.$field->grid_value}}">
<div class="form-group">
<label>{{$field->name}}</label>
@if($field->type == 'text')
<input type="text" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$field->default_value}}" class="form-control" @if($field->is_required){{'required'}}@endif>
@elseif($field->type == 'number')
<input type="number" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$field->default_value}}" class="form-control" @if($field->is_required){{'required'}}@endif>
@elseif($field->type == 'textarea')
<textarea rows="5" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$field->default_value}}" class="form-control" @if($field->is_required){{'required'}}@endif></textarea>
@elseif($field->type == 'checkbox')
<br>
<?php $option_values = explode(",", $field->option_value); ?>
@foreach($option_values as $value)
<label>
<input type="checkbox" name="{{str_replace(' ', '_', strtolower($field->name))}}[]" value="{{$value}}" @if($value == $field->default_value){{'checked'}}@endif @if($field->is_required){{'required'}}@endif> {{$value}}
</label>
&nbsp;
@endforeach
@elseif($field->type == 'radio_button')
<br>
<?php $option_values = explode(",", $field->option_value); ?>
@foreach($option_values as $value)
<label class="radio-inline">
<input type="radio" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$value}}" @if($value == $field->default_value){{'checked'}}@endif @if($field->is_required){{'required'}}@endif> {{$value}}
</label>
&nbsp;
@endforeach
@elseif($field->type == 'select')
<?php $option_values = explode(",", $field->option_value); ?>
<select class="form-control" name="{{str_replace(' ', '_', strtolower($field->name))}}" @if($field->is_required){{'required'}}@endif>
@foreach($option_values as $value)
<option value="{{$value}}" @if($value == $field->default_value){{'selected'}}@endif>{{$value}}</option>
@endforeach
</select>
@elseif($field->type == 'multi_select')
<?php $option_values = explode(",", $field->option_value); ?>
<select class="form-control" name="{{str_replace(' ', '_', strtolower($field->name))}}[]" @if($field->is_required){{'required'}}@endif multiple>
@foreach($option_values as $value)
<option value="{{$value}}" @if($value == $field->default_value){{'selected'}}@endif>{{$value}}</option>
@endforeach
</select>
@elseif($field->type == 'date_picker')
<input type="text" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$field->default_value}}" class="form-control date" @if($field->is_required){{'required'}}@endif>
@endif
</div>
</div>
@endif
@endforeach
<div class="col-md-12 mt-3">
<label>{{__('db.Select Product')}}</label>
<div class="search-box input-group">
<button class="btn btn-secondary"><i class="fa fa-barcode"></i></button>
<input type="text" name="product_code_name" id="lims_productcodeSearch" placeholder="{{__('db.Please type product code and select')}}" class="form-control" />

</div>
</div>
</div>
<div class="row mt-4">
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
<th>{{__('db.Batch No')}}</th>
<th>{{__('Lot No')}}</th>
<th>{{__('db.Expired Date')}}</th>
<th>{{__('db.Supplier')}}</th>
<th>{{__('db.Net Unit Cost')}}</th>
<th>{{__('Shipping')}}</th>
<th style="display: none;">{{__('db.Discount')}}</th>
<th>{{__('db.Tax')}}</th>
<th>{{__('db.Subtotal')}}</th>
<th><i class="dripicons-trash"></i></th>
</tr>
</thead>
<tbody>
</tbody>
<tfoot class="tfoot active">
<th colspan="2">{{__('db.Total')}}</th>
<th id="total-qty">0</th>
<th class="recieved-product-qty d-none"></th>
<th></th>
<th></th>
<th></th>
<th style="display: none;" id="total-discount">{{number_format(0, $general_setting->decimal, '.', '')}}</th>
<th id="total-tax">{{number_format(0, $general_setting->decimal, '.', '')}}</th>
<th id="total">{{number_format(0, $general_setting->decimal, '.', '')}}</th>
<th><i class="dripicons-trash"></i></th>
</tfoot>
</table>
</div>
</div>
</div>
<div class="row">
<div class="col-md-2">
<div class="form-group">
<input type="hidden" name="total_qty" />
</div>
</div>
<div class="col-md-2">
<div class="form-group">
<input type="hidden" name="total_discount" />
</div>
</div>
<div class="col-md-2">
<div class="form-group">
<input type="hidden" name="total_tax" />
</div>
</div>
<div class="col-md-2">
<div class="form-group">
<input type="hidden" name="total_cost" />
</div>
</div>
<div class="col-md-2">
<div class="form-group">
<input type="hidden" name="item" />
<input type="hidden" name="order_tax" />
</div>
</div>
<div class="col-md-2">
<div class="form-group">
<input type="hidden" name="grand_total" />
<input type="hidden" name="paid_amount" value="{{number_format(0, $general_setting->decimal, '.', '')}}" />
<input type="hidden" name="payment_status" value="1" />
</div>
</div>
</div>
<div class="row mt-3">
<div class="col-md-4">
<div class="form-group">
<label>{{__('db.Order Tax')}}</label>
<select class="form-control" name="order_tax_rate">
<option value="0">{{__('db.No Tax')}}</option>
@foreach($lims_tax_list as $tax)
<option value="{{$tax->rate}}">{{$tax->name}}</option>
@endforeach
</select>
</div>
</div>
<div class="col-md-4">
<div class="form-group">
<label>
<strong>{{__('db.Discount')}}</strong>
</label>
<input type="number" name="order_discount" step="0.1"  class="form-control"  />
</div>
</div>
<div class="col-md-4" style="display: none;">
<div class="form-group">
<label>
<strong>{{__('db.Shipping Cost')}}</strong>
</label>
<input type="number" name="shipping_cost" step="0.1" style="direction: ltr; text-align: right;" class="form-control"  />
</div>
</div>

<div class="col-md-4">
    <div class="form-group">
    <label>
    <strong>{{__('Signature')}} <small>This Is Electronic Signature For Company Use Only </small></strong>
    </label>
    <input type="text" name="signature" class="form-control" value=""  />
    </div>
    </div>

</div>

<div class="row">
<div class="col-md-12">
<div class="form-group">
<label>{{__('db.Note')}}</label>
<textarea rows="5" class="form-control" name="note"></textarea>
</div>
</div>

<div class="col-md-12">
<div class="form-group">
<label>{{__('Comment / Instrutions')}}</label>
<textarea rows="5" class="form-control" name="comments"></textarea>
</div>
</div>

<div class="col-md-12">
<div class="form-group">
<label>{{__('Shipping / Instrutions')}}</label>
<textarea rows="4" class="form-control" name="ship_instruction"></textarea>
</div>
</div>


</div>
<div class="form-group">
<button type="submit" class="btn btn-primary" id="submit-btn">{{__('db.submit')}}</button>
</div>
</div>
</div>
{!! Form::close() !!}
</div>
</div>
</div>
</div>
</div>
<div class="container-fluid">
<table class="table table-bordered table-condensed totals">
<td><strong>{{__('db.Items')}}</strong>
<span class="pull-right" id="item">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
</td>
<td><strong>{{__('db.Total')}}</strong>
<span class="pull-right" id="subtotal">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
</td>
<td><strong>{{__('db.Order Tax')}}</strong>
<span class="pull-right" id="order_tax">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
</td>
<td style="display: none;"><strong>{{__('db.Order Discount')}}</strong>
<span class="pull-right" id="order_discount">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
</td>
<td><strong>{{__('db.Shipping Cost')}}</strong>
<span class="pull-right" id="shipping_cost">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
</td>
<td><strong>{{__('db.grand total')}}</strong>
<span class="pull-right" id="grand_total">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
</td>
</table>
</div>
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
<label>{{__('db.Quantity')}}</label>
<input type="number" name="edit_qty" class="form-control" step="any">
</div>
<div style="display: none;" class="col-md-4 form-group" >
<label>{{__('db.Unit Discount')}}</label>
<input type="number" name="edit_discount" class="form-control" step="any">
</div>
<div class="col-md-4 form-group">
<label>{{__('db.Unit Cost')}}</label>
<input type="number" name="edit_unit_cost" class="form-control" step="any">
</div>
<?php
$tax_name_all[] = 'No Tax';
$tax_rate_all[] = 0;
foreach($lims_tax_list as $tax) {
$tax_name_all[] = $tax->name;
$tax_rate_all[] = $tax->rate;
}
?>
<div class="col-md-4 form-group">
<label>{{__('db.Tax Rate')}}</label>
<select name="edit_tax_rate" class="form-control selectpicker">
@foreach($tax_name_all as $key => $name)
<option value="{{$key}}">{{$name}}</option>
@endforeach
</select>
</div>
<div class="col-md-4 form-group">
<label>{{__('db.Product Unit')}}</label>
<select name="edit_unit" class="form-control selectpicker">
</select>
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
<label>{{__('ETD')}}</label>
<input type="text" name="etd_date" class="form-control date" placeholder="{{__('db.Choose date')}}"/>
</div>

<div class="col-md-4 form-group">
<label>{{__('MOQ')}}</label>
<input type="text" name="moq" class="form-control moq" placeholder="{{__('Enter Minimum Order Quantity')}}" required />
</div>

<div class="col-md-4 form-group">
<label>{{__('Shipping Cost')}}</label>
<input type="text" name="ship_cost" class="form-control ship_cost" placeholder="{{__('Enter Shipping Cost')}}" required />
</div>
<!--  Raw Material  -->
<hr class="mt-2 mb-3">

<div id="rm-specs-wrapper" class="rm-specs-card d-none">
  <div class="rm-specs-header">
    <div>
      <div class="rm-title">Formula & Specs</div>
      <div class="rm-subtitle" id="rm-product-meta"></div>
    </div>
    <ul class="rm-tabs nav nav-pills">
      <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#tab-formula">Formula</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-specs">Specs & Instructions</a>
      </li>
    </ul>
  </div>

  <div class="tab-content pb-2">
    <!-- Formula Table -->
    <div class="tab-pane fade show active" id="tab-formula">
      <div class="table-responsive">
        <table class="table table-sm rm-table">
          <thead>
            <tr>
              <th style="min-width:240px">Ingredient</th>
              <th>Product Code</th>
              <th class="text-right">% w/w</th>
              <th class="text-right">lbs / 1k gal</th>
              <th class="text-right">gal / 1k gal</th>
              <th class="text-right">Qty</th>
              <th>Unit</th>
              <th class="text-right">Unit Price</th>
              <th class="text-right">Wastage %</th>
            </tr>
          </thead>
          <tbody id="rm-rows"><tr><td colspan="9" class="text-center py-3">Loading…</td></tr></tbody>
        </table>
      </div>
    </div>

    <!-- Specs -->
    <div class="tab-pane fade" id="tab-specs">
      <div class="row">
        <div class="col-md-6">
          <dl class="rm-datalist">
            <dt>Density (lbs/gal)</dt><dd id="sp-density">—</dd>
            <dt>pH</dt><dd id="sp-ph">—</dd>
            <dt>Brix</dt><dd id="sp-brix">—</dd>
            <dt>Yield (gallons)</dt><dd id="sp-yield">—</dd>
            <dt>Formula Date</dt><dd id="sp-formula-date">—</dd>
          </dl>
        </div>
        <div class="col-md-6">
          <dl class="rm-datalist">
            <dt>Taste</dt><dd id="sp-taste">—</dd>
            <dt>Appearance</dt><dd id="sp-appearance">—</dd>
            <dt>Process</dt><dd id="sp-process">—</dd>
          </dl>
        </div>
        <div class="col-12">
          <label class="mb-1 font-weight-bold">Batching Instructions</label>
          <div id="sp-batching" class="rm-note"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--  Raw Material  -->


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

$("ul#purchase").siblings('a').attr('aria-expanded','true');
$("ul#purchase").addClass("show");
$("ul#purchase #purchase-create-menu").addClass("active");

// array data depend on warehouse
var product_code = [];
var product_name = [];
var product_qty = [];

// array data with selection
var product_cost = [];
var product_discount = [];
var tax_rate = [];
var eta_date = [];
var ets_date = [];
var etd_date = [];
var moq = [];
var ship_cost = [];
var tax_name = [];
var tax_method = [];
var unit_name = [];
var unit_operator = [];
var unit_operation_value = [];
var is_imei = [];

// temporary array
var temp_unit_name = [];
var temp_unit_operator = [];
var temp_unit_operation_value = [];

var rowindex;
var customer_group_rate;
var row_product_cost;
var currency = <?php echo json_encode($currency) ?>;
var exchangeRate = 1;
var currencyChange = false;

$('#currency-id').val(currency['id']);
$('.selectpicker').selectpicker({
style: 'btn-link',
});

$('.selectpicker').selectpicker('refresh');

$('#currency-id').change(function(){
var rate = $(this).find(':selected').data('rate');
var currency_id = $(this).val();
$('#exchange_rate').val(rate);
exchangeRate = rate;
currencyChange = true;
$("table.order-list tbody .qty").each(function(index) {
rowindex = index;
checkQuantity($(this).val(), true);
});
});

$('[data-toggle="tooltip"]').tooltip();

$('select[name="status"]').on('change', function() {
if($('select[name="status"]').val() == 2){
$(".recieved-product-qty").removeClass("d-none");
$(".qty").each(function() {
rowindex = $(this).closest('tr').index();
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val($(this).val());
});

}
else if(($('select[name="status"]').val() == 3) || ($('select[name="status"]').val() == 4)) {
$(".recieved-product-qty").addClass("d-none");
$(".recieved").each(function() {
$(this).val(0);
});
}
else {
$(".recieved-product-qty").addClass("d-none");
$(".qty").each(function() {
rowindex = $(this).closest('tr').index();
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val($(this).val());
});
}
});

 <?php $productArray = []; ?>
    var lims_product_code = [
        @foreach($lims_product_list_without_variant as $product)
            <?php
                $productArray[] = htmlspecialchars($product->code) . '|' . preg_replace('/[\n\r]/', "<br>", htmlspecialchars($product->name))." | (".$product->title.")";
            ?>
        @endforeach
        @foreach($lims_product_list_with_variant as $product)
            <?php
                $productArray[] = htmlspecialchars($product->item_code) . '|' . preg_replace('/[\n\r]/', "<br>", htmlspecialchars($product->name))." | (".$product->title.")";
            ?>
        @endforeach

        <?php
            echo  '"'.implode('","', $productArray).'"';
        ?>
    ];
var lims_productcodeSearch = $('#lims_productcodeSearch');

lims_productcodeSearch.autocomplete({
source: function(request, response) {
var matcher = new RegExp(".?" + $.ui.autocomplete.escapeRegex(request.term), "i");
response($.grep(lims_product_code, function(item) {
return matcher.test(item);
}));
},
response: function(event, ui) {
if (ui.content.length == 1) {
var data = ui.content[0].value;
$(this).autocomplete( "close" );
productSearch(data);
};
},
select: function(event, ui) {
var data = ui.item.value;
productSearch(data);
}
});

$('body').on('focus',".expired-date", function() {
$(this).datepicker({
format: "yyyy-mm-dd",
startDate: "<?php echo date("Y-m-d", strtotime('+ 1 days')) ?>",
autoclose: true,
todayHighlight: true
});
});



//Change quantity
$("#myTable").on('input', '.qty', function() {
rowindex = $(this).closest('tr').index();
//console.log($(this).val());
if($(this).val() < 1 && $(this).val() != '') {
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(1);
alert("Quantity can't be less than 1");
}
checkQuantity($(this).val(), true);
});


//Delete product
$("table.order-list tbody").on("click", ".ibtnDel", function(event) {
rowindex = $(this).closest('tr').index();
product_cost.splice(rowindex, 1);
product_discount.splice(rowindex, 1);

tax_rate.splice(rowindex, 1);
tax_name.splice(rowindex, 1);
tax_method.splice(rowindex, 1);
unit_name.splice(rowindex, 1);
unit_operator.splice(rowindex, 1);
unit_operation_value.splice(rowindex, 1);
// console.log(product_cost);
$(this).closest("tr").remove();
calculateTotal();
});

//Edit product
$("table.order-list").on("click", ".edit-product", function() {
    
    $('button[name="update_btn"]').attr("id",$(this).attr('data-no'));
   var etsdate =  $(".ets-date"+$(this).attr('data-no')).val()
   var etadate =  $(".eta-date"+$(this).attr('data-no')).val()
   var etddate =  $(".etd-date"+$(this).attr('data-no')).val()
   var moq =  $(".moq"+$(this).attr('data-no')).val()
   var ship_cost =  $(".ship_cost"+$(this).attr('data-no')).val()
            $('input[name="ets_date"]').val('')
            $('input[name="eta_date"]').val('')
            $('input[name="etd_date"]').val('')
            $('input[name="moq"]').val('')
            $('input[name="ship_cost"]').val('')
       if (etsdate !=""){ $('input[name="ets_date"]').val(etsdate) }
       if (etadate !=""){ $('input[name="eta_date"]').val(etadate) }
       if (etddate !=""){ $('input[name="etd_date"]').val(etddate) }
       if (moq !=""){ $('input[name="moq"]').val(moq) }
       if (ship_cost !=""){ 
         $('input[name="ship_cost"]').val(ship_cost) }
rowindex = $(this).closest('tr').index();
$(".imei-section").remove();
if(is_imei[rowindex]) {
var imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val();
if(!imeiNumbers.length) {
htmlText = `<div class="col-md-8 form-group imei-section">
<label>IMEI or Serial Numbers</label>
<div class="table-responsive ml-2">
<table id="imei-table" class="table table-hover">
<tbody>
<tr>
<td>
<input type="text" class="form-control imei-numbers" name="imei_numbers[]" />
</td>
<td>
<button type="button" class="imei-del btn btn-sm btn-danger">X</button>
</td>
</tr>
</tbody>
</table>
</div>
<button type="button" class="btn btn-info btn-sm ml-2 mb-3" id="imei-add-more"><i class="ion-plus"></i> Add More</button>
</div>`;
}
else {
imeiArrays = imeiNumbers.split(",");
htmlText = `<div class="col-md-8 form-group imei-section">
<label>IMEI or Serial Numbers</label>
<div class="table-responsive ml-2">
<table id="imei-table" class="table table-hover">
<tbody>`;
for (var i = 0; i < imeiArrays.length; i++) {
htmlText += `<tr>
<td>
<input type="text" class="form-control imei-numbers" name="imei_numbers[]" value="`+imeiArrays[i]+`" />
</td>
<td>
<button type="button" class="imei-del btn btn-sm btn-danger">X</button>
</td>
</tr>`;
}
htmlText += `</tbody>
</table>
</div>
<button type="button" class="btn btn-info btn-sm ml-2 mb-3" id="imei-add-more"><i class="ion-plus"></i> Add More</button>
</div>`;
}
// htmlText = '<div class="col-md-12 form-group imei-section"><label>IMEI or Serial Numbers</label><input type="text" name="imei_numbers" value="'+imeiNumbers+'" class="form-control imei_number" placeholder="Type imei or serial numbers and separate them by comma. Example:1001,2001" step="any"></div>';
$("#editModal .modal-element").append(htmlText);
}

var row_product_name = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').text();
var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
$('#modal-header').text(row_product_name + '(' + row_product_code + ')');

var qty = $(this).closest('tr').find('.qty').val();
$('input[name="edit_qty"]').val(qty);

$('input[name="edit_discount"]').val(parseFloat(product_discount[rowindex]).toFixed({{$general_setting->decimal}}));

unitConversion();
$('input[name="edit_unit_cost"]').val(row_product_cost.toFixed({{$general_setting->decimal}}));

var tax_name_all = <?php echo json_encode($tax_name_all) ?>;
var pos = tax_name_all.indexOf(tax_name[rowindex]);
$('select[name="edit_tax_rate"]').val(pos);


temp_unit_name = (unit_name[rowindex]).split(',');
temp_unit_name.pop();
temp_unit_operator = (unit_operator[rowindex]).split(',');
temp_unit_operator.pop();
temp_unit_operation_value = (unit_operation_value[rowindex]).split(',');
temp_unit_operation_value.pop();
$('select[name="edit_unit"]').empty();
$.each(temp_unit_name, function(key, value) {
$('select[name="edit_unit"]').append('<option value="' + key + '">' + value + '</option>');
});
$('.selectpicker').selectpicker('refresh');
var productId = $(this).closest('tr').find('.product-id').val();
   //showRawmterial(productId);
});

function showRawmterial(product_id){
  // UI: open the section and show loading state
  $("#rm-specs-wrapper").removeClass('d-none');
  $("#rm-product-meta").text('Loading…');
  $("#rm-rows").html('<tr><td colspan="9" class="text-center py-3">Loading…</td></tr>');
  $("#sp-density,#sp-ph,#sp-brix,#sp-taste,#sp-appearance,#sp-process,#sp-yield,#sp-formula-date").text('—');
  $("#sp-batching").text('');

  $.ajax({
    type: 'POST',
    url:  'RawMterial',
    data: { product_id: product_id },
    success: function(resp) {
      if (!resp || resp.ok !== true) {
        $("#rm-rows").html('<tr><td colspan="9" class="text-center text-danger py-3">No data found</td></tr>');
        $("#rm-product-meta").text('');
        return;
      }

      // Header (product)
      var meta = [];
      if (resp.product && resp.product.name) meta.push(resp.product.name);
      if (resp.product && resp.product.code) meta.push('[' + resp.product.code + ']');
      $("#rm-product-meta").text(meta.join(' '));

      // Formula rows
      if (!resp.materials || resp.materials.length === 0) {
        $("#rm-rows").html('<tr><td colspan="9" class="text-center py-3">No raw materials mapped</td></tr>');
      } else {
        var rowsHtml = '';
        resp.materials.forEach(function(m){
          rowsHtml += `
            <tr>
              <td>${escapeHtml(m.raw_material_name || '')} <small class="text-muted">${m.raw_material_code ? '['+escapeHtml(m.raw_material_code)+']' : ''}</small></td>
              <td>${m.product_code ? escapeHtml(m.product_code) : ''}</td>
              <td class="text-right">${fmtNum(m.percent_w_w)}</td>
              <td class="text-right">${fmtNum(m.lbs_per_1k_gal)}</td>
              <td class="text-right">${fmtNum(m.gal_per_1k_gal)}</td>
              <td class="text-right">${fmtNum(m.quantity)}</td>
              <td>${escapeHtml(m.unit || '')}</td>
              <td class="text-right">${fmtMoney(m.unit_price)}</td>
              <td class="text-right">${fmtNum(m.wastage_pct)}</td>
            </tr>`;
        });
        $("#rm-rows").html(rowsHtml);
      }

      // Specs
      if (resp.specs) {
        $("#sp-density").text(fmtNum(resp.specs.density_lbs_per_gal));
        $("#sp-ph").text(resp.specs.ph ?? '—');
        $("#sp-brix").text(fmtNum(resp.specs.brix));
        $("#sp-yield").text(fmtNum(resp.specs.yield_gallons));
        $("#sp-formula-date").text(resp.specs.formula_date || '—');
        $("#sp-taste").text(resp.specs.taste || '—');
        $("#sp-appearance").text(resp.specs.appearance || '—');
        $("#sp-process").text(resp.specs.process || '—');
        $("#sp-batching").text(resp.specs.batching_instructions || '');
      }
    },
    error: function() {
      $("#rm-rows").html('<tr><td colspan="9" class="text-center text-danger py-3">Error loading data</td></tr>');
      $("#rm-product-meta").text('');
    }
  });
}

// Helpers
function fmtNum(v){ if(v===null || v===undefined || v==='') return '—'; var n = parseFloat(v); return isNaN(n)? '—' : n.toFixed(3).replace(/\.000$/,''); }
function fmtMoney(v){ if(v===null || v===undefined || v==='') return '—'; var n=parseFloat(v); return isNaN(n)? '—' : n.toFixed(2); }
function escapeHtml(s){ return String(s).replace(/[&<>"']/g, (m)=>({ '&':'&nbsp;&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])); }

//add imei
$(document).on("click", "#imei-add-more", function() {
var newRow = $("<tr>");
var cols = '';
cols += '<td><input type="text" class="form-control imei-numbers" name="imei_numbers[]" /></td>';
cols += '<td><button type="button" class="imei-del btn btn-sm btn-danger">X</button></td>';

newRow.append(cols);
$("table#imei-table tbody").append(newRow);
//increasing qty
var edit_qty = parseFloat($('input[name="edit_qty"]').val());
$('input[name="edit_qty"]').val(edit_qty+1);
});

//Delete imei
$(document).on("click", "table#imei-table tbody .imei-del", function() {
$(this).closest("tr").remove();
//decreaing qty
var edit_qty = parseFloat($('input[name="edit_qty"]').val());
$('input[name="edit_qty"]').val(edit_qty-1);
});

//Update product
$('button[name="update_btn"]').on("click", function() {
    var etsdate = $('input[name="ets_date"]').val();
    var etadate = $('input[name="eta_date"]').val();
    var etddate = $('input[name="etd_date"]').val();
    var moq = $('input[name="moq"]').val();
    var ship_cost = $('input[name="ship_cost"]').val();
    var thisid =  $(this).attr('id');
        $('.ets-date'+thisid).val(etsdate);
        $('.eta-date'+thisid).val(etadate);
        $('.etd-date'+thisid).val(etddate); 
        $('.moq'+thisid).val(moq); 
        $('.ship_cost'+thisid).val(ship_cost); 
    var index = parseInt(thisid);

    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.ship_costt').text(ship_cost);

if(is_imei[rowindex]) {
var imeiNumbers = '';
$("#editModal .imei-numbers").each(function(i) {
if (i)
imeiNumbers += ','+ $(this).val();
else
imeiNumbers = $(this).val();
});
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val(imeiNumbers);
}

var edit_discount = $('input[name="edit_discount"]').val();
var edit_qty = $('input[name="edit_qty"]').val();
var edit_unit_cost = $('input[name="edit_unit_cost"]').val();
if (parseFloat(edit_discount) > parseFloat(edit_unit_cost)) {
alert('Invalid Discount Input!');
return;
}

if(edit_qty < 1) {
$('input[name="edit_qty"]').val(1);
edit_qty = 1;
alert("Quantity can't be less than 1");
}

var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(","));
row_unit_operation_value = parseFloat(row_unit_operation_value);
var tax_rate_all = <?php echo json_encode($tax_rate_all) ?>;

tax_rate[rowindex] = parseFloat(tax_rate_all[$('select[name="edit_tax_rate"]').val()]);
tax_name[rowindex] = $('select[name="edit_tax_rate"] option:selected').text();

if (row_unit_operator == '*') {
product_cost[rowindex] = $('input[name="edit_unit_cost"]').val() / row_unit_operation_value;
} else {
product_cost[rowindex] = $('input[name="edit_unit_cost"]').val() * row_unit_operation_value;
}

product_discount[rowindex] = $('input[name="edit_discount"]').val();

var position = $('select[name="edit_unit"]').val();
var temp_operator = temp_unit_operator[position];
var temp_operation_value = temp_unit_operation_value[position];
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit').val(temp_unit_name[position]);



temp_unit_name.splice(position, 1);
temp_unit_operator.splice(position, 1);
temp_unit_operation_value.splice(position, 1);

temp_unit_name.unshift($('select[name="edit_unit"] option:selected').text());
temp_unit_operator.unshift(temp_operator);
temp_unit_operation_value.unshift(temp_operation_value);

unit_name[rowindex] = temp_unit_name.toString() + ',';
unit_operator[rowindex] = temp_unit_operator.toString() + ',';
unit_operation_value[rowindex] = temp_unit_operation_value.toString() + ',';
checkQuantity(edit_qty, false);
});
flag2 = 1;
function productSearch(data) {
$.ajax({
type: 'GET',
url: 'lims_product_search',
data: {
data: data
},
success: function(data) {
var flag = 1;
// console.log(flag2)
$(".product-code").each(function(i) {
if ($(this).val() == data[1]) {
rowindex = i;
var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) + 1;
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
if($('select[name="status"]').val() == 1 || $('select[name="status"]').val() == 1) {
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .recieved').val(qty);
}
calculateRowProductData(qty);
flag = 0;
}
});
$("input[name='product_code_name']").val('');
if(flag){
var newRow = $("<tr>");
var cols = '';
temp_unit_name = (data[6]).split(',');
cols += '<td>' + data[0] + '<button type="button" class="edit-product btn btn-link" data-toggle="modal" data-no='+flag2+' data-target="#editModal"> <i class="dripicons-document-edit"></i></button></td>';
cols += '<td>' + data[1] + '</td>';
cols += '<td><input type="text" class="form-control qty" name="qty[]" value="1" required/></td>';
if($('select[name="status"]').val() == 1)
cols += '<td class="recieved-product-qty d-none"><input type="text" class="form-control recieved" name="recieved[]" value="1" /></td>';
else if($('select[name="status"]').val() == 2)
cols += '<td class="recieved-product-qty"><input type="text" class="form-control recieved" name="recieved[]" value="1" /></td>';
else
cols += '<td class="recieved-product-qty d-none"><input type="text" class="form-control recieved" name="recieved[]" value="0" /></td>';
if(data[10]) {
cols += '<td><input type="text" class="form-control batch-no" name="batch_no[]" required/></td>';

cols += '<td><input type="text" class="form-control lot-no" name="lot_no[]" required/></td>';

cols += '<td><input type="text" class="form-control expired-date" name="expired_date[]" required/></td>';
}
else {
cols += '<td><input type="text" class="form-control batch-no" name="batch_no[]" disabled/></td>';

cols += '<td><input type="text" class="form-control lot-no" name="lot_no[]" disabled/></td>';

cols += '<td><input type="text" class="form-control expired-date" name="expired_date[]" disabled/></td>';
}

let supplierOptions = `
    <td>
        <select name="supplier_name[]" class="form-control " title="Select Supplier">
            @foreach($lims_supplier_list as $supplier)
                <option value="{{ $supplier->id }}">
                    {{ $supplier->company_name . ' (' . $supplier->name . ')' }}   
                </option>
            @endforeach
        </select>
    </td>
`;

cols += supplierOptions;
cols += '<td class="net_unit_cost"></td>';
cols += '<td style="display:none;" class="discount">{{number_format(0, $general_setting->decimal, '.', '')}}</td>';
cols += '<td style="" class="ship_costt">{{number_format(0, $general_setting->decimal, '.', '')}}</td>';
cols += '<td class="tax"></td>';
cols += '<td class="sub-total"></td>';
cols += '<td><button type="button" class="ibtnDel btn btn-md btn-danger">{{__("db.delete")}}</button></td>';
cols += '<input type="hidden" class="product-code" name="product_code[]" value="' + data[1] + '"/>';
cols += '<input type="hidden" class="product-id" name="product_id[]" value="' + data[9] + '"/>';
cols += '<input type="hidden" class="purchase-unit" name="purchase_unit[]" value="' + temp_unit_name[0] + '"/>';
cols += '<input type="hidden" class="net_unit_cost" name="net_unit_cost[]" />';
cols += '<input type="hidden" class="discount-value" name="discount[]" />';
cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + data[3] + '"/>';
cols += '<input type="hidden" class="tax-value" name="tax[]" />';
cols += '<input type="hidden" class="subtotal-value" name="subtotal[]" />';
cols += '<input type="hidden" class="imei-number" name="imei_number[]" />';
cols += '<input type="hidden" class="original-cost" value="'+data[2]+'" />';
cols += '<input type="hidden" class="eta-date'+flag2+'" name="eta_date[]" />';
cols += '<input type="hidden" class="ets-date'+flag2+'" name="ets_date[]" />';
cols += '<input type="hidden" class="etd-date'+flag2+'" name="etd_date[]" />';
cols += '<input type="hidden" class="moq'+flag2+'" name="moq[]" />';
cols += '<input type="hidden" class="ship_cost'+flag2+'" name="ship_cost[]" />';

newRow.append(cols);
$("table.order-list tbody").prepend(newRow);

rowindex = newRow.index();

product_cost.splice(rowindex,0, parseFloat(data[2]));
product_discount.splice(rowindex,0, '{{number_format(0, $general_setting->decimal, '.', '')}}');

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
}
flag2++;
}
});

}

function checkQuantity(purchase_qty, flag) {
$('#editModal').modal('hide');
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(purchase_qty);
var status = $('select[name="status"]').val();
if(status == '1' || status == '2' )
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val(purchase_qty);
else
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val(0);
if(flag)
product_cost[rowindex] = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.original-cost').val() * exchangeRate;
calculateRowProductData(purchase_qty);
}

function calculateRowProductData(quantity) {
//product_cost[rowindex] = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.original-cost').val() * exchangeRate;
unitConversion();
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount').text((product_discount[rowindex] * quantity).toFixed({{$general_setting->decimal}}));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[rowindex] * quantity).toFixed({{$general_setting->decimal}}));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex].toFixed({{$general_setting->decimal}}));

if (tax_method[rowindex] == 1) {
var net_unit_cost = row_product_cost - product_discount[rowindex];
var tax = net_unit_cost * quantity * (tax_rate[rowindex] / 100);
var sub_total = (net_unit_cost * quantity) + tax;

$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').text(net_unit_cost.toFixed({{$general_setting->decimal}}));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').val(net_unit_cost.toFixed({{$general_setting->decimal}}));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax').text(tax.toFixed({{$general_setting->decimal}}));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed({{$general_setting->decimal}}));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub-total').text(sub_total.toFixed({{$general_setting->decimal}}));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total.toFixed({{$general_setting->decimal}}));
} else {
var sub_total_unit = row_product_cost - product_discount[rowindex];
var net_unit_cost = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
var tax = (sub_total_unit - net_unit_cost) * quantity;
var sub_total = sub_total_unit * quantity;

$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').text(net_unit_cost.toFixed({{$general_setting->decimal}}));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').val(net_unit_cost.toFixed({{$general_setting->decimal}}));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax').text(tax.toFixed({{$general_setting->decimal}}));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed({{$general_setting->decimal}}));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub-total').text(sub_total.toFixed({{$general_setting->decimal}}));
$('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total.toFixed({{$general_setting->decimal}}));
}

calculateTotal();
}

function unitConversion() {
var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(","));
row_unit_operation_value = parseFloat(row_unit_operation_value);
if (row_unit_operator == '*') {
row_product_cost = product_cost[rowindex] * row_unit_operation_value;
} else {
row_product_cost = product_cost[rowindex] / row_unit_operation_value;
}
}

function calculateTotal() {
//Sum of quantity
var total_qty = 0;
$(".qty").each(function() {

if ($(this).val() == '') {
total_qty += 0;
} else {
total_qty += parseFloat($(this).val());
}
});
$("#total-qty").text(total_qty);
$('input[name="total_qty"]').val(total_qty);

//Sum of discount
var total_discount = 0;
$(".discount").each(function() {
total_discount += parseFloat($(this).text());
});
$("#total-discount").text(total_discount.toFixed({{$general_setting->decimal}}));
$('input[name="total_discount"]').val(total_discount.toFixed({{$general_setting->decimal}}));

//Sum of tax
var total_tax = 0;
$(".tax").each(function() {
total_tax += parseFloat($(this).text());
});
$("#total-tax").text(total_tax.toFixed({{$general_setting->decimal}}));
$('input[name="total_tax"]').val(total_tax.toFixed({{$general_setting->decimal}}));

//Sum of subtotal
var total = 0;
$(".sub-total").each(function() {
total += parseFloat($(this).text());
});
$("#total").text(total.toFixed({{$general_setting->decimal}}));
$('input[name="total_cost"]').val(total.toFixed({{$general_setting->decimal}}));

calculateGrandTotal();
}

function calculateGrandTotal() {
var item = $('table.order-list tbody tr:last').index();
var total_qty = parseFloat($('#total-qty').text());
var subtotal = parseFloat($('#total').text());
var order_tax = parseFloat($('select[name="order_tax_rate"]').val());
if($('input[name="order_discount"]').val()) {
if(!currencyChange)
var order_discount = parseFloat($('input[name="order_discount"]').val());
else
var order_discount = parseFloat($('input[name="order_discount"]').val()) * exchangeRate;
}
else
var order_discount = {{number_format(0, $general_setting->decimal, '.', '')}};
        let totalShippingCost = 0;

        $('input[name="ship_cost[]"]').each(function() {
        let val = parseFloat($(this).val());
        if (!isNaN(val)) {
        totalShippingCost += val;
        }
        });

if($('input[name="shipping_cost"]').val()) {
if(!currencyChange)
// var shipping_cost = parseFloat($('input[name="shipping_cost"]').val());
var shipping_cost = parseFloat(totalShippingCost);
else
var shipping_cost = parseFloat(totalShippingCost) * exchangeRate;
// var shipping_cost = parseFloat($('input[name="shipping_cost"]').val()) * exchangeRate;
}
else
var shipping_cost = {{number_format(0, $general_setting->decimal, '.', '')}};

item = ++item + '(' + total_qty + ')';
order_tax = (subtotal - order_discount) * (order_tax / 100);
var grand_total = (subtotal + order_tax + shipping_cost) - order_discount;

$('#item').text(item);
$('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
$('#subtotal').text(subtotal.toFixed({{$general_setting->decimal}}));
$('#order_tax').text(order_tax.toFixed({{$general_setting->decimal}}));
$('input[name="order_tax"]').val(order_tax.toFixed({{$general_setting->decimal}}));
$('#order_discount').text(order_discount.toFixed({{$general_setting->decimal}}));
$('input[name="order_discount"]').val(order_discount);
$('#shipping_cost').text(shipping_cost.toFixed({{$general_setting->decimal}}));
$('input[name="shipping_cost"]').val(shipping_cost);
$('#grand_total').text(grand_total.toFixed({{$general_setting->decimal}}));
$('input[name="grand_total"]').val(grand_total.toFixed({{$general_setting->decimal}}));
currencyChange = false;
}

$('input[name="order_discount"]').on("input", function() {
calculateGrandTotal();
});

$('input[name="shipping_cost"]').on("input", function() {
calculateGrandTotal();
});

$('select[name="order_tax_rate"]').on("change", function() {
calculateGrandTotal();
});

$(window).keydown(function(e){
if (e.which == 13) {
var $targ = $(e.target);
if (!$targ.is("textarea") && !$targ.is(":button,:submit")) {
var focusNext = false;
$(this).find(":input:visible:not([disabled],[readonly]), a").each(function(){
if (this === e.target) {
focusNext = true;
}
else if (focusNext){
$(this).focus();
return false;
}
});
return false;
}
}
});

// $('#purchase-form').on('submit',function(e){
// var rownumber = $('table.order-list tbody tr:last').index();
// if (rownumber < 0) {
// alert("Please insert product to order table!")
// e.preventDefault();
// }

// else if($('select[name="status"]').val() != 1)
// {
// flag = 0;
// $(".qty").each(function() {
// rowindex = $(this).closest('tr').index();
// quantity =  $(this).val();
// recieved = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val();

// if(quantity != recieved){
// flag = 1;
// return false;
// }
// });
// if(!flag){
// alert('Quantity and Recieved value is same! Please Change Purchase Status or Recieved value');
// e.preventDefault();
// }
// else 
// {
// $(".batch-no, .expired-date").prop('disabled', false);

// }
// }
// else {


//     let isValid = true;
//         let firstInvalid = null;
//         $('#form-error-message').text('');

//         $('.required-field').each(function () {
//             const field = $(this);
//             const type = field.attr('type');
//             let value = '';

//             // Handle file input separately
//             if (type === 'file') {
//                 value = field.val(); // for file inputs
//             } else {
//                 value = $.trim(field.val());
//             }

//             if (!value) {
//                 isValid = false;
//                 field.addClass('is-invalid');
//                 if (!firstInvalid) {
//                     firstInvalid = field;
//                 }
//             } else {
//                 field.removeClass('is-invalid');
//             }
//         });

//         if (!isValid) {
//             e.preventDefault();
//             $('#form-error-message').text('Please fill all required fields!');
//             if (firstInvalid) {
//                 firstInvalid.focus();
//             }
//         }
//     // Remove red border on change or input
//     $('.required-field').on('input change', function () {
//         if ($(this).val().trim()) {
//             $(this).removeClass('is-invalid');

//         }
//     });

// $(".batch-no, .expired-date").prop('disabled', false);
// $("#submit-btn").prop('disabled', true);
// }
// });


// ----------------------------------------------------------
$('#purchase-form').on('submit', function (e) {
    let isValid = true;
    let errorMessage = "";

    const po_no = $.trim($('input[name="po_no"]').val());
    const warehouse_id = $.trim($('select[name="warehouse_id"]').val());
    const customer_id = $.trim($('select[name="customer_id"]').val());

    if (!po_no) {
        errorMessage += "- PO Number is required.\n";
        isValid = false;
    }

    if (!warehouse_id) {
        errorMessage += "- Warehouse is required.\n";
        isValid = false;
    }

    if (!customer_id) {
        errorMessage += "- Customer is required.\n";
        isValid = false;
    }

    var rownumber = $('table.order-list tbody tr:last').index();

    if (rownumber < 0) {
        errorMessage += "- Please insert product to order table!\n";
        isValid = false;
    }

    else if ($('select[name="status"]').val() != 1) {
        let flag = 0;
        $(".qty").each(function () {
            let rowindex = $(this).closest('tr').index();
            let quantity = $(this).val();
            let recieved = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val();

            if (quantity != recieved) {
                flag = 1;
                return false;
            }
        });

        if (!flag) {
            errorMessage += "- Quantity and Received value is same! Please change Purchase Status or Received value.\n";
            isValid = false;
        } else {
            $(".batch-no, .expired-date").prop('disabled', false);
        }
    }

    if (!isValid) {
        alert(errorMessage);
        e.preventDefault();
        return;
    }

    // Final step if everything is valid
    $(".batch-no, .expired-date").prop('disabled', false);
    $("#submit-btn").prop('disabled', true);
});


// ----------------------------------------------------------

 // $(document).ready(function () {
 //    $('#btn1').click(function () {
 //      $('#select1').show();
 //      $('#select2').hide();
 //    });

 //    $('#btn2').click(function () {
 //      $('#select2').show();
 //      $('#select1').hide();
 //    });
 //  });



</script>

<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
@endpush
