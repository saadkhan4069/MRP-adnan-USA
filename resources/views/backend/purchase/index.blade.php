@extends('backend.layout.main') 
@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<style>
/* ==========================
   Purchase Order Modal Design
========================== */

/* Modal box content */
#purchase-details .modal-content {
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    background-color: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Modal title */
#purchase-details .modal-title {
    font-size: 24px;
    font-weight: 600;
    text-transform: uppercase;
    color: #333;
    margin-top: 10px;
}

/* Modal logo */
#purchase-details img {
    position: absolute;
    top: 20px;
    right: 30px;
    width: 120px;
    height: auto;
}

/* Buttons */
#purchase-details .btn-default {
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    color: #333;
    padding: 5px 12px;
    border-radius: 4px;
    transition: all 0.3s ease;
}
#purchase-details .btn-default:hover {
    background-color: #e2e6ea;
}

/* Close button style (X icon) */
#purchase-details .close {
    float: right;
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
    opacity: 0.7;
}
#purchase-details .close:hover {
    color: red;
    opacity: 1;
}

/* Table design */
.product-purchase-list_design {
    border-collapse: separate;
    font-size: 10px;
    left: 9px;
    display: block;
    position: relative;
}
.product-purchase-list_design th, 
.product-purchase-list_design td {
    border: 1px solid #dee2e6;
    padding: 8px;
    text-align: center;
    vertical-align: middle;
}
.product-purchase-list_design thead {
    background-color: #343a40;
    color: #ffffff;
    text-transform: uppercase;
    font-size: 11px;
}
.product-purchase-list_design tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}
.product-purchase-list_design tbody tr:hover {
    background-color: #f1f1f1;
}

/* Footer space */
#purchase-footer {
    padding: 15px 10px;
    border-top: 1px solid #dee2e6;
}

/* Responsive tweaks */
@media (max-width: 768px) {
    #purchase-details img {
        position: static;
        display: block;
        margin: 10px auto;
    }
    #purchase-details .modal-title {
        font-size: 20px;
    }
}

/* ==========================
   Print Styling
========================== */
@media print {
    body * { visibility: hidden; }
    #purchase-details, #purchase-details * { visibility: visible; }
    #purchase-details {
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        background-color: #fff;
        padding: 20px;
        box-shadow: none;
    }
    .d-print-none { display: none !important; }
    .modal-content { border: none !important; }
    .product-purchase-list_design th, 
    .product-purchase-list_design td {
        border: 1px solid #000 !important;
    }
}
.header-box {
    border-bottom:  2px solid #b4a9a9;
    padding-bottom: 5px;
    margin-bottom: 10px;
    font-weight: bold;
    font-size: 16px;
}
.footer {
    margin-top: 50px;
    text-align: center;
    font-size: 10px;
    color: #888;
    clear: both;
}
</style>

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{__('db.Purchase List')}}</h3>
            </div>
            {!! Form::open(['route' => 'purchases.index', 'method' => 'get']) !!}
            <div class="row ml-1 mt-2">
                <div class="col-md-3">
                    <div class="form-group">
                        <label><strong>{{__('db.date')}}</strong></label>
                        <input type="text" class="daterangepicker-field form-control" value="{{$starting_date}} To {{$ending_date}}" required />
                        <input type="hidden" name="starting_date" value="{{$starting_date}}" />
                        <input type="hidden" name="ending_date" value="{{$ending_date}}" />
                    </div>
                </div>
                <div class="col-md-3 @if(\Auth::user()->role_id > 2){{'d-none'}}@endif">
                    <div class="form-group">
                        <label><strong>{{__('db.Warehouse')}}</strong></label>
                        <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" >
                            <option value="0">{{__('db.All Warehouse')}}</option>
                            @foreach($lims_warehouse_list as $warehouse)
                                <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><strong>{{__('db.Purchase Status')}}</strong></label>
                        <select id="purchase-status" class="form-control" name="purchase_status">
                            <option value="0">{{__('db.All')}}</option>
                            <option value="1">{{__('db.Recieved')}}</option>
                            <option value="2">{{__('db.Partial')}}</option>
                            <option value="3">{{__('db.Pending')}}</option>
                            <option value="4">{{__('db.Ordered')}}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><strong>{{__('db.Payment Status')}}</strong></label>
                        <select id="payment-status" class="form-control" name="payment_status">
                            <option value="0">{{__('db.All')}}</option>
                            <option value="1">{{__('db.Due')}}</option>
                            <option value="2">{{__('db.Paid')}}</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 mt-3">
                    <div class="form-group">
                        <button class="btn btn-primary" id="filter-btn" type="submit">{{__('db.submit')}}</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
        @if(in_array("purchases-add", $all_permission))
            <a href="{{route('purchases.create')}}" class="btn btn-info"><i class="dripicons-plus"></i> {{__('db.Add Purchase')}}</a>&nbsp;
            <a href="{{url('purchases/purchase_by_csv')}}" class="btn btn-primary"><i class="dripicons-copy"></i> {{__('db.Import Purchase')}}</a>
        @endif
    </div>
    <div class="table-responsive">
        <table id="purchase-table" class="table purchase-list" style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{__('db.date')}}</th>
                    <th>{{__('PO #')}}</th>
                    <th>{{__('db.reference')}}</th>
                    <th>{{__('db.customer')}}</th>
                    <th>{{__('db.Purchase Status')}}</th>
                    <th>{{__('db.grand total')}}</th>
                    <th>{{__('db.Returned Amount')}}</th>
                    <th>{{__('db.Paid')}}</th>
                    <th>{{__('db.Due')}}</th>
                    <th>{{__('db.Payment Status')}}</th>
                    @foreach($custom_fields as $fieldName)
                    <th>{{$fieldName}}</th>
                    @endforeach
                    <th class="not-exported">{{__('db.action')}}</th>
                </tr>
            </thead>

            <tfoot class="tfoot active">
                <th></th>
                <th>{{__('db.Total')}}</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                @foreach($custom_fields as $fieldName)
                <th></th>
                @endforeach
                <th></th>
            </tfoot>
        </table>
    </div>
