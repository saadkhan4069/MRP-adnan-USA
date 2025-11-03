@extends('backend.layout.main') @section('content')

<x-error-message key="not_permitted" />

<style>
    .inventory-header {
        background: #007bff;
        color: white;
        padding: 15px 20px;
        border-radius: 3px 3px 0 0;
        margin-bottom: 0;
    }
    .inventory-header h3 {
        margin: 0;
        font-weight: 600;
        font-size: 18px;
    }
    .inventory-header p {
        margin: 3px 0 0 0;
        opacity: 0.9;
        font-size: 12px;
    }
    .filter-card {
        border: 1px solid #dee2e6;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 3px;
        margin-bottom: 15px;
    }
    .filter-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 0 0 3px 3px;
    }
    .form-label-custom {
        font-weight: 600;
        color: #495057;
        font-size: 12px;
        margin-bottom: 5px;
        display: block;
    }
    .filter-btn-custom {
        background: #007bff;
        border: 1px solid #007bff;
        padding: 8px 15px;
        border-radius: 3px;
        color: white;
        font-weight: 500;
        font-size: 12px;
        transition: all 0.3s;
    }
    .filter-btn-custom:hover {
        background: #0056b3;
        border-color: #0056b3;
    }
    .reset-btn-custom {
        background: #6c757d;
        border: 1px solid #6c757d;
        padding: 8px 15px;
        border-radius: 3px;
        color: white;
        font-weight: 500;
        font-size: 12px;
        transition: all 0.3s;
    }
    .reset-btn-custom:hover {
        background: #545b62;
        border-color: #545b62;
    }
    .table-card {
        border: 1px solid #dee2e6;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 3px;
    }
    #inventory-movement-table thead th {
        background: #007bff;
        color: white;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 11px;
        padding: 10px 8px;
    }
    #inventory-movement-table tbody tr {
        transition: all 0.2s;
    }
    #inventory-movement-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    #inventory-movement-table tfoot th {
        background: #f8f9fa;
        font-weight: 700;
        color: #495057;
        border-top: 2px solid #007bff;
        font-size: 12px;
        padding: 8px;
    }
    .badge-in {
        background: #28a745;
        color: white;
        padding: 4px 8px;
        border-radius: 3px;
        font-weight: 600;
        font-size: 10px;
    }
    .badge-out {
        background: #dc3545;
        color: white;
        padding: 4px 8px;
        border-radius: 3px;
        font-weight: 600;
        font-size: 10px;
    }
    .qty-in-cell {
        color: #28a745;
        font-weight: 700;
        font-size: 12px;
    }
    .qty-out-cell {
        color: #dc3545;
        font-weight: 700;
        font-size: 12px;
    }
    /* Export buttons styling */
    .dataTables_wrapper .dt-buttons {
        float: left;
        margin-bottom: 10px;
    }
    .dt-buttons .btn {
        margin-right: 3px;
        margin-bottom: 3px;
        border-radius: 3px;
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 500;
        display: inline-block;
        vertical-align: top;
    }
    .badge {
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 600;
    }
    .badge-primary {
        background: #007bff;
        border: none;
    }
    .badge-success {
        background: #28a745;
        border: none;
    }
    #inventory-movement-table tbody td {
        vertical-align: middle;
        padding: 8px;
        font-size: 12px;
    }
    .dataTables_info {
        font-weight: 500;
        color: #007bff;
        font-size: 12px;
    }
    .page-item.active .page-link {
        background: #007bff;
        border-color: #007bff;
    }
    .page-link {
        color: #007bff;
        font-size: 12px;
        padding: 0.375rem 0.75rem;
    }
    .page-link:hover {
        color: #0056b3;
        background-color: #f8f9fa;
    }
    .dataTables_filter input {
        border: 1px solid #ced4da;
        border-radius: 3px;
        padding: 4px 8px;
        font-size: 12px;
        transition: all 0.3s;
    }
    .dataTables_filter input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .dataTables_processing {
        background: #007bff;
        color: white;
        border-radius: 3px;
        padding: 15px 30px;
        font-weight: 600;
        font-size: 12px;
    }
    /* Fix double pagination */
    .dataTables_wrapper .dataTables_paginate {
        float: right;
        text-align: right;
        padding-top: 0.25em;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        box-sizing: border-box;
        display: inline-block;
        min-width: 1.5em;
        padding: 0.375rem 0.75rem;
        margin-left: 2px;
        text-align: center;
        text-decoration: none !important;
        cursor: pointer;
        border: 1px solid transparent;
        border-radius: 2px;
        background: none;
        color: #007bff !important;
        font-size: 12px;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        color: white !important;
        border: 1px solid #007bff;
        background: #007bff;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        color: white !important;
        border: 1px solid #007bff;
        background: #007bff;
    }
    /* DataTables length selector */
    .dataTables_length {
        font-size: 12px;
    }
    .dataTables_length select {
        font-size: 12px;
        padding: 2px 5px;
        border-radius: 3px;
    }
    /* Responsive */
    @media (max-width: 768px) {
        .filter-section .row > div {
            margin-bottom: 8px;
        }
        .inventory-header h3 {
            font-size: 16px;
        }
        .inventory-header p {
            font-size: 11px;
        }
        #inventory-movement-table {
            font-size: 10px;
        }
        .dt-buttons .btn {
            padding: 4px 8px;
            font-size: 10px;
            margin-bottom: 3px;
        }
        .filter-section {
            padding: 10px;
        }
    }
    /* Loading spinner */
    .dataTables_wrapper .dataTables_processing {
        position: fixed !important;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
    }
