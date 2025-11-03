    @extends('backend.layout.main') @section('content')
    

    <x-success-message key="message" />
    <x-error-message key="not_permitted" />
    <x-validation-error fieldName="product_code" />
    <x-validation-error fieldName="qty" />
     @if (session('not_permitted'))
  <div class="alert alert-danger">{{ session('not_permitted') }}</div>
@endif

@if ($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach ($errors->all() as $err)
        <li>{{ $err }}</li>
      @endforeach
    </ul>
  </div>
@endif
    <section class="forms">
    <div class="container-fluid">
    <div class="row">
    <div class="col-md-12">
    <div class="card">
    <div class="card-header d-flex align-items-center">
    <h4>{{__('db.Update Purchase')}}</h4>
    </div>
    <div class="card-body">
    <p class="italic"><small>{{__('db.The field labels marked with * are required input fields')}}.</small></p>
    {!! Form::open(['route' => ['purchases.update', $lims_purchase_data->id], 'method' => 'put', 'files' => true, 'id' => 'purchase-form']) !!}
    <div class="row">
    <div class="col-md-12">
    <div class="row">
    <div class="col-md-4">
    <div class="form-group">
    <!-- <label>{{__('db.PO#')}}</label> -->
    <label>System PO#</label>
    <p><strong>{{ $lims_purchase_data->system_po_no }}</strong> </p>
    </div>
    </div>
    <div class="col-md-4">
    <div class="form-group">
    <label>{{__('PO#')}}</label>
    <input type="text" name="po_no" class="form-control po_no" value="{{ $lims_purchase_data->po_no }}" />
    </div>
    </div>

    <div class="col-md-4">
    <div class="form-group">
    <label>{{__('db.date')}}</label>
    <input type="text" name="created_at" class="form-control date" value="{{date($general_setting->date_format, strtotime($lims_purchase_data->created_at->toDateString()))}}" />
    </div>
    </div>
    <div class="col-md-4">
    <div class="form-group">
    <label>{{__('db.Reference No')}}</label>
    <p><strong>{{ $lims_purchase_data->reference_no }}</strong> </p>
    </div>
    <x-validation-error fieldName="reference_no" />
    </div>
   
   <!--  <div class="col-md-4" >
    <div class="form-group">

    <button type="button"  id="btn1" class="btn btn-primary" >Warehouse</button>
   
<button type="button" id="btn2"  class="btn btn-primary">Production</button>

 </div>
</div> -->
    
 <div class="col-md-4" id="select1">
    <div class="form-group">
        <label>{{__('Warehouse / Production')}} *</label>
        <input type="hidden" name="warehouse_id_hidden" value="{{ $lims_purchase_data->warehouse_id }}" />
        <select required name="warehouse_id" class="selectpicker form-control" data-live-search="true" title="Select warehouse...">
            @foreach($lims_warehouse_list as $warehouse)
                <option value="{{ $warehouse->id }}" {{ $lims_purchase_data->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                    {{ $warehouse->company }}
                </option>
            @endforeach
        </select>
        <x-validation-error fieldName="warehouse_id" />
    </div>
</div>

<!-- <div class="col-md-4" id="select2" style="{{ $lims_purchase_data->production_id ? '' : 'display: none' }}">
    <div class="form-group">
        <label>{{__('Production')}} *</label>
        <input type="hidden" name="production_id_hidden" value="{{ $lims_purchase_data->production_id }}" />
        <select required name="production_id" class="selectpicker form-control" data-live-search="true" title="Select production...">
            @foreach($lims_production_list as $production)
                <option value="{{ $production->id }}" {{ $lims_purchase_data->production_id == $production->id ? 'selected' : '' }}>
                    {{ $production->company }}
                </option>
            @endforeach
        </select>
        <x-validation-error fieldName="production_id" />
    </div>
</div> -->

    <div class="col-md-4">
    <div class="form-group">
    <label>Customer</label>
    <input type="hidden" name="customer_id_hidden" value="{{ $lims_purchase_data->user_id }}" />
   <select name="customer_id" name="customer_id" class="selectpicker form-control" data-live-search="true" title="Select Customer...">

@foreach($lims_customer_list as $customer)

<option value="{{$customer->id}}"
     @if(isset($lims_purchase_data->user_id) && $lims_purchase_data->user_id == $customer->id) selected @endif>
     {{$customer->name .' ('. $customer->company_name .')'}}</option>
@endforeach
</select>
    </div>
    </div>
    <div class="col-md-4">
    <div class="form-group">
    <label>{{__('db.Purchase Status')}}</label>
    <input type="hidden" name="status_hidden" value="{{$lims_purchase_data->status}}">
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
    @foreach($custom_fields as $field)
    <?php $field_name = str_replace(' ', '_', strtolower($field->name)); ?>
    @if(!$field->is_admin || \Auth::user()->role_id == 1)
    <div class="{{'col-md-'.$field->grid_value}}">
    <div class="form-group">
    <label>{{$field->name}}</label>
    @if($field->type == 'text')
    <input type="text" name="{{$field_name}}" value="{{$lims_purchase_data->$field_name}}" class="form-control" @if($field->is_required){{'required'}}@endif>
    @elseif($field->type == 'number')
    <input type="number" name="{{$field_name}}" value="{{$lims_purchase_data->$field_name}}" class="form-control" @if($field->is_required){{'required'}}@endif>
    @elseif($field->type == 'textarea')
    <textarea rows="5" name="{{$field_name}}" value="{{$lims_purchase_data->$field_name}}" class="form-control" @if($field->is_required){{'required'}}@endif></textarea>
    @elseif($field->type == 'checkbox')
    <br>
    <?php
    $option_values = explode(",", $field->option_value);
    $field_values =  explode(",", $lims_purchase_data->$field_name);
    ?>
    @foreach($option_values as $value)
    <label>
    <input type="checkbox" name="{{$field_name}}[]" value="{{$value}}" @if(in_array($value, $field_values)) checked @endif @if($field->is_required){{'required'}}@endif> {{$value}}
    </label>
    &nbsp;
    @endforeach
    @elseif($field->type == 'radio_button')
    <br>
    <?php
    $option_values = explode(",", $field->option_value);
    ?>
    @foreach($option_values as $value)
    <label class="radio-inline">
    <input type="radio" name="{{$field_name}}" value="{{$value}}" @if($value == $lims_purchase_data->$field_name){{'checked'}}@endif @if($field->is_required){{'required'}}@endif> {{$value}}
    </label>
    &nbsp;
    @endforeach
    @elseif($field->type == 'select')
    <?php $option_values = explode(",", $field->option_value); ?>
    <select class="form-control" name="{{$field_name}}" @if($field->is_required){{'required'}}@endif>
    @foreach($option_values as $value)
    <option value="{{$value}}" @if($value == $lims_purchase_data->$field_name){{'selected'}}@endif>{{$value}}</option>
    @endforeach
    </select>
    @elseif($field->type == 'multi_select')
    <?php
    $option_values = explode(",", $field->option_value);
    $field_values =  explode(",", $lims_purchase_data->$field_name);
    ?>
    <select class="form-control" name="{{$field_name}}[]" @if($field->is_required){{'required'}}@endif multiple>
    @foreach($option_values as $value)
    <option value="{{$value}}" @if(in_array($value, $field_values)) selected @endif>{{$value}}</option>
    @endforeach
    </select>
    @elseif($field->type == 'date_picker')
    <input type="text" name="{{$field_name}}" value="{{$lims_purchase_data->$field_name}}" class="form-control date" @if($field->is_required){{'required'}}@endif>
    @endif
    </div>
    </div>
    @endif
    @endforeach
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
    <th>{{__('Batch_No')}}</th>
    <th>{{__('Lot_No      ')}} &nbsp;&nbsp;&nbsp;</th>
    <th>{{__('db.Expired Date')}}</th>
    <th>{{__('db.Supplier')}}</th>
    <th>{{__('db.Net Unit Cost')}}</th>
    <th>{{__('Shipping')}}</th>
    <!-- <th>{{__('db.Discount')}}</th> -->
    <th>{{__('db.Tax')}}</th>
    <th>{{__('db.Subtotal')}}</th>
    <th><i class="dripicons-trash"></i></th>
    </tr>
    </thead>
    <tbody>
    <?php
    $temp_unit_name = [];
    $temp_unit_operator = [];
    $temp_unit_operation_value = [];
    $flag2 = 1;
    ?>

    @foreach($lims_product_purchase_data as $product_purchase)
    <tr>
    <?php
$supplier_data = $supplier_data = DB::table('suppliers')
    ->where('id', $product_purchase->supplier_id)
    ->get(['id','name'])
    ->first(); 
    $supplier_data = json_decode(json_encode($supplier_data), true); // now array

$product_data = DB::table('products')->find($product_purchase->product_id);
    if($product_purchase->variant_id) {
    $product_variant_data = \App\Models\ProductVariant::FindExactProduct($product_data->id, $product_purchase->variant_id)->select('item_code')->first();
    if($product_variant_data)
    $product_data->code = $product_variant_data->item_code;
    }

    $tax = DB::table('taxes')->where('rate', $product_purchase->tax_rate)->first();

    $units = DB::table('units')->where('base_unit', $product_data->unit_id)->orWhere('id', $product_data->unit_id)->get();

    $unit_name = array();
    $unit_operator = array();
    $unit_operation_value = array();

    foreach($units as $unit) {
    if($product_purchase->purchase_unit_id == $unit->id) {
    array_unshift($unit_name, $unit->unit_name);
    array_unshift($unit_operator, $unit->operator);
    array_unshift($unit_operation_value, $unit->operation_value);
    }
    else {
    $unit_name[]  = $unit->unit_name;
    $unit_operator[] = $unit->operator;
    $unit_operation_value[] = $unit->operation_value;
    }
    }
    if($product_data->tax_method == 1){
    $product_cost = ($product_purchase->net_unit_cost + ($product_purchase->discount / $product_purchase->qty)) / $unit_operation_value[0];
    }
    else{
    $product_cost = (($product_purchase->total + ($product_purchase->discount / $product_purchase->qty)) / $product_purchase->qty) / $unit_operation_value[0];
    }


    $temp_unit_name = $unit_name = implode(",",$unit_name) . ',';

    $temp_unit_operator = $unit_operator = implode(",",$unit_operator) .',';

    $temp_unit_operation_value = $unit_operation_value =  implode(",",$unit_operation_value) . ',';
   
    $product_batch_data = \App\Models\ProductBatch::select('batch_no', 'expired_date','lot_no')->find($product_purchase->product_batch_id);

    ?>
    <td>{{$product_data->name}} <button type="button" class="edit-product btn btn-link" data-toggle="modal" data-no="{{ $flag2 }}" data-target="#editModal"> <i class="dripicons-document-edit"></i></button> </td>
    <td>{{$product_data->code}}</td>
    <td><input type="number" class="form-control qty" name="qty[]"  step="0.0001"  value="{{$product_purchase->qty}}" required /></td>
    <td class="recieved-product-qty d-none"><input type="number" step="0.0001" class="form-control recieved" name="recieved[]" value="{{$product_purchase->recieved}}" step="any"/></td>
    @if($product_purchase->product_batch_id)
    <td>
    <input type="hidden" name="product_batch_id[]" value="{{$product_purchase->product_batch_id}}">

    <input type="text" class="form-control batch-no" name="batch_no[]" value="{{$product_batch_data->batch_no}}" required/>
    </td>

    <td>

    <input type="text" class="form-control lot-no" name="lot_no[]" value="{{$product_batch_data->lot_no}}" required/>
    </td>
    <td>
    <input type="text" class="form-control expired-date" name="expired_date[]" value="{{$product_batch_data->expired_date}}" required/>
    </td>
    @else
    
    <td>
    <input type="hidden" name="product_batch_id[]">
    <input type="text" class="form-control batch-no" name="batch_no[]" disabled />
    </td>

    <td>
    <input type="text" class="form-control lot-no" name="lot_no[]" disabled />
    </td>

    <td>
    <input type="text" class="form-control expired-date" name="expired_date[]" disabled />
    </td>
    @endif
    <td>
       <select name="supplier_name[]" class="form-control" title="Select Supplier">
    <option value="">Select Supplier</option>

   @foreach($lims_supplier_list as $supplier)
    <option value="{{ $supplier->id }}"
        @if(isset($supplier_data['id']) && $supplier_data['id'] == $supplier->id) selected @endif>
         {{ $supplier->company_name . ' (' . $supplier->name . ')' }}   
    </option>
@endforeach
</select>
    </td>
    <td class="net_unit_cost">{{ number_format((float)$product_purchase->net_unit_cost, $general_setting->decimal, '.', '')}} </td>
    <!-- <td class="discount">{{ number_format((float)$product_purchase->discount, $general_setting->decimal, '.', '')}}</td> -->
    <td class="ship_costt">{{ number_format((float)$product_purchase->ship_cost, $general_setting->decimal, '.', '')}}</td>
    <td class="tax">{{ number_format((float)$product_purchase->tax, $general_setting->decimal, '.', '')}}</td>
    <td class="sub-total">{{ number_format((float)$product_purchase->total, $general_setting->decimal, '.', '')}}</td>
    <td><button type="button" class="ibtnDel btn btn-md btn-danger">{{__("db.delete")}}</button></td>
    <input type="hidden" class="product-id" name="product_id[]" value="{{$product_data->id}}"/>
    <input type="hidden" class="product-code" name="product_code[]" value="{{$product_data->code}}"/>
    <input type="hidden" class="product-cost" name="product_cost[]" value="{{ $product_cost}}"/>
    <input type="hidden" class="purchase-unit" name="purchase_unit[]" value="{{$unit_name}}"/>
    <input type="hidden" class="purchase-unit-operator" value="{{$unit_operator}}"/>
    <input type="hidden" class="purchase-unit-operation-value" value="{{$unit_operation_value}}"/>
    <input type="hidden" class="net_unit_cost" name="net_unit_cost[]" value="{{$product_purchase->net_unit_cost}}" />
    <input type="hidden" class="discount-value" name="discount[]" value="{{$product_purchase->discount}}" />
    <input type="hidden" class="tax-rate" name="tax_rate[]" value="{{$product_purchase->tax_rate}}"/>
    @if($tax)
    <input type="hidden" class="tax-name" value="{{$tax->name}}" />
    @else
    <input type="hidden" class="tax-name" value="No Tax" />
    @endif
    <input type="hidden" class="tax-method" value="{{$product_data->tax_method}}"/>
    <input type="hidden" class="tax-value" name="tax[]" value="{{$product_purchase->tax}}" />
    <input type="hidden" class="subtotal-value" name="subtotal[]" value="{{$product_purchase->total}}" />
    <input type="hidden" class="is-imei" value="{{$product_data->is_imei}}" />
    <input type="hidden" class="imei-number" name="imei_number[]"  value="{{$product_purchase->imei_number}}" />
    <input type="hidden" class="original-cost"  value="{{$product_data->cost}}" />
    <input type="hidden" class="original-cost" value="'+data[2]+'" />
   
    <input type="hidden" class="eta-date{{ $flag2 }}" name="eta_date[]" value="{{ date('d-m-Y', strtotime($product_purchase->eta_date)) }}" />
   
    <input type="hidden" class="ets-date{{ $flag2 }}" name="ets_date[]" value="{{ date('d-m-Y', strtotime($product_purchase->ets_date)) }}" />

    <input type="hidden" class="etd-date{{ $flag2 }}" name="etd_date[]" value="{{ $product_purchase->etd_date }}" />

    <input type="hidden" class="moq{{ $flag2 }}" name="moq[]" value="{{ $product_purchase->moq }}" />

    <input type="hidden" class="ship_cost{{ $flag2 }}" name="ship_cost[]" value="{{ $product_purchase->ship_cost }}" />

    <input type="hidden" class="ship_term{{ $flag2 }}" name="ship_term[]" value="{{ $product_purchase->ship_term }}" />

    </tr>
   @php  
   $flag2++; 

   @endphp
    @endforeach
    </tbody>
    <tfoot class="tfoot active">
    <th colspan="2">{{__('db.Total')}}</th>
    <th id="total-qty">{{$lims_purchase_data->total_qty}}</th>
    <th></th>
    <th></th>
    <th></th>
    <th class="recieved-product-qty d-none"></th>
    <th id="total-discount">{{ number_format((float)$lims_purchase_data->total_discount, $general_setting->decimal, '.', '')}}</th>
    <th id="total-tax">{{ number_format((float)$lims_purchase_data->total_tax, $general_setting->decimal, '.', '')}}</th>
    <th id="total">{{ number_format((float)$lims_purchase_data->total_cost, $general_setting->decimal, '.', '')}}</th>
    <th><i class="dripicons-trash"></i></th>
    </tfoot>
    </table>
    </div>
    </div>
    </div>
    <div class="row">
    <div class="col-md-2">
    <div class="form-group">
    <input type="hidden" name="total_qty" value="{{$lims_purchase_data->total_qty}}" />
    </div>
    </div>
    <div class="col-md-2">
    <div class="form-group">
    <input type="hidden" name="total_discount" value="{{$lims_purchase_data->total_discount}}" />
    </div>
    </div>
    <div class="col-md-2">
    <div class="form-group">
    <input type="hidden" name="total_tax" value="{{$lims_purchase_data->total_tax}}" />
    </div>
    </div>
    <div class="col-md-2">
    <div class="form-group">
    <input type="hidden" name="total_cost" value="{{$lims_purchase_data->total_cost}}" />
    </div>
    </div>
    <div class="col-md-2">
    <div class="form-group">
    <input type="hidden" name="item" value="{{$lims_purchase_data->item}}" />
    <input type="hidden" name="order_tax" value="{{$lims_purchase_data->order_tax}}"/>
    </div>
    </div>
    <div class="col-md-2">
    <div class="form-group">
    <input type="hidden" name="grand_total" value="{{$lims_purchase_data->grand_total}}" />
    <input type="hidden" name="paid_amount" value="{{$lims_purchase_data->paid_amount}}" />
    </div>
    </div>
    </div>
    <div class="row mt-5">
    <div class="col-md-4">
    <div class="form-group">
    <label><strong>{{__('db.Order Tax')}}</strong></label>
    <input type="hidden" name="order_tax_rate_hidden" value="{{$lims_purchase_data->order_tax_rate}}">
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
    <input type="number" name="order_discount" class="form-control" value="{{$lims_purchase_data->order_discount}}" step="any" />
    </div>
    </div>
    <div class="col-md-4"  style="display: none;">
    <div class="form-group">
    <label>
    <strong>{{__('db.Shipping Cost')}}</strong>
    </label>
    <input type="number" name="shipping_cost" class="form-control" value="{{$lims_purchase_data->shipping_cost}}" step="any" />
    </div>
    </div>
    
    <div class="col-md-4">
    <div class="form-group">
    <label>
    <strong>{{__('Signature')}} <small>This Is Electronic Signature For Company Use Only </small></strong>
    </label>
    <input type="text" name="signature" class="form-control" value="{{$lims_purchase_data->signature}}"  />
    </div>
    </div>
    </div>
    
    <div class="row">
    <div class="col-md-12">
    <div class="form-group">
    <label>{{__('Internal Comments')}}</label>
    <textarea rows="5" class="form-control" name="note" >{{ $lims_purchase_data->note }}</textarea>
    </div>
    </div>
     <div class="col-md-12">
    <div class="form-group">
    <label>{{__('Comment / Instructions')}}</label>
    <textarea rows="5" class="form-control" name="comments" >{{ $lims_purchase_data->comments }}</textarea>
    </div>
    </div>
    
    <div class="col-md-12">
    <div class="form-group">
    <label>{{__('Shipping / Instructions')}}</label>
    <textarea rows="4" class="form-control" name="ship_instruction" >{{ $lims_purchase_data->ship_instruction }}</textarea>
    </div>
    </div>


    </div>

    <div class="form-group">
    <input type="submit" value="{{__('db.submit')}}" class="btn btn-primary" id="submit-button">
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

    <div class="container-fluid">
        <h3>Log History</h3> 
  <table class="table table-bordered table-condensed" style="width:100%;">
  <thead>
    <tr>
      <th style="width:140px;">User</th>
      <th>Notes</th>
      <th style="width:170px;">Created at</th>
    </tr>
  </thead>
  <tbody>
    @foreach($product_purchase_log as $val)
      @php
        $userName = $val->user->name ?? ($val->customer->name ?? 'Unknown');
        $roleId   = $val->user->role_id ?? null;
        $notes    = is_array($val->notes) ? $val->notes : json_decode($val->notes, true);

        // helper: title case label
        $pretty = function($k){
            $k = str_replace(['_id','Id'], '', (string)$k);
            return \Illuminate\Support\Str::title(str_replace('_',' ', $k));
        };

        // helper: array list check (php 7/8 compatible)
        $isList = function($arr){
            if (!is_array($arr)) return false;
            return array_keys($arr) === range(0, count($arr) - 1);
        };

        // helper: value render
        $valToStr = function($v){
            if (is_array($v)) return implode(', ', array_map(fn($x)=> is_array($x)? json_encode($x) : $x, $v));
            return $v;
        };
      @endphp
      <tr>
        <td>
          <span class="badge {{ $roleId == 1 ? 'badge-success' : 'badge-warning' }}">{{ $userName }}</span>
        </td>

        <td>
          @if(is_array($notes))
            {{-- HEADER TABLE --}}
            @php $header = $notes['header'] ?? []; @endphp
            @if(!empty($header))
              <div class="mb-2">
                <strong>Header changes</strong>
                <table class="table table-sm table-striped mb-2" style="width:auto; min-width:360px;">
                  <thead>
                    <tr>
                      <th style="width:220px;">Field</th>
                      <th>Value</th>
                    </tr>
                  </thead>
                  <tbody>
                  @if($isList($header))
                    {{-- shape: [{field,label,value}] --}}
                    @foreach($header as $h)
                      @php
                        $label = $h['label'] ?? $pretty($h['field'] ?? '');
                        $valx  = $h['value'] ?? null;
                      @endphp
                      <tr>
                        <td>{{ $label }}</td>
                        <td>{{ $valToStr($valx) }}</td>
                      </tr>
                    @endforeach
                  @else
                    {{-- shape: {"total_discount":"0.00", ...} --}}
                    @foreach($header as $k => $v)
                      <tr>
                        <td>{{ $pretty($k) }}</td>
                        <td>{{ $valToStr($v) }}</td>
                      </tr>
                    @endforeach
                  @endif
                  </tbody>
                </table>
              </div>
            @endif

            {{-- LINES SECTION --}}
            @php $lines = $notes['lines'] ?? []; @endphp
            @if(!empty($lines))
              <div>
                <strong>Line changes</strong>

                {{-- MODIFIED --}}
                @if(!empty($lines['modified']))
                  @foreach($lines['modified'] as $row)
                    @php
                      $prod = $row['product'] ?? ('#'.($row['product_id'] ?? ''));
                      $chg  = $row['changes'] ?? [];
                    @endphp
                    <div class="mt-2">
                      <em>{{ $prod }}</em>
                      <table class="table table-sm table-striped mb-2" style="width:auto; min-width:360px;">
                        <thead>
                          <tr>
                            <th style="width:220px;">Field</th>
                            <th>New value</th>
                          </tr>
                        </thead>
                        <tbody>
                          @if($isList($chg))
                            {{-- shape: [{field,label,value}] --}}
                            @foreach($chg as $c)
                              @php
                                $label = $c['label'] ?? $pretty($c['field'] ?? '');
                                $valx  = $c['value'] ?? null;
                              @endphp
                              <tr>
                                <td>{{ $label }}</td>
                                <td>{{ $valToStr($valx) }}</td>
                              </tr>
                            @endforeach
                          @else
                            {{-- shape: {"batch_no":"9","moq":"$i",...} --}}
                            @foreach($chg as $k => $v)
                              <tr>
                                <td>{{ $pretty($k) }}</td>
                                <td>{{ $valToStr($v) }}</td>
                              </tr>
                            @endforeach
                          @endif
                        </tbody>
                      </table>
                    </div>
                  @endforeach
                @endif

                {{-- ADDED --}}
                @if(!empty($lines['added']))
                  @foreach($lines['added'] as $row)
                    @php
                      $prod = $row['product'] ?? ('#'.($row['product_id'] ?? ''));
                      $vals = $row['values'] ?? [];
                    @endphp
                    <div class="mt-2">
                      <em>{{ $prod }}</em> <span class="text-muted">(Added)</span>
                      <table class="table table-sm table-striped mb-2" style="width:auto; min-width:360px;">
                        <thead>
                          <tr>
                            <th style="width:220px;">Field</th>
                            <th>Value</th>
                          </tr>
                        </thead>
                        <tbody>
                          @if($isList($vals))
                            {{-- shape: [{field,label,value}] --}}
                            @foreach($vals as $c)
                              @php
                                $label = $c['label'] ?? $pretty($c['field'] ?? '');
                                $valx  = $c['value'] ?? null;
                              @endphp
                              <tr>
                                <td>{{ $label }}</td>
                                <td>{{ $valToStr($valx) }}</td>
                              </tr>
                            @endforeach
                          @else
                            {{-- shape: {"qty":1,"batch_no":"9",...} --}}
                            @foreach($vals as $k => $v)
                              <tr>
                                <td>{{ $pretty($k) }}</td>
                                <td>{{ $valToStr($v) }}</td>
                              </tr>
                            @endforeach
                          @endif
                        </tbody>
                      </table>
                    </div>
                  @endforeach
                @endif

                {{-- REMOVED --}}
                @if(!empty($lines['removed']))
                  <div class="mt-2">
                    <em>Removed lines</em>
                    <ul class="mb-2" style="margin-left:18px;">
                      @foreach($lines['removed'] as $row)
                        <li>{{ $row['product'] ?? ('#'.($row['product_id'] ?? '')) }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif
              </div>
            @endif

          @else
            {{-- Fallback: plain string --}}
            {{ $val->notes }}
          @endif
        </td>

        <td>{{ $val->created_at }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

  
    </table>
    </div>

    <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
    <h5 id="modal_header" class="modal-title"></h5>
    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
    </div>
    <div class="modal-body">
    <form>
    <div class="row modal-element">
    <div class="col-md-4 form-group">
    <label>{{__('db.Quantity')}}</label>
    <input type="number" name="edit_qty"   step="0.0001"  class="form-control" step="any">
    </div>
    <div class="col-md-4 form-group" style="display: none;">
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
<label>{{__('Lead Time')}}</label>
<input type="text" name="etd_date" class="form-control " placeholder="{{__('Lead Time')}}"/>
</div>

<div class="col-md-4 form-group">
<label>{{__('MOQ')}}</label>
<input type="text" name="moq" class="form-control moq" placeholder="{{__('Minimum Order Quantity')}}"/>
</div>
<div class="col-md-4 form-group">
<label>{{__('Shipping Cost')}}</label>
<input type="text" name="ship_cost" class="form-control ship_cost" placeholder="{{__('Enter Shipping Cost')}}"/>
</div>
<div class="col-md-4 form-group">
<label>{{__('Shipping Term')}}</label>
<input type="text" name="ship_term" class="form-control ship_term" placeholder="{{__('Pre-Paid OR Post-Paid')}}" required />
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

    $("ul#purchase").siblings('a').addClass("active");
    $("ul#purchase").addClass("show");

    // array data depend on warehouse
    var lims_product_array = [];
    var product_code = [];
    var product_name = [];
    var product_qty = [];

    // array data with selection
    var product_cost = [];
    var product_discount = [];
    var tax_rate = [];
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

    var rownumber = $('table.order-list tbody tr:last').index();
    for(rowindex  =0; rowindex <= rownumber; rowindex++){
    product_cost.push(parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-cost').val()));
    var total_discount = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount').text());
    var quantity = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val());
    product_discount.push((total_discount / quantity).toFixed({{$general_setting->decimal}}));
    tax_rate.push(parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val()));
    tax_name.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-name').val());
    tax_method.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-method').val());
    temp_unit_name = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit').val().split(',');
    unit_name.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit').val());
    unit_operator.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit-operator').val());
    unit_operation_value.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit-operation-value').val());
    is_imei.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.is-imei').val());
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit').val(temp_unit_name[0]);
    }

    $('.selectpicker').selectpicker({
    style: 'btn-link',
    });

    $('[data-toggle="tooltip"]').tooltip();

    //assigning value
    $('select[name="supplier_id"]').val($('input[name="customer_id_hidden"]').val());
    $('select[name="warehouse_id"]').val($('input[name="warehouse_id_hidden"]').val());

    // $('select[name="production_id"]').val($('input[name="production_id_hidden"]').val());

    $('select[name="status"]').val($('input[name="status_hidden"]').val());
    $('select[name="order_tax_rate"]').val($('input[name="order_tax_rate_hidden"]').val());
    $('.selectpicker').selectpicker('refresh');

    $('#item').text($('input[name="item"]').val() + '(' + $('input[name="total_qty"]').val() + ')');
    $('#subtotal').text(parseFloat($('input[name="total_cost"]').val()).toFixed({{$general_setting->decimal}}));
    $('#order_tax').text(parseFloat($('input[name="order_tax"]').val()).toFixed({{$general_setting->decimal}}));
    if($('select[name="status"]').val() == 2){
    $(".recieved-product-qty").removeClass("d-none");

    }
    if(!$('input[name="order_discount"]').val())
    $('input[name="order_discount"]').val('{{number_format(0, $general_setting->decimal, '.', '')}}');
    $('#order_discount').text(parseFloat($('input[name="order_discount"]').val()).toFixed({{$general_setting->decimal}}));
    if(!$('input[name="shipping_cost"]').val())
    $('input[name="shipping_cost"]').val('{{number_format(0, $general_setting->decimal, '.', '')}}');

       let  totalShippingCost = 0;
        $('input[name="ship_cost[]"]').each(function() {
        let val = parseFloat($(this).val());
        if (!isNaN(val)) {
        totalShippingCost += val;
        }
        });

    $('#shipping_cost').text(parseFloat(totalShippingCost).toFixed({{$general_setting->decimal}}));
    // $('#shipping_cost').text(parseFloat($('input[name="shipping_cost"]').val()).toFixed({{$general_setting->decimal}}));
    $('#grand_total').text(parseFloat($('input[name="grand_total"]').val()).toFixed({{$general_setting->decimal}}));

    $('select[name="status"]').on('change', function() {
    if($('select[name="status"]').val() == 2){
    $(".recieved-product-qty").removeClass("d-none");
    $(".qty").each(function() {
    rowindex = $(this).closest('tr').index();
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val($(this).val());
    });

    }
    else if(($('select[name="status"]').val() == 3) || ($('select[name="status"]').val() == 4)){
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


    var lims_product_code = [
    @foreach($lims_product_list_without_variant as $product)
    <?php
    $productArray[] = htmlspecialchars($product->code) . '|' . preg_replace('/[\n\r]/', "<br>", htmlspecialchars($product->name));
    ?>
    @endforeach
    @foreach($lims_product_list_with_variant as $product)
    <?php
    $productArray[] = htmlspecialchars($product->item_code) . '|' . preg_replace('/[\n\r]/', "<br>", htmlspecialchars($product->name));
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
    is_imei.splice(rowindex, 1);
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
        var ship_term =  $(".ship_term"+$(this).attr('data-no')).val()
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
        if (ship_term !=""){ $('input[name="ship_term"]').val(ship_term) }
        if (ship_cost !=""){ $('input[name="ship_cost"]').val(ship_cost) }
         
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
    $("#editModal .modal-element").append(htmlText);
    }
    var row_product_name = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').text();
    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
    $('#modal_header').text(row_product_name + '(' + row_product_code + ')');

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
    });

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
    var ship_term = $('input[name="ship_term"]').val();
    var ship_cost = $('input[name="ship_cost"]').val();
    var thisid =  $(this).attr('id');
        $('.ets-date'+thisid).val(etsdate);
        $('.eta-date'+thisid).val(etadate);
        $('.etd-date'+thisid).val(etddate); 
        $('.ship_term'+thisid).val(ship_term); 
        $('.ship_cost'+thisid).val(ship_cost); 
        $('.moq'+thisid).val(moq); 
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
    console.log(product_cost[rowindex]);
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
    flag2 = 1000;
    function productSearch(data) {
    $.ajax({
    type: 'GET',
    url: '../lims_product_search',
    data: {
    data: data
    },
    success: function(data) {
    var flag = 1;
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
    cols += '<td><input type="number" class="form-control qty" name="qty[]"  step="0.0001"  value="1" required /></td>';
    if($('select[name="status"]').val() == 1)
    cols += '<td class="recieved-product-qty d-none"><input type="number" step="0.0001" class="form-control recieved" name="recieved[]" value="1"  /></td>';
    else if($('select[name="status"]').val() == 2)
    cols += '<td class="recieved-product-qty"><input type="number" step="0.0001" class="form-control recieved" name="recieved[]" value="1" /></td>';
    else
    cols += '<td class="recieved-product-qty d-none"><input type="number" step="0.0001" class="form-control recieved" name="recieved[]" value="0" /></td>';
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
cols += '<input type="hidden" class="ship_term'+flag2+'" name="ship_term[]" />';
cols += '<input type="hidden" class="ship_cost'+flag2+'" name="ship_cost[]" />';



    newRow.append(cols);
    $("table.order-list tbody").prepend(newRow);

    rowindex = newRow.index();
    product_cost.splice(rowindex, 0, parseFloat(data[2]));
    product_discount.splice(rowindex, 0, '{{number_format(0, $general_setting->decimal, '.', '')}}');
    tax_rate.splice(rowindex, 0, parseFloat(data[3]));
    tax_name.splice(rowindex, 0, data[4]);
    tax_method.splice(rowindex, 0, data[5]);
    unit_name.splice(rowindex, 0, data[6]);
    unit_operator.splice(rowindex, 0, data[7]);
    unit_operation_value.splice(rowindex, 0, data[8]);
    is_imei.splice(rowindex, 0, data[11]);
    calculateRowProductData(1);
    if(data[11]) {
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.edit-product').click();
    }
    }
    flag2++;
    }
    });
    }
    function checkQuantity(purchase_qty, flag) {
    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
    var pos = product_code.indexOf(row_product_code);
    var operator = unit_operator[rowindex].split(',');
    var operation_value = unit_operation_value[rowindex].split(',');
    if(operator[0] == '*')
    total_qty = purchase_qty * operation_value[0];
    else if(operator[0] == '/')
    total_qty = purchase_qty / operation_value[0];

    $('#editModal').modal('hide');
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(purchase_qty);
    var status = $('select[name="status"]').val();
    if(status == '1' || status == '2' )
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val(purchase_qty);
    else
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val(0);

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
    var order_discount = parseFloat($('input[name="order_discount"]').val());
        let totalShippingCost = 0;

        $('input[name="ship_cost[]"]').each(function() {
        let val = parseFloat($(this).val());
        if (!isNaN(val)) {
        totalShippingCost += val;
        }
        });
      // alert(totalShippingCost)
    // var shipping_cost = parseFloat($('input[name="shipping_cost"]').val());
    var shipping_cost = parseFloat(totalShippingCost);

    if (!order_discount)
    order_discount = {{number_format(0, $general_setting->decimal, '.', '')}};
    if (!shipping_cost)
    shipping_cost = {{number_format(0, $general_setting->decimal, '.', '')}};

    item = ++item + '(' + total_qty + ')';
    order_tax = (subtotal - order_discount) * (order_tax / 100);
    var grand_total = (subtotal + order_tax + shipping_cost) - order_discount;

    $('#item').text(item);
    $('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
    $('#subtotal').text(subtotal.toFixed({{$general_setting->decimal}}));
    $('#order_tax').text(order_tax.toFixed({{$general_setting->decimal}}));
    $('input[name="order_tax"]').val(order_tax.toFixed({{$general_setting->decimal}}));
    $('#order_discount').text(order_discount.toFixed({{$general_setting->decimal}}));
    // $('#shipping_cost').text(shipping_cost.toFixed({{$general_setting->decimal}}));
    $('#shipping_cost').text(totalShippingCost.toFixed({{$general_setting->decimal}}));
    $('#grand_total').text(grand_total.toFixed({{$general_setting->decimal}}));
    $('input[name="grand_total"]').val(grand_total.toFixed({{$general_setting->decimal}}));
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

    $('#purchase-form').on('submit',function(e){
    var rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) {
    alert("Please insert product to order table!")
    e.preventDefault();
    }

    else if($('select[name="status"]').val() != 1)
    {
    flag = 0;
    $(".qty").each(function() {
    rowindex = $(this).closest('tr').index();
    quantity =  $(this).val();
    recieved = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val();

    if(quantity != recieved){
    flag = 1;
    return false;
    }
    });
    if(!flag){
    alert('Quantity and Recieved value is same! Please Change Purchase Status or Recieved value');
    e.preventDefault();
    }
    else
    $(".batch-no, .expired-date").prop('disabled', false);
    }
    else {
    $("#submit-button").prop('disabled', true);
    $(".batch-no, .expired-date").prop('disabled', false);
    }
    });


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
    @endpush