</section>

<div id="purchase-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
      <div class="modal-content">
        <div class="container mt-3 pb-2 border-bottom">
            <div class="row">
                <div class="col-md-6 d-print-none">
                    <button id="print-btn" type="button" class="btn btn-default btn-sm"><i class="dripicons-print"></i> {{__('db.Print')}}</button>
                </div>
                <div class="col-md-6 d-print-none">
                    <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="col-md-12" style="height: 64px;">
                    <h3 id="exampleModalLabel" class="modal-title text-center container-fluid" >Purchase Order</h3>
                    <img class="logo-image" src="{{ asset('images/brandvolt.jpg') }}" style="display: inline-block;position: relative;width: 22%;top: -72px;left: -37px;float: right;">
                </div>
                <div class="col-md-12 text-center header-box"></div>
            </div>
            <div id="purchase-content" class="modal-body" style="margin:4px"></div>
            <div class="row">
                <div class="col-md-12">
                    <div class="float-left">
                        <table class="table table-bordered product-purchase-list product-purchase-list_design">
                            <thead>
                                <th>#</th>
                                <th>{{__('db.product')}}</th>
                                <th>{{__('db.Supplier')}}</th>
                                <th>{{__('db.Batch No')}}</th>
                                <th>{{__('Lot No')}}</th>
                                <th>Qty</th>
                                <th>MOQ</th>
                                <!-- <th>{{__('db.Returned')}}</th> -->
                                <th>{{__('Shipping term')}}</th>
                                <th>{{__('db.Unit Cost')}}</th>
                                <th>{{__('db.Tax')}}</th>
                                <th>{{__('Ship Cost')}}</th>
                                <th>{{__('db.Subtotal')}}</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>               
                </div>               
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="float-left">
                        <table class="table table-bordered product-purchase-list2 product-purchase-list_design" style="left: 8px">
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="purchase-footer" class="modal-body"></div>
         </div>
      </div>
    </div>
</div>

