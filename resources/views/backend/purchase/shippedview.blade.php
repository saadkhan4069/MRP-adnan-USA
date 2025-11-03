@extends('backend.layout.main') @section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

    <style>
/* ==========================
   Purchase Order Modal Design
========================== */

/* Modal box content */
#shipment-details .modal-content {
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    background-color: #fff;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Modal title */
#shipment-details .modal-title {
    font-size: 24px;
    font-weight: 600;
    text-transform: uppercase;
    /*color: #333;*/
    margin-top: 10px;
    width: 100%;
    margin: 0 auto;
    text-align: center;
}

/* Modal logo */
#shipment-details img {
    position: absolute;
    top: 20px;
    right: 30px;
    width: 120px;
    height: auto;
}

/* Buttons */
#shipment-details .btn-default {
    background-color: #f8f9fa;
    border: 1px solid #ced4da;
    color: #333;
    padding: 5px 12px;
    border-radius: 4px;
    transition: all 0.3s ease;
}
#shipment-details .btn-default:hover {
    background-color: #e2e6ea;
}

/* Close button style (X icon) */
#shipment-details .close {
    float: right;
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
    opacity: 0.7;
}
#shipment-details .close:hover {
    color: red;
    opacity: 1;
}

/* Table design */
.product-purchase-list_design {
       /*width: 98%;*/
    border-collapse: separate;
    /* margin-top: 15px; */
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

/* Responsive tweaks (optional) */
@media (max-width: 768px) {
    #shipment-details img {
        position: static;
        display: block;
        margin: 10px auto;
    }

    #shipment-details .modal-title {
        font-size: 20px;
    }
}

/* ==========================
   Print Styling
========================== */
@media print {
    body * {
        visibility: hidden;
    }

    #shipment-details, 
    #shipment-details * {
        visibility: visible;
    }

    #shipment-details {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        background-color: #fff;
        padding: 20px;
        box-shadow: none;
    }

    .d-print-none {
        display: none !important;
    }

    .modal-content {
        border: none !important;
    }

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

.dt-buttons
{
    width: 100% !important;
}
</style>

<section>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{__('Shipping List')}}</h3>
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
               
               
               
                <div class="col-md-2 mt-3">
                    <div class="form-group">
                        <button class="btn btn-primary" id="filter-btn" type="submit">{{__('db.submit')}}</button>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
      
    </div>
    <div class="table-responsive">
    <table id="shipped-table" class="table table-striped table-bordered" style="width: 100%">
        <thead>
            <tr>
                <th></th> <!-- Expand -->
                <th>PO#</th>
                <th>Reference</th>
                <th>Supplier</th>
                <th>Customer</th>
                <th>Shipped Date</th>
                <th>Shipping Cost</th>
                <th>Status</th>
                <th>Courier Name</th>
                <th>Tracking No</th>
                <th>Actions</th>
            </tr>
        </thead>
    </table>
</div>
</section>

<!-- Shipment Details Modal -->
<div class="modal fade" id="shipment-details" tabindex="-1" role="dialog" aria-labelledby="shipmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">

      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title" id="shipmentModalLabel">
          <i class="fa fa-truck"></i> Shipment Details
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body" id="shipment-modal-body">
        <!-- Shipment data from Ajax will be injected here -->
        <div class="text-center py-4">
          <i class="fa fa-spinner fa-spin"></i> Loading...
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fa fa-times"></i> Close
        </button>
      </div>

    </div>
  </div>
</div>


