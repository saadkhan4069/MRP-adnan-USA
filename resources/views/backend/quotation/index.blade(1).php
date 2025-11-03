@extends('backend.layout.main') 
@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<style>
    /* Modal box content */
#quotation-details .modal-content {
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    background-color: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Modal title */
#quotation-details .modal-title {
    font-size: 24px;
    font-weight: 600;
    text-transform: uppercase;
    color: #333;
    margin-top: 10px;
}

/* Modal logo */
#quotation-details img {
    position: absolute;
    top: 20px;
    right: 30px;
    width: 120px;
    height: auto;
}

/* Buttons */
#quotation-details .btn-default {
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    color: #333;
    padding: 5px 12px;
    border-radius: 4px;
    transition: all 0.3s ease;
}
#quotation-details .btn-default:hover {
    background-color: #e2e6ea;
}

/* Close button style (X icon) */
#quotation-details .close {
    float: right;
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
    opacity: 0.7;
}
#quotation-details .close:hover {
    color: red;
    opacity: 1;
}

/* Table design */
.product-quotation-list_design {
    border-collapse: separate;
    font-size: 10px;
    left: 9px;
    display: block;
    position: relative;
}
.product-quotation-list_design th, 
.product-quotation-list_design td {
    border: 1px solid #dee2e6;
    padding: 8px;
    text-align: center;
    vertical-align: middle;
}
.product-quotation-list_design thead {
    background-color: #343a40;
    color: #ffffff;
    text-transform: uppercase;
    font-size: 11px;
}
.product-quotation-list_design tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}
.product-quotation-list_design tbody tr:hover {
    background-color: #f1f1f1;
}

/* Footer space */
#quotation-footer {
    padding: 15px 10px;
    border-top: 1px solid #dee2e6;
}

/* Responsive tweaks */
@media (max-width: 768px) {
    #quotation-details img {
        position: static;
        display: block;
        margin: 10px auto;
    }
    #quotation-details .modal-title {
        font-size: 20px;
    }
}

/* ==========================
   Print Styling
========================== */
@media print {
    body * { visibility: hidden; }
    #quotation-details, #quotation-details * { visibility: visible; }
    #quotation-details {
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        background-color: #fff;
        padding: 20px;
        box-shadow: none;
    }
    .d-print-none { display: none !important; }
    .modal-content { border: none !important; }
    .product-quotation-list_design th, 
    .product-quotation-list_design td {
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
                <h3 class="text-center">{{__('db.Quotation List')}}</h3>
            </div>
            {!! Form::open(['route' => 'quotations.index', 'method' => 'get']) !!}
            <div class="row mb-3">
                <div class="col-md-4 offset-md-2 mt-3">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{__('db.Choose Your Date')}}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <div class="input-group">
                                <input type="text" class="daterangepicker-field form-control" value="{{$starting_date}} To {{$ending_date}}" required />
                                <input type="hidden" name="starting_date" value="{{$starting_date}}" />
                                <input type="hidden" name="ending_date" value="{{$ending_date}}" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mt-3 @if(\Auth::user()->role_id > 2){{'d-none'}}@endif">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{__('db.Choose Warehouse')}}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" >
                                <option value="0">{{__('db.All Warehouse')}}</option>
                                @foreach($lims_warehouse_list as $warehouse)
                                    @if($warehouse->id == $warehouse_id)
                                        <option selected value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                    @else
                                        <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
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
        @if(in_array("quotes-add", $all_permission))
            <a href="{{route('quotations.create')}}" class="btn btn-info"><i class="dripicons-plus"></i> {{__('db.Add Quotation')}}</a>&nbsp;
        @endif
    </div>
    <div class="table-responsive">
        <table id="quotation-table" class="table quotation-list" style="width: 100%">
            <thead>
                <tr>
                    <th class="not-exported"></th>
                    <th>{{__('db.date')}}</th>
                    <th>{{__('db.reference')}}</th>
                    <th>{{__('db.Warehouse')}}</th>
                    <th>{{__('db.Biller')}}</th>
                    <th>{{__('db.customer')}}</th>
                    <th>{{__('Total Qty')}}</th>
                    <th>{{__('db.Quotation Status')}}</th>
                    <th>{{__('db.grand total')}}</th>
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
            </tfoot>
        </table>
    </div>