<div id="view-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{__('db.All Payment')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                <table class="table table-hover payment-list">
                    <thead>
                        <tr>
                            <th>{{__('db.date')}}</th>
                            <th>{{__('db.Reference No')}}</th>
                            <th>{{__('db.Account')}}</th>
                            <th>{{__('db.Amount')}}</th>
                            <th>{{__('db.Paid By')}}</th>
                            <th>{{__('db.action')}}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="add-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{__('db.Add Payment')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => 'purchase.add-payment', 'method' => 'post', 'class' => 'payment-form' ]) !!}
                    <div class="row">
                        <input type="hidden" name="balance">
                        <div class="col-md-6">
                            <label>{{__('db.Recieved Amount')}} *</label>
                            <input type="text" name="paying_amount" class="form-control numkey" step="any" required>
                        </div>
                        <div class="col-md-6">
                            <label>{{__('db.Paying Amount')}} *</label>
                            <input type="text" id="amount" name="amount" class="form-control" step="any" required>
                        </div>
                        <div class="col-md-6 mt-1">
                            <label>{{__('db.Change')}} : </label>
                            <p class="change ml-2">{{number_format(0, $general_setting->decimal, '.', '')}}</p>
                        </div>
                        <div class="col-md-6 mt-1">
                            <label>{{__('db.Paid By')}}</label>
                            <select name="paid_by_id" class="form-control">
                                <option value="1">{{ __('db.Cash') }}</option>
                                <option value="3">{{ __('db.Credit Card') }}</option>
                                <option value="4">{{ __('db.Cheque') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <div class="card-element" class="form-control"></div>
                        <div class="card-errors" role="alert"></div>
                    </div>
                    <div id="cheque">
                        <div class="form-group">
                            <label>{{__('db.Cheque Number')}} *</label>
                            <input type="text" name="cheque_no" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Account')}}</label>
                        <select class="form-control selectpicker" name="account_id">
                        @foreach($lims_account_list as $account)
                            @if($account->is_default)
                            <option selected value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                            @else
                            <option value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                            @endif
                        @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Payment Note')}}</label>
                        <textarea rows="3" class="form-control" name="payment_note"></textarea>
                    </div>
                    <input type="hidden" name="purchase_id">
                    <button type="submit" class="btn btn-primary">{{__('db.submit')}}</button>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

<div id="edit-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
    <div role="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{__('db.Update Payment')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => 'purchase.update-payment', 'method' => 'post', 'class' => 'payment-form' ]) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <label>{{__('db.Recieved Amount')}} *</label>
                            <input type="text" name="edit_paying_amount" class="form-control numkey" step="any" required>
                        </div>
                        <div class="col-md-6">
                            <label>{{__('db.Paying Amount')}} *</label>
                            <input type="text" name="edit_amount" class="form-control" step="any" required>
                        </div>
                        <div class="col-md-6 mt-1">
                            <label>{{__('db.Change')}} : </label>
                            <p class="change ml-2">{{number_format(0, $general_setting->decimal, '.', '')}}</p>
                        </div>
                        <div class="col-md-6 mt-1">
                            <label>{{__('db.Paid By')}}</label>
                            <select name="edit_paid_by_id" class="form-control selectpicker">
                                <option value="1">{{ __('db.Cash') }}</option>
                                <option value="3">{{ __('db.Credit Card') }}</option>
                                <option value="4">{{ __('db.Cheque') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group mt-2">
                        <div class="card-element" class="form-control"></div>
                        <div class="card-errors" role="alert"></div>
                    </div>
                    <div id="edit-cheque">
                        <div class="form-group">
                            <label>{{__('db.Cheque Number')}} *</label>
                            <input type="text" name="edit_cheque_no" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Account')}}</label>
                        <select class="form-control selectpicker" name="account_id">
                        @foreach($lims_account_list as $account)
                            <option value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{__('db.Payment Note')}}</label>
                        <textarea rows="3" class="form-control" name="edit_payment_note"></textarea>
                    </div>
                    <input type="hidden" name="payment_id">
                    <button type="submit" class="btn btn-primary">{{__('db.update')}}</button>
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">
/* =========================================================
   DECIMAL / ROUNDING (HALF-UP) — ALWAYS 2 DECIMALS
========================================================= */
const DEC = 2; // as requested: exactly 2 digits after decimal

function toNum(v) {
  if (v === null || v === undefined) return 0;
  const n = parseFloat(String(v).replace(/,/g, '').trim());
  return Number.isFinite(n) ? n : 0;
}

/* Safe HALF-UP rounding via string arithmetic (0.155 -> 0.16) */
function roundHalfUp(value, decimals = DEC) {
  let s = String(value).replace(/,/g, '').trim();
  if (s === '') return 0;
  let neg = false;
  if (s[0] === '-') { neg = true; s = s.slice(1); }

  if (!/^\d*\.?\d*(e[+-]?\d+)?$/i.test(s)) {
    const n = parseFloat(s);
    if (!isFinite(n)) return 0;
    s = String(n);
  }

  let [intPart, fracPart = ''] = s.split('.');
  while (fracPart.length < decimals + 1) fracPart += '0';

  const keep = fracPart.slice(0, decimals);
  const deciding = +(fracPart[decimals] || '0');

  let combined = (decimals > 0) ? (intPart + keep) : intPart;

  if (deciding >= 5) {
    let carry = 1, arr = combined.split('').reverse();
    for (let i = 0; i < arr.length; i++) {
      let d = (arr[i].charCodeAt(0) - 48) + carry;
      if (d >= 10) { arr[i] = String(d - 10); carry = 1; }
      else { arr[i] = String(d); carry = 0; break; }
    }
    if (carry) arr.push('1');
    combined = arr.reverse().join('');
  }

  let out;
  if (decimals > 0) {
    if (combined.length <= decimals) combined = combined.padStart(decimals + 1, '0');
    const idx = combined.length - decimals;
    out = combined.slice(0, idx) + '.' + combined.slice(idx);
  } else out = combined;

  return (neg ? '-' : '') + out;
}

/* format with thousands + exact decimals */
function formatFixed(value, decimals = DEC) {
  const r = String(roundHalfUp(value, decimals));
  const [intRaw, fracRaw = ''] = r.split('.');
  const intFmt = intRaw.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  const frac = decimals > 0 ? (fracRaw + '0'.repeat(decimals)).slice(0, decimals) : '';
  return decimals > 0 ? `${intFmt}.${frac}` : intFmt;
}

/* aliases used through the code */
const roundN = (n, p = DEC) => toNum(roundHalfUp(n, p)); // numeric rounded
const fmt    = (n, p = DEC) => formatFixed(n, p);        // string formatted

/* =========================================================
   SweetAlert2 Helpers
========================================================= */
const Toast = Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:3500, timerProgressBar:true });
window.alert = (message)=> Toast.fire({ icon:'info', title:String(message) });
const toastSuccess=(msg)=>Toast.fire({icon:'success',title:msg});
const toastError  =(msg)=>Toast.fire({icon:'error',  title:msg});
const toastWarn   =(msg)=>Toast.fire({icon:'warning',title:msg});
function sweetConfirm({title='Are you sure?', text='', confirmText='Yes', cancelText='Cancel', icon='warning'}={}) {
  return Swal.fire({ title, text, icon, showCancelButton:true, confirmButtonText:confirmText, cancelButtonText:cancelText, reverseButtons:true, focusCancel:true })
         .then(r => r.isConfirmed);
}

/* =========================================================
   UI Init
========================================================= */
$(".daterangepicker-field").daterangepicker({
  callback: function(startDate, endDate){
    var starting_date = startDate.format('YYYY-MM-DD');
    var ending_date   = endDate.format('YYYY-MM-DD');
    $(this).val(starting_date + ' To ' + ending_date);
    $('input[name="starting_date"]').val(starting_date);
    $('input[name="ending_date"]').val(ending_date);
  }
});
$("ul#purchase").siblings('a').attr('aria-expanded','true');
$("ul#purchase").addClass("show");
$("ul#purchase #purchase-list-menu").addClass("active");

@if($lims_pos_setting_data)
  var public_key = <?php echo json_encode($lims_pos_setting_data->stripe_public_key) ?>;
@endif

var all_permission  = <?php echo json_encode($all_permission) ?>;
var starting_date   = <?php echo json_encode($starting_date); ?>;
var ending_date     = <?php echo json_encode($ending_date); ?>;
var warehouse_id    = <?php echo json_encode($warehouse_id); ?>;
var purchase_status = <?php echo json_encode($purchase_status); ?>;
var payment_status  = <?php echo json_encode($payment_status); ?>;

/* =========================================================
   Purchase Amount Calculator (uses HALF-UP rounding)
========================================================= */
function computeRowAmounts(purchaseArr){
  const baseTotal     = toNum(purchaseArr?.[13]); // items total
  const orderTaxAmt   = toNum(purchaseArr?.[16]);
  const orderDiscAmt  = toNum(purchaseArr?.[18]);
  const shippingAmt   = toNum(purchaseArr?.[19]);
  const grandServer   = toNum(purchaseArr?.[20]);

  const grandCalc = roundN(baseTotal - orderDiscAmt + orderTaxAmt + shippingAmt);
  const grand     = grandServer > 0 ? grandServer : grandCalc;

  return { baseTotal, orderTaxAmt, orderDiscAmt, shippingAmt, grand };
}

/* =========================================================
   DataTable setup
========================================================= */
var columns = [
  {"data": "key"}, {"data": "date"}, {"data": "po_no"}, {"data": "reference_no"},
  {"data": "supplier"}, {"data": "purchase_status"}, {"data": "grand_total"},
  {"data": "returned_amount"}, {"data": "paid_amount"}, {"data": "due"},
  {"data": "payment_status"}
];
var field_name = <?php echo json_encode($field_name) ?>;
for(let i=0;i<field_name.length;i++) columns.push({"data": field_name[i]});
columns.push({"data": "options"});

$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
$("#warehouse_id").val(warehouse_id);
$("#purchase-status").val(purchase_status);
$("#payment-status").val(payment_status);
$('.selectpicker').selectpicker('refresh');

let dt = $('#purchase-table').DataTable({
  processing:true, serverSide:true,
  ajax:{ url:"purchases/purchase-data", data:{ all_permission, starting_date, ending_date, warehouse_id, purchase_status, payment_status }, dataType:"json", type:"post" },
  createdRow:function(row, data){ $(row).addClass('purchase-link'); $(row).attr('data-purchase', data['purchase']); },
  columns:columns,
  language:{
    lengthMenu: '_MENU_ {{__("db.records per page")}}',
    info: '<small>{{__("db.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
    search: '{{__("db.Search")}}',
    paginate: { previous:'<i class="dripicons-chevron-left"></i>', next:'<i class="dripicons-chevron-right"></i>' }
  },
  order:[['1','desc']],
  columnDefs:[
    { orderable:false, targets:[0,3,4,7,8,9,10] },
    { render:function(data,type){ if(type==='display'){ data='<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'; } return data; },
      checkboxes:{ selectRow:true, selectAllRender:'<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>' }, targets:[0] }
  ],
  select:{ style:'multi', selector:'td:first-child' },
  lengthMenu:[[10,25,50,-1],[10,25,50,"All"]],
  dom:'<"row"lfB>rtip',
  buttons:[
    { extend:'pdf', text:'<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
      exportOptions:{ columns:':visible:not(.not-exported)', rows:':visible' },
      action:function(e,dt,button,config){ datatable_sum_calc(dt,true); $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this,e,dt,button,config); datatable_sum_calc(dt,false); },
      footer:true
    },
    { text:'<i title="Export (Calc) to Excel" class="dripicons-document-new"></i>', action:function(){ exportCalcToXlsx(); } },
    { extend:'csv', text:'<i title="export (calc) to csv" class="fa fa-file-text-o"></i>',
      exportOptions:{ columns:':visible:not(.not-exported)', rows:':visible' },
      action:function(e,dt,button,config){ datatable_sum_calc(dt,true); $.fn.dataTable.ext.buttons.csvHtml5.action.call(this,e,dt,button,config); datatable_sum_calc(dt,false); },
      footer:true
    },
    { extend:'print', text:'<i title="print" class="fa fa-print"></i>',
      exportOptions:{ columns:':visible:not(.not-exported)', rows:':visible' },
      action:function(e,dt,button,config){ datatable_sum_calc(dt,true); $.fn.dataTable.ext.buttons.print.action.call(this,e,dt,button,config); datatable_sum_calc(dt,false); },
      footer:true
    },
    { text:'<i title="delete" class="dripicons-cross"></i>', className:'buttons-delete',
      action: async function(){
        if(<?php echo json_encode(env('USER_VERIFIED')) ?>=='1'){
          let purchase_id=[]; $(':checkbox:checked').each(function(i){ if(i){ var p=$(this).closest('tr').data('purchase'); if(p) purchase_id[i-1]=p[3]; } });
          if(!purchase_id.length){ toastWarn('Nothing is selected!'); return; }
          const ok = await sweetConfirm({ title:'Delete selected purchases?', text:'This action cannot be undone.', confirmText:'Delete', icon:'warning' });
          if(!ok) return;
          $.post('purchases/deletebyselection',{ purchaseIdArray: purchase_id })
            .done(function(msg){ toastSuccess(msg); dt.rows({ page:'current', selected:true }).remove().draw(false); })
            .fail(function(){ toastError('Failed to delete. Please try again.'); });
        } else toastWarn('This feature is disabled for demo!');
      }
    },
    { extend:'colvis', text:'<i title="column visibility" class="fa fa-eye"></i>', columns:':gt(0)' }
  ],
  drawCallback:function(){ datatable_sum_calc(this.api(), false); }
});