<div class="modal fade" id="shipmentEditModal" tabindex="-1" role="dialog" aria-labelledby="shipmentEditLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="shipmentEditForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Update Shipment</h5>
          <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" id="shipment_id">

            <div class="form-group">
                <label>Status</label>
                <select name="shipment_status" id="shipment_status" class="form-control">
                    <option value="0">Pending</option>
                    <option value="1">Processing</option>
                    <option value="2">Packed</option>
                    <option value="3">Dispatched</option>
                    <option value="4">In Transit</option>
                    <option value="5">Delivered</option>
                    <option value="6">Failed</option>
                </select>
            </div>

            <div class="form-group">
                <label>Courier</label>
                <select name="carrier_id" id="carrier_id" class="form-control">
                    @foreach($couriers as $courier)
                        <option value="{{ $courier->id }}">{{ $courier->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Tracking Number</label>
                <input type="text" class="form-control" name="tracking_number" id="tracking_number">
            </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>


 

@endsection

@push('scripts')
<!-- Include this in <head> ya end of body -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="text/javascript">

function format(rowData) {
    if (!rowData.products_data || rowData.products_data.length === 0) {
        return '<div class="p-3"><em>No product data available.</em></div>';
    }

    let rows = rowData.products_data.map(p => `
        <tr>
            <td>${p.product_name}</td>
            <td>${p.qty}</td>
            <td>${p.unit_cost}</td>
            <td>${p.ship_cost}</td>
        </tr>
    `).join('');

    return `
        <div class="container-fluid">
            <h6><strong>Shipped Products</strong></h6>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Cost</th>
                        <th>Shipping Cost</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `;
}

// ------------datatable-----------------------
$(document).ready(function () {
    var table = $('#shipped-table').DataTable({
        processing: true,
        serverSide: true,
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10,

        dom:
            "<'row mb-2'<'col-md-4'l><'col-md-4 text-center'f><'col-md-4 text-right'B>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row mt-2'<'col-md-6'i><'col-md-6 text-right'p>>",

        buttons: [
            {
                extend: 'copy',
                text: '<i class="fa fa-copy"></i>',
                className: 'btn btn-secondary btn-sm'
            },
            {
                extend: 'excel',
                text: '<i class="fa fa-file-excel-o"></i>',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'csv',
                text: '<i class="fa fa-file-text-o"></i>',
                className: 'btn btn-info btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="fa fa-file-pdf-o"></i>',
                className: 'btn btn-danger btn-sm'
            },
            {
                extend: 'print',
                text: '<i class="fa fa-print"></i>',
                className: 'btn btn-dark btn-sm'
            },
            {
                extend: 'colvis',
                text: '<i class="fa fa-eye-slash"></i>',
                className: 'btn btn-warning btn-sm'
            }
        ],

        ajax: {
            url: "{{ route('shipped.data') }}",
            type: "POST",
            data: function (d) {
                d._token = '{{ csrf_token() }}';
                d.starting_date = $('input[name=starting_date]').val();
                d.ending_date = $('input[name=ending_date]').val();
                d.shipping_status = $('select[name=shipping_status]').val();
            }
        },

        columns: [
            { className: 'dt-control', orderable: false, data: null, defaultContent: '' },
            { data: 'po_no' },
            { data: 'reference_no' },
            { data: 'supplier_name' },
            { data: 'customer_name' },
            { data: 'date' },
            { data: 'ship_cost' },
            { data: 'shipment_status' },
            { data: 'courier' },
            { data: 'tracking_number' },
            { data: 'options', orderable: false, searchable: false, className: 'no-click' }
        ],

        order: [[1, 'desc']]
    });

    // Expand/Collapse child row
    $('#shipped-table tbody').on('click', 'td.dt-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            row.child(format(row.data())).show();
            tr.addClass('shown');
        }
    });

    // Modal trigger on all td except no-click and dt-control
    $('#shipped-table').on('click', 'tbody tr td:not(.no-click):not(.dt-control)', function () {
        let tr = $(this).closest('tr');
        let rowData = table.row(tr).data();

        let purchaseId = rowData.purchase_id;
        let supplierId = rowData.supplier_id;

        if (!purchaseId || !supplierId) return;

        $('#shipment-modal-body').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
        $('#shipment-details').modal('show');

        $.ajax({
            url: '/shipment/modal/view',
            method: 'GET',
            data: {
                purchase_id: purchaseId,
                supplier_id: supplierId
            },
            success: function (response) {
                $('#shipment-modal-body').html(response);
            },
            error: function () {
                $('#shipment-modal-body').html('<div class="text-danger text-center py-4">Error loading shipment details.</div>');
            }
        });
    });

    // Date Range Picker
    $('input[name="date_range"]').daterangepicker({
        locale: { format: 'YYYY-MM-DD' }
    });

    $('input[name="date_range"]').on('apply.daterangepicker', function (ev, picker) {
        $('input[name="starting_date"]').val(picker.startDate.format('YYYY-MM-DD'));
        $('input[name="ending_date"]').val(picker.endDate.format('YYYY-MM-DD'));
        table.draw();
    });

    // Shipping status filter change
    $('select[name="shipping_status"]').change(function () {
        table.draw();
    });
});

// ------------datatable-----------------------


// -------child view-----------------------------
$(document).ready(function () {
    var table = $('#shipped-table').DataTable();

    // Handle View button click (custom toggle)
    $('#shipped-table tbody').on('click', '.view-details', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        // Collapse if already open
        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        } else {
            let purchase_id = $(this).data('purchase');
            let supplier_id = $(this).data('supplier');

            // Show loading temporarily
            row.child('<div style="padding:10px;">Loading...</div>').show();

            // Fetch product data from Laravel route via AJAX
            $.ajax({
                url: '/shipped/child-details',
                method: 'GET',
                data: {
                    purchase_id: purchase_id,
                    supplier_id: supplier_id
                },
                success: function (response) {
                    row.child(response).show();
                    tr.addClass('shown');
                }
            });
        }
    });
});

// -------child view-----------------------------


$(document).on('click', '.edit-shipment', function () {
       $('#shipment_id').val($(this).data('id'));
    $('#shipment_status').val($(this).data('status')).selectpicker('refresh');
    $('#carrier_id').val($(this).data('courier')).selectpicker('refresh');
    $('#tracking_number').val($(this).data('tracking'));
    $('#shipmentEditModal').modal('show');
});

$('#shipmentEditForm').submit(function (e) {
    e.preventDefault();

    let formData = $(this).serialize();

    $.ajax({
        url: "{{ route('shipment.update') }}",
        method: "POST",
        data: formData,
        success: function (res) {
            $('#shipmentEditModal').modal('hide');
            // toastr.success('Shipment updated!');
              Swal.fire('Success!','Shipment updated!', 'success');
            $('#shipped-table').DataTable().ajax.reload(null, false);
        },
        error: function (xhr) {
              Swal.fire('Error!','Shipment updated!', 'error');
            // toastr.error('Something went wrong.');
        }
    });
});



$(document).ready(function () {
    $('#shipped-table').on('click', 'tbody tr td:not(.no-click)', function () {
        let tr = $(this).closest('tr');
        let purchaseId = tr.data('purchaseid');
        let supplierId = tr.data('supplierid');

        if (!purchaseId || !supplierId) return;

        $('#shipment-modal-body').html('<div class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
        $('#shipment-details').modal('show');

        $.ajax({
            url: '/shipment/modal/view',
            method: 'GET',
            data: {
                purchase_id: purchaseId,
                supplier_id: supplierId
            },
            success: function (response) {
                $('#shipment-modal-body').html(response);
            },
            error: function () {
                $('#shipment-modal-body').html('<div class="text-danger text-center py-4">Error loading shipment details.</div>');
            }
        });
    });
});



</script>

@endpush