</style>

<section class="forms">
    <div class="container-fluid">
        <div class="card filter-card">
            <div class="inventory-header">
                <h3><i class="dripicons-swap"></i> Inventory Movement History</h3>
                <p>Track all inventory IN and OUT movements from purchases and sales</p>
            </div>
            <div class="filter-section">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label-custom"><i class="dripicons-calendar"></i> {{__('db.Choose Your Date')}}</label>
                            <div class="input-group">
                                <input type="text" class="daterangepicker-field form-control" placeholder="Select date range" />
                                <input type="hidden" name="start_date" />
                                <input type="hidden" name="end_date" />
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-label-custom"><i class="dripicons-box"></i> {{__('db.Product')}}</label>
                            <select name="product_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="All Products">
                                <option value="all">{{__('db.All Products')}}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-label-custom"><i class="dripicons-store"></i> {{__('db.Warehouse')}}</label>
                            <select name="warehouse_id" class="selectpicker form-control" data-live-search="true">
                                <option value="all">{{__('db.All Warehouse')}}</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-label-custom"><i class="dripicons-direction"></i> Movement Type</label>
                            <select name="movement_type" class="form-control">
                                <option value="all">All Movements</option>
                                <option value="IN">⬇ IN (Purchase)</option>
                                <option value="OUT">⬆ OUT (Sale)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="form-label-custom">&nbsp;</label>
                            <button class="btn btn-block filter-btn-custom filter-btn" type="button">
                                <i class="fa fa-filter"></i> Apply Filter
                            </button>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label class="form-label-custom">&nbsp;</label>
                            <button class="btn btn-block reset-btn-custom reset-btn" type="button">
                                <i class="fa fa-refresh"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container-fluid">
        <div class="card table-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="inventory-movement-table" class="table table-hover" style="width: 100%">
                        <thead>
                            <tr>
                                <th><i class="dripicons-calendar"></i> Date & Time</th>
                                <th><i class="dripicons-box"></i> Product</th>
                                <th><i class="dripicons-document"></i> Type</th>
                                <th><i class="dripicons-direction"></i> Movement</th>
                                <th><i class="dripicons-store"></i> Warehouse</th>
                                <th><i class="dripicons-arrow-down"></i> Qty IN</th>
                                <th><i class="dripicons-arrow-up"></i> Qty OUT</th>
                                <th><i class="dripicons-link"></i> Reference</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th colspan="5" style="text-align: right;">Total:</th>
                                <th id="total-in" class="qty-in-cell"></th>
                                <th id="total-out" class="qty-out-cell"></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize date range picker
    $(".daterangepicker-field").daterangepicker({
        callback: function(startDate, endDate, period){
            var start_date = startDate.format('YYYY-MM-DD');
            var end_date = endDate.format('YYYY-MM-DD');
            var title = start_date + ' To ' + end_date;
            $(this).val(title);
            $("input[name=start_date]").val(start_date);
            $("input[name=end_date]").val(end_date);
        }
    });

    // Load all products for filter
    $.get('{{ url("products/lims_product_search") }}', {data: 'all'}, function(data) {
        var options = '<option value="all">All Products</option>';
        $.each(data, function(index, product) {
            options += '<option value="' + product.id + '">' + product.code + ' - ' + product.name + '</option>';
        });
        $('select[name="product_id"]').html(options);
        $('.selectpicker').selectpicker('refresh');
    });

    var inventoryMovementTable;

    function loadInventoryMovements() {
        var start_date = $("input[name=start_date]").val();
        var end_date = $("input[name=end_date]").val();
        var product_id = $("select[name=product_id]").val();
        var warehouse_id = $("select[name=warehouse_id]").val();
        var movement_type = $("select[name=movement_type]").val();

        if (inventoryMovementTable) {
            inventoryMovementTable.destroy();
        }

        inventoryMovementTable = $('#inventory-movement-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: "{{ route('inventory-movement.data') }}",
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    product_id: product_id,
                    warehouse_id: warehouse_id,
                    movement_type: movement_type
                },
                dataType: "json",
                type: "post"
            },
            "columns": [
                {"data": "date"},
                {"data": "product"},
                {
                    "data": "type",
                    "render": function(data, type, row) {
                        if (data === 'Purchase') {
                            return '<span class="badge badge-primary"><i class="dripicons-basket"></i> Purchase</span>';
                        } else {
                            return '<span class="badge badge-success"><i class="dripicons-cart"></i> Sale</span>';
                        }
                    }
                },
                {
                    "data": "movement",
                    "render": function(data, type, row) {
                        if (data === 'IN') {
                            return '<span class="badge-in">⬇ IN</span>';
                        } else {
                            return '<span class="badge-out">⬆ OUT</span>';
                        }
                    }
                },
                {"data": "warehouse"},
                {
                    "data": "qty_in",
                    "render": function(data, type, row) {
                        if (data !== '-') {
                            return '<span class="qty-in-cell">+ ' + data + '</span>';
                        }
                        return '<span style="color: #ccc;">-</span>';
                    }
                },
                {
                    "data": "qty_out",
                    "render": function(data, type, row) {
                        if (data !== '-') {
                            return '<span class="qty-out-cell">- ' + data + '</span>';
                        }
                        return '<span style="color: #ccc;">-</span>';
                    }
                },
                {
                    "data": "reference",
                    "render": function(data, type, row) {
                        return '<code style="background: #f8f9fa; padding: 4px 8px; border-radius: 4px; color: #667eea; font-weight: 600;">' + data + '</code>';
                    }
                }
            ],
            "order": [[0, "desc"]],
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "pageLength": 25,
            "footerCallback": function(row, data, start, end, display) {
                var api = this.api();
                var totalIn = 0;
                var totalOut = 0;

                // Calculate totals
                data.forEach(function(item) {
                    if (item.qty_in !== '-') {
                        totalIn += parseFloat(item.qty_in.replace(/,/g, ''));
                    }
                    if (item.qty_out !== '-') {
                        totalOut += parseFloat(item.qty_out.replace(/,/g, ''));
                    }
                });

                // Update footer
                $('#total-in').html(totalIn.toFixed(2));
                $('#total-out').html(totalOut.toFixed(2));
            },
            "dom": '<"row mb-2"<"col-sm-6"B><"col-sm-6"f>><"row"<"col-sm-6"l><"col-sm-6">>rt<"row"<"col-sm-5"i><"col-sm-7"p>>',
            "buttons": [
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    className: 'btn btn-success',
                    exportOptions: {
                        columns: ':visible'
                    },
                    title: 'Inventory Movement Report - ' + new Date().toLocaleDateString()
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    className: 'btn btn-danger',
                    exportOptions: {
                        columns: ':visible'
                    },
                    title: 'Inventory Movement Report',
                    customize: function(doc) {
                        doc.styles.title = {
                            fontSize: 18,
                            bold: true,
                            alignment: 'center'
                        };
                    }
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> Print',
                    className: 'btn btn-info',
                    exportOptions: {
                        columns: ':visible'
                    },
                    title: 'Inventory Movement Report'
                },
                {
                    extend: 'copy',
                    text: '<i class="fa fa-copy"></i> Copy',
                    className: 'btn btn-secondary',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ]
        });
    }

    // Initial load
    loadInventoryMovements();

    // Filter button click
    $('.filter-btn').on('click', function() {
        loadInventoryMovements();
    });

    // Reset button click
    $('.reset-btn').on('click', function() {
        $('select[name="product_id"]').val('all');
        $('select[name="warehouse_id"]').val('all');
        $('select[name="movement_type"]').val('all');
        $('input[name="start_date"]').val('');
        $('input[name="end_date"]').val('');
        $('.daterangepicker-field').val('');
        $('.selectpicker').selectpicker('refresh');
        loadInventoryMovements();
    });

    // Refresh selectpicker
    $('.selectpicker').selectpicker();
</script>
@endpush