/* =========================================================
   Footer Sums (HALF-UP)
========================================================= */
function datatable_sum_calc(api, is_export){
  let totalGrand=0, totalReturned=0, totalPaid=0, totalDue=0;
  let rowsIdx = api.rows({ page: is_export ? 'all' : 'current', search:'applied' }).indexes();
  rowsIdx.each(function(idx){
    const row = api.row(idx).data();
    const pArr = row?.purchase || $(api.row(idx).node()).data('purchase') || [];
    const c = computeRowAmounts(pArr);
    totalGrand   += toNum(c.grand);
    totalReturned+= toNum(row.returned_amount);
    totalPaid    += toNum(row.paid_amount);
    totalDue     += toNum(row.due);
  });
  $(api.column(5).footer()).html('{{__("db.Total")}}');
  $(api.column(6).footer()).html(fmt(totalGrand));
  $(api.column(7).footer()).html(fmt(totalReturned));
  $(api.column(8).footer()).html(fmt(totalPaid));
  $(api.column(9).footer()).html(fmt(totalDue));
}

/* =========================================================
   Export (Calc) → XLSX (client)
========================================================= */
function exportCalcToXlsx(){
  const makeXlsx = async ()=>{
    if(!window.XLSX){
      await new Promise((res,rej)=>{ const s=document.createElement('script'); s.src='https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js'; s.onload=res; s.onerror=rej; document.head.appendChild(s); });
    }
    const rows=[];
    rows.push(['Date','PO #','Reference','Customer','Status','Base Total','Order Tax','Order Discount','Shipping','Grand Total (Calc)','Returned','Paid','Due','Payment Status']);
    dt.rows({ search:'applied', page:'all' }).every(function(){
      const d=this.data(); const pArr=d?.purchase || $(this.node()).data('purchase') || []; const c=computeRowAmounts(pArr);
      rows.push([ d.date, d.po_no, d.reference_no, d.supplier, $(this.node()).find('td:eq(5)').text().trim(),
        fmt(c.baseTotal), fmt(c.orderTaxAmt), fmt(c.orderDiscAmt), fmt(c.shippingAmt), fmt(c.grand),
        fmt(toNum(d.returned_amount)), fmt(toNum(d.paid_amount)), fmt(toNum(d.due)), d.payment_status ]);
    });
    let totals={base:0,tax:0,disc:0,ship:0,grand:0,ret:0,paid:0,due:0};
    for(let r=1;r<rows.length;r++){ totals.base+=toNum(rows[r][5]); totals.tax+=toNum(rows[r][6]); totals.disc+=toNum(rows[r][7]); totals.ship+=toNum(rows[r][8]); totals.grand+=toNum(rows[r][9]); totals.ret+=toNum(rows[r][10]); totals.paid+=toNum(rows[r][11]); totals.due+=toNum(rows[r][12]); }
    rows.push(['','','','','TOTALS', fmt(totals.base), fmt(totals.tax), fmt(totals.disc), fmt(totals.ship), fmt(totals.grand), fmt(totals.ret), fmt(totals.paid), fmt(totals.due), '']);
    const ws=XLSX.utils.aoa_to_sheet(rows); const wb=XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb,ws,'Purchases'); XLSX.writeFile(wb,'purchases_calc.xlsx'); toastSuccess('Export (Calc) complete');
  };
  makeXlsx().catch(()=> toastError('Export failed. Please try again.'));
}