</section>

<div id="quotation-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left" data-ref="" data-qno="" data-date="">
    <div role="document" class="modal-dialog">
      <div class="modal-content">
        <div class="container mt-3 pb-2 ">
            <div class="row">
                <div class="col-md-6 d-print-none">
                    <button id="print-btn" type="button" class="btn btn-default btn-sm d-print-none"><i class="dripicons-print"></i> {{__('db.Print')}}</button>

                    <!-- NEW: CSV & Excel export buttons -->
                    <button id="csv-btn" type="button" class="btn btn-default btn-sm d-print-none"><i class="fa fa-file-text-o"></i> CSV</button>
                    <button id="excel-btn" type="button" class="btn btn-default btn-sm d-print-none"><i class="dripicons-document-new"></i> Excel</button>

                    {{ Form::open(['route' => 'quotation.sendmail', 'method' => 'post', 'class' => 'sendmail-form'] ) }}
                        <input type="hidden" name="quotation_id">
                        <button class="btn btn-default btn-sm d-print-none"><i class="dripicons-mail"></i> {{__('db.Email')}}</button>
                    {{ Form::close() }}
                </div>

                <div class="col-md-6 d-print-none">
                    <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close" class="close d-print-none"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>

                <div class="col-md-12" style="height: 64px;">
                    <h3 id="exampleModalLabel" class="modal-title text-center container-fluid" >{{__('db.Quotation Details')}}</h3>
                    <img src="{{ asset('images/brandvolt.jpg') }}" style="display: inline-block;position: relative;width: 22%;top: -72px;left: -37px;float: right;">
                </div>
                <div class="col-md-12 text-center header-box"></div>

            </div>
        </div>
            <div id="quotation-content" class="modal-body">
            </div>
            <br>
            <table class="table  product-quotation-list  product-quotation-list_design">
                <thead>
                    <th>#</th>
                    <th>{{__('db.product')}}</th>
                    <th>{{__('db.Supplier')}}</th>
                    <th>Qty</th>
                    <th>MOQ</th>
                    <th>{{__('Unit Price')}}</th>
                    <th>{{__('Shipping Cost')}}</th>
                    <th>{{__('db.Subtotal')}}</th>
                </thead>
                <tbody></tbody>
            </table>
             <div class="row">
                <div class="col-md-12">
                    <div class="float-left">
                        <table class="table  product-quotation-list2 product-quotation-list_design" style="left: 8px">
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div id="quotation-footer" class="modal-body"></div>
      </div>
    </div>
</div>

<!-- SweetAlert2 (already included) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@push('scripts')