/* =========================================================
   Row Click → Details modal
========================================================= */
$(document).on("click","tr.purchase-link td:not(:first-child, :last-child)",function(){
  var purchase=$(this).parent().data('purchase'); purchaseDetails(purchase);
});
$(document).on("click",".view",function(){
  var purchase=$(this).closest('tr').closest('table').find('tr.purchase-link').data('purchase'); purchaseDetails(purchase);
});

/* =========================================================
   Print modal content
========================================================= */
$("#print-btn").on("click", function () {
  var divContents = document.getElementById("purchase-details").innerHTML;
  var a = window.open('', '_blank');
  a.document.write('<html><head><title>Print</title>');
  a.document.write(`<style>
    body{font-family:sans-serif;line-height:1.15}
    .d-print-none{display:none}
    .text-center{text-align:center}
    .logo-image{top:-14px !important}
    table{width:100%;margin-top:30px;border-collapse:collapse}
    .product-purchase-list_design th,.product-purchase-list_design td{border:1px solid #dee2e6;padding:8px;text-align:center;vertical-align:middle}
    .product-purchase-list_design thead{background:#343a40;color:#fff;text-transform:uppercase;font-size:11px}
    .product-purchase-list_design tbody tr:nth-child(even){background:#f8f9fa}
  </style>`);
  a.document.write('</head><body>'+divContents+'</body></html>');
  a.document.close(); a.onload=function(){ a.focus(); a.print(); };
});

/* =========================================================
   Payments (add/view/edit) — formatting fixed
========================================================= */
let payment_date, payment_reference, paid_amount, paying_method, payment_id, payment_note, cheque_no, change, paying_amount, account_name, account_id;

$(document).on("click","table.purchase-list tbody .add-payment",function(){
  $("#cheque").hide(); $(".card-element").hide(); $('select[name="paid_by_id"]').val(1);
  var id=$(this).data('id').toString();
  var balance=$(this).closest('tr').find('td:nth-child(9)').text();
  balance=toNum(balance);
  $('input[name="amount"]').val(fmt(balance));
  $('input[name="balance"]').val(fmt(balance));
  $('input[name="paying_amount"]').val(fmt(balance));
  $('input[name="purchase_id"]').val(id);
});

$(document).on("click","table.purchase-list tbody .get-payment",function(){
  var id=$(this).data('id').toString();
  $.get('purchases/getpayment/'+id,function(data){
    $(".payment-list tbody").remove(); var newBody=$("<tbody>");
    payment_date=data[0]; payment_reference=data[1]; paid_amount=data[2]; paying_method=data[3]; payment_id=data[4];
    payment_note=data[5]; cheque_no=data[6]; change=data[7]; paying_amount=data[8]; account_name=data[9]; account_id=data[10];

    $.each(payment_date,function(index){
      var newRow=$("<tr>"); var cols='';
      cols+='<td>'+payment_date[index]+'</td>';
      cols+='<td>'+payment_reference[index]+'</td>';
      cols+='<td>'+account_name[index]+'</td>';
      cols+='<td>'+fmt(paid_amount[index])+'</td>';
      cols+='<td>'+paying_method[index]+'</td>';
      cols+='<td><div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Action<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">';
      if(all_permission.indexOf("purchase-payment-edit")!=-1)
        cols+='<li><button type="button" class="btn btn-link edit-btn" data-id="'+payment_id[index]+'" data-clicked=false data-toggle="modal" data-target="#edit-payment"><i class="dripicons-document-edit"></i> Edit</button></li><li class="divider"></li>';
      if(all_permission.indexOf("purchase-payment-delete")!=-1)
        cols+=`{!! Form::open(['route' => 'purchase.delete-payment', 'method' => 'post', 'class' => 'delete-payment-form'] ) !!}<li><input type="hidden" name="id" value="`+payment_id[index]+`" /> <button type="submit" class="btn btn-link"><i class="dripicons-trash"></i> Delete</button></li>{!! Form::close() !!}`;
      cols+='</ul></div></td>';
      newRow.append(cols); newBody.append(newRow); $("table.payment-list").append(newBody);
    });
    $('#view-payment').modal('show');
  });
});

$(document).on("click","table.payment-list .edit-btn",function(){
  $(".edit-btn").attr('data-clicked', true); $(".card-element").hide(); $("#edit-cheque").hide();
  $('#edit-payment select[name="edit_paid_by_id"]').prop('disabled', false);
  var id=$(this).data('id').toString();
  $.each(payment_id,function(index){
    if(payment_id[index]==parseFloat(id)){
      $('input[name="payment_id"]').val(payment_id[index]);
      $('#edit-payment select[name="account_id"]').val(account_id[index]);
      if(paying_method[index]=='Cash') $('select[name="edit_paid_by_id"]').val(1);
      else if(paying_method[index]=='Credit Card'){
        $('select[name="edit_paid_by_id"]').val(3);
        @if($lims_pos_setting_data && (strlen($lims_pos_setting_data->stripe_public_key)>0) && (strlen($lims_pos_setting_data->stripe_secret_key )>0))
          $.getScript("vendor/stripe/checkout.js");
          $(".card-element").show();
        @endif
        $("#edit-cheque").hide();
        $('#edit-payment select[name="edit_paid_by_id"]').prop('disabled', true);
      } else {
        $('select[name="edit_paid_by_id"]').val(4);
        $("#edit-cheque").show();
        $('input[name="edit_cheque_no"]').val(cheque_no[index]).attr('required', true);
      }
      $('input[name="edit_date"]').val(payment_date[index]);
      $("#payment_reference").html(payment_reference[index]);
      $('input[name="edit_amount"]').val(fmt(paid_amount[index]));
      $('input[name="edit_paying_amount"]').val(fmt(paying_amount[index]));
      $('.change').text(fmt(change[index]));
      $('textarea[name="edit_payment_note"]').val(payment_note[index]);
      return false;
    }
  });
  $('.selectpicker').selectpicker('refresh');
  $('#view-payment').modal('hide');
});

/* paid-by change + change calc (use formatFixed) */
$('select[name="paid_by_id"]').on("change", function() {
  var id=$('select[name="paid_by_id"]').val();
  $('input[name="cheque_no"]').attr('required', false);
  $(".payment-form").off("submit");
  if (id == 3) { $.getScript("vendor/stripe/checkout.js"); $(".card-element").show(); $("#cheque").hide(); }
  else if (id == 4) { $("#cheque").show(); $(".card-element").hide(); $('input[name="cheque_no"]').attr('required', true); }
  else { $(".card-element").hide(); $("#cheque").hide(); }
});
$('input[name="paying_amount"]').on("input", function() {
  $(".change").text(formatFixed(toNum($(this).val()) - toNum($('input[name="amount"]').val())));
});
$('input[name="amount"]').on("input", function() {
  if( toNum($(this).val()) > toNum($('input[name="paying_amount"]').val()) ) { toastWarn('Paying amount cannot be bigger than received amount'); $(this).val(''); }
  else if( toNum($(this).val()) > toNum($('input[name="balance"]').val()) ) { toastWarn('Paying amount cannot be bigger than due amount'); $(this).val(''); }
  $(".change").text(formatFixed(toNum($('input[name="paying_amount"]').val()) - toNum($(this).val())));
});
$('select[name="edit_paid_by_id"]').on("change", function() {
  var id=$('select[name="edit_paid_by_id"]').val();
  $('input[name="edit_cheque_no"]').attr('required', false);
  $(".payment-form").off("submit");
  if (id == 3) { $(".edit-btn").attr('data-clicked', true); $.getScript("vendor/stripe/checkout.js"); $(".card-element").show(); $("#edit-cheque").hide(); }
  else if (id == 4) { $("#edit-cheque").show(); $(".card-element").hide(); $('input[name="edit_cheque_no"]').attr('required', true); }
  else { $(".card-element").hide(); $("#edit-cheque").hide(); }
});
$('input[name="edit_amount"]').on("input", function() {
  if( toNum($(this).val()) > toNum($('input[name="edit_paying_amount"]').val()) ) { toastWarn('Paying amount cannot be bigger than received amount'); $(this).val(''); }
  $(".change").text(formatFixed(toNum($('input[name="edit_paying_amount"]').val()) - toNum($(this).val())));
});
$('input[name="edit_paying_amount"]').on("input", function() {
  $(".change").text(formatFixed(toNum($(this).val()) - toNum($('input[name="edit_amount"]').val())));
});