<script type="text/javascript">
/* ---------------------------
   SweetAlert2 Toast Helpers
----------------------------*/
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3500,
  timerProgressBar: true
});
// Global override: ANY alert('msg') becomes toast
window.alert = function(message){ Toast.fire({ icon: 'info', title: String(message) }); };
// Convenience helpers
function toastSuccess(msg){ Toast.fire({ icon: 'success', title: msg }); }
function toastError(msg){ Toast.fire({ icon: 'error', title: msg }); }
function toastWarn(msg){ Toast.fire({ icon: 'warning', title: msg }); }
// Sweet confirm (returns a Promise<boolean>)
function sweetConfirm({title='Are you sure?', text='', confirmText='Yes', cancelText='Cancel', icon='warning'}={}) {
  return Swal.fire({
    title, text, icon,
    showCancelButton: true,
    confirmButtonText: confirmText,
    cancelButtonText: cancelText,
    reverseButtons: true,
    focusCancel: true
  }).then(r => r.isConfirmed);
}

    $("ul#quotation").siblings('a').attr('aria-expanded','true');
    $("ul#quotation").addClass("show");
    $("ul#quotation #quotation-list-menu").addClass("active");

    $(".daterangepicker-field").daterangepicker({
      callback: function(startDate, endDate, period){
        var starting_date = startDate.format('YYYY-MM-DD');
        var ending_date = endDate.format('YYYY-MM-DD');
        var title = starting_date + ' To ' + ending_date;
        $(this).val(title);
        $('input[name="starting_date"]').val(starting_date);
        $('input[name="ending_date"]').val(ending_date);
      }
    });

    var all_permission = <?php echo json_encode($all_permission) ?>;
    var quotation_id = [];
    var user_verified = <?php echo json_encode(env('USER_VERIFIED')) ?>;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function confirmDelete() {
        if (confirm("Are you sure want to delete?")) {
            return true;
        }
        return false;
    }

    $(document).on("click", "tr.quotation-link td:not(:first-child, :last-child)", function() {
        var quotation = $(this).parent().data('quotation');
        quotationDetails(quotation);
    });

    $(document).on("click", ".view", function() {
        var quotation = $(this).parent().parent().parent().parent().parent().data('quotation');
        quotationDetails(quotation);
    });

    $("#print-btn").on("click", function(){
        var divContents = document.getElementById("quotation-details").innerHTML;
        var a = window.open('');
        a.document.write('<html>');
        a.document.write('<body><style>body{font-family: sans-serif;line-height: 1.15;-webkit-text-size-adjust: 100%;}.d-print-none{display:none}.text-center{text-align:center}.row{width:100%;margin-right: -15px;margin-left: -15px;}.col-md-12{width:100%;display:block;padding: 5px 15px;}.col-md-6{width: 50%;float:left;padding: 5px 15px;}table{width:100%;margin-top:30px;}th{text-aligh:left}td{padding:10px}table,th,td{border: 1px solid black; border-collapse: collapse;}</style><style>@media print {.modal-dialog { max-width: 1000px;} }</style>');
        a.document.write(divContents);
        a.document.write('</body></html>');
        a.document.close();
        setTimeout(function(){a.close();},10);
        a.print();
    });

    /* =========================
       NEW: CSV & Excel Export
    ========================= */
    // holding the last opened quotation array so export can use it
    window.currentQuotation = null;

    function getFileSafe(str){
        return String(str || '').toString()
            .replace(/[\\\/:*?"<>|]+/g,'-')
            .replace(/\s+/g,'_')
            .slice(0,120);
    }

    function currentFileBase(){
        const modal = $('#quotation-details');
        const qno   = modal.data('qno') || (window.currentQuotation ? window.currentQuotation[32] : '');
        const ref   = modal.data('ref') || (window.currentQuotation ? window.currentQuotation[1] : '');
        const date  = modal.data('date')|| (window.currentQuotation ? window.currentQuotation[0] : '');
        const parts = ['quotation', getFileSafe(qno || ref || 'export'), getFileSafe(date || '')].filter(Boolean);
        return parts.join('_');
    }

    function tableToArray($table){
        const rows = [];
        $table.find('tr').each(function(){
            const row = [];
            $(this).find('th,td').each(function(){
                let txt = $(this).text().replace(/\s+/g,' ').trim();
                row.push(txt);
            });
            if(row.length) rows.push(row);
        });
        return rows;
    }

    function arrayToCSV(rows){
        // RFC4180-ish CSV
        return rows.map(r => r.map(cell => {
            const c = (cell==null ? '' : String(cell));
            if(/[",\n]/.test(c)) return '"' + c.replace(/"/g,'""') + '"';
            return c;
        }).join(',')).join('\n');
    }

    function downloadBlob(content, mime, filename){
        const blob = new Blob([content], {type: mime + ';charset=utf-8;'});
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        setTimeout(()=>{ URL.revokeObjectURL(url); a.remove(); }, 0);
    }

    function exportQuotationCSV(){
        const rows = [];

        // Header meta from currentQuotation (saaf aur professional)
        const q = window.currentQuotation || [];
        const status = (q && q.length ? q[2] : '');
        rows.push(['Quotation #', (q && q.length ? (q[32] || '') : '')]);
        rows.push(['Date', (q && q.length ? (q[0] || '') : '')]);
        rows.push(['Reference', (q && q.length ? (q[1] || '') : '')]);
        rows.push(['Status', status]);
        rows.push([]);

        // Customer Info
        rows.push(['Customer Info']);
        rows.push(['Name', (q[9]||'')]);
        rows.push(['Email', (q[10]||'')]);
        rows.push(['Phone', (q[11]||'')]);
        rows.push(['Company', (q[12]||'')]);
        rows.push(['Address', (q[13]||'')]);
        rows.push(['Website', (q[14]||'')]);
        rows.push([]);

        // Warehouse / Production info
        rows.push(['Warehouse / Production Info']);
        rows.push(['Name', (q[27]||'')]);
        rows.push(['Company', (q[30]||'')]);
        rows.push(['Phone', (q[28]||'')]);
        rows.push(['Address', (q[29]||'')]);
        rows.push(['Website', (q[31]||'')]);
        rows.push([]);

        // Suppliers table (from DOM)
        rows.push(['Suppliers']);
        const suppRows = tableToArray($('.product-quotation-list_design').eq(0)); // first table after it is inserted inside details builder
        suppRows.forEach(r => rows.push(r));
        rows.push([]);

        // Product list table
        rows.push(['Products']);
        const prodRows = tableToArray($('table.product-quotation-list'));
        prodRows.forEach(r => rows.push(r));
        rows.push([]);

        // Totals table
        rows.push(['Totals']);
        const totalsRows = tableToArray($('table.product-quotation-list2'));
        totalsRows.forEach(r => rows.push(r));

        const csv = arrayToCSV(rows);
        const fname = currentFileBase() + '.csv';
        downloadBlob(csv, 'text/csv', fname);
        toastSuccess('CSV download started');
    }

    function exportQuotationExcel(){
        // Excel (HTML table trick) — professional & simple
        const meta = '<meta charset="utf-8" />';
        const style = '<style>table{border-collapse:collapse} td,th{border:1px solid #333; padding:4px;}</style>';

        // Clone live DOM pieces to avoid mutating UI
        const suppliersHTML = $('.product-quotation-list_design').eq(0).prop('outerHTML') || '';
        const productsHTML  = $('table.product-quotation-list').prop('outerHTML') || '';
        const totalsHTML    = $('table.product-quotation-list2').prop('outerHTML') || '';
        const headerTitle   = $('#quotation-content').text().trim().slice(0,200);

        const q = window.currentQuotation || [];
        const topMeta = `
            <table>
                <tr><th align="left">Quotation #</th><td>${(q[32]||'')}</td></tr>
                <tr><th align="left">Date</th><td>${(q[0]||'')}</td></tr>
                <tr><th align="left">Reference</th><td>${(q[1]||'')}</td></tr>
                <tr><th align="left">Status</th><td>${(q[2]||'')}</td></tr>
            </table><br/>
            <table>
                <tr><th colspan="2" align="left">Customer Info</th></tr>
                <tr><td>Name</td><td>${(q[9]||'')}</td></tr>
                <tr><td>Email</td><td>${(q[10]||'')}</td></tr>
                <tr><td>Phone</td><td>${(q[11]||'')}</td></tr>
                <tr><td>Company</td><td>${(q[12]||'')}</td></tr>
                <tr><td>Address</td><td>${(q[13]||'')}</td></tr>
                <tr><td>Website</td><td>${(q[14]||'')}</td></tr>
            </table><br/>
            <table>
                <tr><th colspan="2" align="left">Warehouse / Production Info</th></tr>
                <tr><td>Name</td><td>${(q[27]||'')}</td></tr>
                <tr><td>Company</td><td>${(q[30]||'')}</td></tr>
                <tr><td>Phone</td><td>${(q[28]||'')}</td></tr>
                <tr><td>Address</td><td>${(q[29]||'')}</td></tr>
                <tr><td>Website</td><td>${(q[31]||'')}</td></tr>
            </table><br/>
        `;

        const html = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office"
                  xmlns:x="urn:schemas-microsoft-com:office:excel"
                  xmlns="http://www.w3.org/TR/REC-html40">
              <head>${meta}${style}</head>
              <body>
                ${topMeta}
                <h3>Suppliers</h3>
                ${suppliersHTML}
                <br/>
                <h3>Products</h3>
                ${productsHTML}
                <br/>
                <h3>Totals</h3>
                ${totalsHTML}
              </body>
            </html>
        `;
        const fname = currentFileBase() + '.xls';
        downloadBlob(html, 'application/vnd.ms-excel', fname);
        toastSuccess('Excel download started');
    }

    $('#csv-btn').on('click', exportQuotationCSV);
    $('#excel-btn').on('click', exportQuotationExcel);

    /* DataTables */
    var starting_date = $("input[name=starting_date]").val();
    var ending_date = $("input[name=ending_date]").val();
    var warehouse_id = $("#warehouse_id").val();
    $('#quotation-table').DataTable( {
        "processing": true,
        "serverSide": true,
        "ajax":{
            url:"quotations/quotation-data",
            data:{
                all_permission: all_permission,
                starting_date: starting_date,
                ending_date: ending_date,
                warehouse_id: warehouse_id
            },
            dataType: "json",
            type:"post",
        },
        "createdRow": function( row, data, dataIndex ) {
            $(row).addClass('quotation-link');
            $(row).attr('data-quotation', data['quotation']);
        },
        "columns": [
            {"data": "key"},
            {"data": "date"},
            {"data": "reference_no"},
            {"data": "warehouse"},
            {"data": "biller"},
            {"data": "customer"},
            {"data": "total_qty"},
            {"data": "status"},
            {"data": "grand_total"},
            {"data": "options"},
        ],
        'language': {
            'lengthMenu': '_MENU_ {{__("db.records per page")}}',
            "info":      '<small>{{__("db.Showing")}} _START_ - _END_ (_TOTAL_)</small>',
            "search":  '{{__("db.Search")}}',
            'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
            }
        },
        order:[['1', 'desc']],
        'columnDefs': [
            {
                "orderable": false,
                'targets': [0, 3, 4, 7, 8, 9]
            },
            {
                'render': function(data, type, row, meta){
                    if(type === 'display'){
                        data = '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                    }
                   return data;
                },
                'checkboxes': {
                   'selectRow': true,
                   'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                },
                'targets': [0]
            }
        ],
        'select': { style: 'multi',  selector: 'td:first-child'},
        'lengthMenu': [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: '<"row"lfB>rtip',
        buttons: [
            {
                extend: 'pdf',
                text: '<i title="export to pdf" class="fa fa-file-pdf-o"></i>',
                exportOptions: {
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'excel',
                text: '<i title="export to excel" class="dripicons-document-new"></i>',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'csv',
                text: '<i title="export to csv" class="fa fa-file-text-o"></i>',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                extend: 'print',
                text: '<i title="print" class="fa fa-print"></i>',
                exportOptions: {
                    columns: ':visible:not(.not-exported)',
                    rows: ':visible'
                },
                action: function(e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer:true
            },
            {
                text: '<i title="delete" class="dripicons-cross"></i>',
                className: 'buttons-delete',
                action: function ( e, dt, node, config ) {
                    if(user_verified == '1') {
                        quotation_id.length = 0;
                        $(':checkbox:checked').each(function(i){
                            if(i){
                                var quotation = $(this).closest('tr').data('quotation');
                                quotation_id[i-1] = quotation[13];
                            }
                        });
                        if(quotation_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type:'POST',
                                url:'quotations/deletebyselection',
                                data:{
                                    quotationIdArray: quotation_id
                                },
                                success:function(data) {
                                    alert(data);
                                    dt.rows({ page: 'current', selected: true }).remove().draw(false);
                                }
                            });
                        }
                        else if(!quotation_id.length)
                            alert('Nothing is selected!');
                    }
                    else
                        alert('This feature is disable for demo!');
                }
            },
            {
                extend: 'colvis',
                text: '<i title="column visibility" class="fa fa-eye"></i>',
                columns: ':gt(0)'
            },
        ],
        drawCallback: function () {
            var api = this.api();
            datatable_sum(api, false);
        }
    } );

    // FIX: datatable_sum else-branch rows scope
    function datatable_sum(dt_selector, is_calling_first) {
        let colIndex = 8;
        if (dt_selector.rows('.selected').any() && is_calling_first) {
            var rows = dt_selector.rows('.selected').indexes();
            $( dt_selector.column(colIndex).footer() ).html(
                dt_selector.cells(rows, colIndex, { page: 'current' }).data().sum().toFixed({{$general_setting->decimal}})
            );
        } else {
            var rowsAll = dt_selector.rows({ page: 'current' }).indexes();
            $( dt_selector.column(colIndex).footer() ).html(
                dt_selector.cells(rowsAll, colIndex, { page: 'current' }).data().sum().toFixed({{$general_setting->decimal}})
            );
        }
    }

    if(all_permission.indexOf("quotes-delete") == -1)
        $('.buttons-delete').addClass('d-none');

    /* ---------------------------
       Quotation Details
    ----------------------------*/
function quotationDetails(quotation) {
    window.currentQuotation = quotation; // store globally for CSV/Excel
    // also store in modal dataset for filename parts
    $('#quotation-details')
      .attr('data-ref', quotation[1] || '')
      .attr('data-qno', quotation[32] || '')
      .attr('data-date', quotation[0] || '');

    var htmltext = `
    <div class="row">
      <h3 style="text-align:center;margin:0 auto;width:51%;margin-bottom:17px;margin-top:-18px;font-weight:700;">#  `+quotation[32]+`</h3>

      <div class="col-md-7">
        <div class="float-left">
          <strong style="color:#9a191c">Customer Info:</strong>
          <br><strong> Name:</strong> `+quotation[9]+`
          <br><strong> Email:</strong> <a href='mailto:`+quotation[10]+`' style="text-decoration: underline;">`+quotation[10]+`</a>  
          <br><strong> Phone:</strong> `+quotation[11]+`
          <br><strong> Company:</strong> `+quotation[12]+`
          <br><strong> Address:</strong> `+quotation[13]+`
          <br><strong> Website:</strong><a href='`+quotation[14]+`' style="text-decoration: underline;">`+quotation[14]+`</a>
        </div>
      </div>
      
      <div class="float-left">
          <strong>{{__("db.date")}}: </strong>` + quotation[0] + `<br>
          <strong>{{__("db.reference")}}: </strong>` + quotation[1] + `<br>
          <strong>{{__("Status")}}: </strong>`;

           if(quotation[2] == "Pending") {
                     htmltext += `<div class="badge badge-danger">`+quotation[2]+`</div>`;
                }
                else{
                     htmltext += `<div class="badge badge-success">`+quotation[2]+`</div>`;
                }
                htmltext +=`
        </div>
    </div>
    <br><br>`;

    htmltext += '<div class="row">';
    htmltext += `<div class="col-md-6">
              <div class="float-left">
                <strong style="color:#9a191c">Warehouse / Production info:</strong>
                <br><strong> Name:</strong> ` + quotation[27] + `
                <br><strong> Company:</strong> ` + quotation[30] + `
                <br><strong> Phone:</strong> ` + quotation[28] + `
                <br><strong> Address:</strong> ` + quotation[29] + `
                <br><strong> Website:</strong> <a href='`+quotation[31] + `' style="text-decoration: underline;">` + quotation[31] + `</a>
            </div>
      </div><br>`;

    if (quotation[33]) {
        htmltext += '<strong>{{__("db.Attach Document")}}: </strong><a href="documents/quotation/' + quotation[33] + '">Download</a><br>';
    }
    $(".product-quotation-list tbody").remove();

    $.get('quotations/product_quotation/' + quotation[3], function(data) {
        htmltext += `</div>`;

        htmltext  += `<div class="row">
          <div class="col-md-12">
            <div class="float-left">
              <table class="table  product-quotation-list_design" style="left:-9px">
                <thead>
                  <th>{{__('Supplier')}}</th>
                  <th>{{__('Company')}}</th>
                  <th>{{__('Phone')}}</th>
                  <th>{{__('Email')}}</th>
                  <th>{{__('Address')}}</th>
                  <th>{{__('*')}}</th>
                </thead>
                <tbody>`;

        let seenIds = [];
        if (Array.isArray(data[9])) {
            $(data[9]).each(function(k, v) {
                if (!v || typeof v !== 'object' || v.id === undefined || v.id === null) {
                    toastWarn('Please set supplier data');
                    return;
                }
                if (seenIds.includes(v.id)) return;
                seenIds.push(v.id);
                htmltext += `<tr>
                  <td>` + v['name'] + `</td>
                  <td>` + v['company_name'] + `</td>
                  <td>` + v['phone_number'] + `</td>
                  <td>` + v['email'] + `</td>
                  <td>` + v['address'] + `</td>
                  <td style="width: 15%;">
                    <button class="generate-pdf btn btn-secondary buttons-pdf buttons-html5"
                        data-id="`+v['id']+`"
                        data-quotation="`+quotation[3]+`"
                        type="button" style="padding: 6px;">
                      <span><i title="Quotation PDF" class="fa fa-file-pdf-o"></i></span>
                    </button>
                  </td>
                </tr>`;
            });
        } else {
            console.warn("data[9] is not an array");
            toastWarn('Supplier data is missing!');
        }

        htmltext += `
                </tbody>
              </table>
            </div>
          </div>
        </div></div>`;

        var newBody = $("<tbody>");
        if (data === 'Something is wrong!') {
            var newRow = $("<tr>");
            var cols = '<td colspan="8">Something is wrong!</td>';
            newRow.append(cols);
            newBody.append(newRow);
        } else {
            var name_code = data[0];
            var qty = data[1];
            var unit_code = data[2];
            var tax = data[3];
            var tax_rate = data[4];
            var ship_cost = data[5];
            var subtotal = data[6];
            var moq = data[11];

            let totalShipCost = 0;
            $.each(name_code, function(index) {
                let thisShipCost = parseFloat(ship_cost[index]) || 0;
                totalShipCost += thisShipCost;
                var newRow = $("<tr>");
                var cols = '';
                cols += '<td><strong>' + (index + 1) + '</strong></td>';
                cols += '<td>' + name_code[index] + '</td>';
                cols += '<td>' + data[9][index]['company_name'] + '</td>';
                cols += '<td>' + qty[index] + ' ' + unit_code[index] + '</td>';
                cols += '<td>' + moq[index] + '</td>';
                cols += '<td>' + (subtotal[index] / qty[index]).toFixed(2) + '</td>';
                cols += '<td>' + ship_cost[index] + '</td>';
                cols += '<td>' + (ship_cost[index] + subtotal[index]) + '</td>';
                newRow.append(cols);
                newBody.append(newRow);
            });

            var rowData = [
                ['{{__("db.Total")}}', quotation[17]],
                ['{{__("db.Order Tax")}}', quotation[18] + ' (' + quotation[19] + '%)'],
                ['{{__("db.Order Discount")}}', quotation[20]],
                ['{{__("db.Shipping Cost")}}', totalShipCost],
                ['{{__("db.grand total")}}', quotation[22]],
            ];
            var th = '<thead><tr>';
            var td = '<tbody><tr>';
            rowData.forEach(function(row) {
                th += `<th>` + row[0] + `</th>`;
                td += `<td>` + row[1] + `</td>`;
            });
            td += '</tr><tbody>';
            th += '</tr></thead>' + td;

            $("table.product-quotation-list").append(newBody);
            $("table.product-quotation-list2").html(th);
        }
       
        $('#quotation-content').html(htmltext);
        let  htmlfooter = '';
        if(quotation[26]){
            htmlfooter += `
            <div style="margin-top: 30px; text-align: left;">
                <span style="font-style: italic;font-size: 12px;left: 5px;top: 15px;position: relative;"><p> `+quotation[26]+`</p></span><br>
                <div style="display:inline-block;text-align:center;border-top:1px solid #9a191c;padding-top:6px;width:180px;font-size:11px;color:#9a191c;">
                    <strong>Authorized Signature</strong>
                </div>
            </div>`;
        }
        htmlfooter += `<div style="margin-top: 50px;text-align: center;font-size: 10px;color: #888;clear: both;width:56%;margin:0 auto;">
            © {{ date('Y') }} EZ-Solutions.co All rights reserved.
        </div>`;

        $('#quotation-footer').html(htmlfooter);
        $('#quotation-details').modal('show');

    });
}

/* ---------------------------
   Supplier PDF buttons
----------------------------*/
$(document).on('click', '.generate-pdf', function () {
    var supplierId = $(this).data('id');
    var quotation_id = $(this).data('quotation');
    var url = 'quotations/pdf/' + supplierId + '/' + quotation_id;
    window.open(url, '_blank');
});
</script>
@endpush