/* =========================================================
   Purchase Details (modal) — all numbers via fmt()
========================================================= */
function purchaseDetails(purchase) {
  var htmltext = `
    <div class="row">
      <h3 style="text-align:center;margin:0 auto;width:51%;margin-bottom:17px;margin-top:-18px;font-weight:700;">#  `+purchase[31]+`</h3>
      <h3 style="text-align:center;margin:0 auto;width:51%;margin-bottom:17px;margin-top:-18px;font-weight:700;">#  `+purchase[33]+`</h3>
      <div class="col-md-7">
        <div class="float-left">
          <strong style="color:#9a191c">Customer Info:</strong>
          <br><strong> Name:</strong> `+purchase[23]+`
          <br><strong> Email:</strong> <a href='mailto:`+purchase[24]+`' style="text-decoration: underline;">`+purchase[24]+`</a>  
          <br><strong> Phone:</strong> `+purchase[25]+`
          <br><strong> Company:</strong> `+purchase[26]+`
          <br><strong> Address:</strong> `+purchase[27]+`
          <br><strong> Website:</strong><a href='`+purchase[36]+`' style="text-decoration: underline;">`+purchase[36]+`</a>
        </div>
      </div>
      <div class="col-md-5">
        <div class="float-left">
          <strong>{{__("db.date")}}: </strong>` + purchase[0] + `<br>
          <strong>{{__("db.reference")}}: </strong>` + purchase[1] + `<br>
          <strong>{{__("db.Purchase Status")}}: </strong>` + purchase[2] + `<br>
          <strong>{{__("db.Currency")}}: </strong>` + purchase[29]+`
        </div>
      </div>
    </div><br>
  `;
  htmltext += '<div class="row">';
  if (purchase[28]) htmltext += '<strong>{{__("db.Attach Document")}}: </strong><a href="documents/purchase/' + purchase[28] + '">Download</a><br>';
  $(".product-purchase-list tbody").remove();

  $.get('purchases/product_purchase/' + purchase[3], function(data) {
    htmltext += `
      <div class="col-md-7">
        <div class="float-left">
          <strong style="color:#9a191c">Warehouse / Production info:</strong>
          <br><strong> Name:</strong> `+purchase[4]+`
          <br><strong> Company:</strong> `+purchase[32]+`
          <br><strong> Phone:</strong> `+purchase[5]+`
          <br><strong> Address:</strong> `+purchase[6]+`
          <br><strong> Website:</strong> <a href='`+purchase[37]+`' style="text-decoration: underline;">`+purchase[37]+`</a>
        </div>
      </div>
      <div class="col-md-5">
        <div class="float-left">
          <strong style="color:#9a191c">Shipping Instructions:</strong>
          <br>`+purchase[38]+`
        </div>
      </div>
    </div><br>`;

    /* Supplier table */
    htmltext  += `<div class="row"><div class="col-md-12"><div class="float-left">
      <table class="table table-bordered product-purchase-list_design" style="left:-9px">
        <thead>
          <th>{{__('Supplier')}}</th><th>{{__('Company')}}</th><th>{{__('Phone')}}</th><th>{{__('Email')}}</th><th>{{__('Address')}}</th><th>{{__('*')}}</th>
        </thead><tbody>`;
    let seenIds=[];
    if (Array.isArray(data[9])) {
      $(data[9]).each(function(k,v){
        if(!v || typeof v!=='object' || v.id==null){ toastWarn('Please set supplier data'); return; }
        if(seenIds.includes(v.id)) return; seenIds.push(v.id);
        htmltext += `<tr>
          <td>${v['name']||''}</td>
          <td>${v['company_name']||''}</td>
          <td>${v['phone_number']||''}</td>
          <td>${v['email']||''}</td>
          <td>${v['address']||''}</td>
          <td style="width:15%;">
            <button class="generate-pdf btn btn-secondary buttons-pdf buttons-html5" data-id="${v['id']}" data-purchaseid="${purchase[3]}" type="button" style="padding:6px;">
              <span><i title="Purchase PDF" class="fa fa-file-pdf-o"></i></span>
            </button>
            <button class="generate-shipped-pdf btn btn-warning" data-id="${v['id']}" data-purchaseid="${purchase[3]}" type="button">
              <span><i title="Shipment PDF" class="fa fa-truck"></i></span>
            </button>
          </td>
        </tr>`;
      });
    } else { console.warn("data[9] not array"); toastWarn('Supplier data is missing!'); }
    htmltext += `</tbody></table></div></div></div>`;

    /* Line items */
    var newBody=$("<tbody>");
    if (data === 'Something is wrong!') {
      newBody.append($("<tr>").append('<td colspan="12">Something is wrong!</td>'));
    } else {
      var name_code=data[0], qty=data[1], unit_code=data[2], tax=data[3], tax_rate=data[4],
          ship_cost=data[5], subtotal=data[6], batch_no=data[7], returned=data[8],
          lot_no=data[10], moq=data[11], ship_term=data[12], suppliers=data[9]||[];

      let totalShipCost=0, totalCost=0;

      $.each(name_code, function(index){
        if (ship_term[index]==null) ship_term[index]='';

        const qtyN=toNum(qty[index]);
        const unit=(unit_code[index]||'').toString();
        const subN=toNum(subtotal[index]);
        const scN =toNum(ship_cost[index]);
        const taxAmtN =toNum(tax[index]);
        const taxRateN=toNum(tax_rate[index]);

        totalShipCost = roundN(totalShipCost + scN);
        const rowTotal = roundN(subN + scN);
        totalCost      = roundN(totalCost + rowTotal);

        const perUnit = qtyN>0 ? roundN(subN / qtyN) : 0;
        const companyName = (suppliers[index] && suppliers[index]['company_name']) ? suppliers[index]['company_name'] : '';

        var newRow=$("<tr>");
        var cols='';
        cols += '<td><strong>' + (index+1) + '</strong></td>';
        cols += '<td>' + (name_code[index] ?? '') + '</td>';
        cols += '<td>' + companyName + '</td>';
        cols += '<td>' + ((batch_no[index] ?? '') + '') + '</td>';
        cols += '<td>' + ((lot_no[index] ?? '') + '') + '</td>';
        cols += '<td>' + fmt(qtyN) + ' ' + unit + '</td>';
        cols += '<td>' + ((moq[index] ?? '') + '') + '</td>';
        cols += '<td>' + (ship_term[index] ?? '') + '</td>';
        cols += '<td>' + fmt(perUnit) + '</td>';
        cols += '<td>' + fmt(taxAmtN) + ' (' + formatFixed(taxRateN) + '%)</td>';
        cols += '<td>' + fmt(scN) + '</td>';
        cols += '<td>' + fmt(rowTotal) + '</td>';
        newRow.append(cols); newBody.append(newRow);
      });

      const baseTotal = toNum(purchase[13]);
      const finalTotal = baseTotal === 0 ? totalCost : baseTotal;

      const rowData = [
        ['{{__("db.Total")}}',            fmt(finalTotal)],
        ['{{__("db.Order Tax")}}',        fmt(toNum(purchase[16])) + ' (' + formatFixed(toNum(purchase[17])) + '%)'],
        ['{{__("db.Order Discount")}}',   fmt(toNum(purchase[18]))],
        ['{{__("db.Shipping Cost")}}',    fmt(totalShipCost)],
        ['{{__("db.grand total")}}',      fmt(toNum(purchase[20]))],
        ['{{__("db.Paid Amount")}}',      fmt(toNum(purchase[21]))],
        ['{{__("db.Due")}}',              fmt(toNum(purchase[20]) - toNum(purchase[21]))]
      ];
      var th='<thead><tr>', td='<tbody><tr>';
      rowData.forEach(function(row){ th += `<th>${row[0]}</th>`; td += `<td>$ ${row[1]}</td>`; });
      td+='</tr><tbody>'; th+='</tr></thead>'+td;

      $("table.product-purchase-list2").html('');
      $("table.product-purchase-list").append(newBody);
      $("table.product-purchase-list2").html(th);
    }

    $('#purchase-content').html(htmltext);

    var htmlfooter = `<p><strong style="color:#9a191c">{{__("Special Instructions ")}}:</strong><br> `+purchase[35]+` </p>`;
    if(purchase[34]){
      htmlfooter += `
      <div style="margin-top: 30px; text-align: left;">
        <span style="font-style: italic;font-size: 12px;left: 5px;top: 15px;position: relative;"><p> `+purchase[34]+`</p></span><br>
        <div style="display:inline-block;text-align:center;border-top:1px solid #9a191c;padding-top:6px;width:180px;font-size:11px;color:#9a191c;">
          <strong>Authorized Signature</strong>
        </div>
      </div>`;
    }
    htmlfooter += `<div style="margin-top: 50px;text-align: center;font-size: 10px;color: #888;clear: both;width:56%;margin:0 auto;">
      © {{ date('Y') }} EZ-Solutions.co All rights reserved.
    </div>`;
    $('#purchase-footer').html(htmlfooter);
    $('#purchase-details').modal('show');
  });
}

/* =========================================================
   Print button (already above) & misc
========================================================= */
$("#close-btn").on("click", function(){ $('#purchase-details').modal('hide'); });

/* Delete payment confirm */
$(document).on('submit','form.delete-payment-form', async function(e){
  e.preventDefault();
  const ok = await sweetConfirm({ title:'Delete this payment?', text:'This will refund the money in records.', confirmText:'Delete', icon:'warning' });
  if(ok) this.submit();
});

/* hide delete if no permission */
if(all_permission.indexOf("purchases-delete")==-1) $('.buttons-delete').addClass('d-none');

/* Supplier PDF buttons */
$(document).on('click','.generate-pdf', function(){
  var supplierId=$(this).data('id'); var purchase_id=$(this).data('purchaseid'); window.open('purchases/pdf/'+supplierId+'/'+purchase_id,'_blank');
});

/* Shipped PDF (tracking) */
$(document).on('click','.generate-shipped-pdf', function(){
  const supplierId=$(this).data('id'); const purchaseId=$(this).data('purchaseid');
  $.ajax({
    url:'/purchases/check-shipment-dates', method:'POST',
    data:{ supplier_id:supplierId, purchase_id:purchaseId, _token:$('meta[name="csrf-token"]').attr('content') },
    success:function(response){
      const modal=$('#purchase-details'); modal.modal('hide');
      const productIds=response.product_ids||[];
      setTimeout(()=>{
        Swal.fire({
          title:'Enter Tracking Number', input:'text', inputLabel:'Tracking Number', inputPlaceholder:'e.g. TCN-123456',
          showCancelButton:true, confirmButtonText:'Submit', cancelButtonText:'Cancel', allowOutsideClick:false, allowEscapeKey:true,
          didOpen:()=>{ Swal.getInput().focus(); }, inputValidator:(value)=>{ if(!value) return 'Tracking number is required'; }
        }).then((result)=>{
          if(result.isConfirmed){
            const tracking_number=result.value;
            $.ajax({
              url:'/purchases/shipped-check', method:'POST',
              data:{ supplier_id:supplierId, purchase_id:purchaseId, tracking_number, product_ids:productIds, _token:$('meta[name="csrf-token"]').attr('content') },
              success:(res)=> toastSuccess(res.message||'Saved'),
              error:(xhr)=> toastError(xhr.responseJSON?.message||'Something went wrong.')
            });
          } else { modal.modal('show'); }
        });
      },300);
    },
    error:(xhr)=> toastWarn(xhr.responseJSON?.message || 'Please update product dates (ETS/ETA/ETD) first.')
  });
});
</script>
<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
@endpush
