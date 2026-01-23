@extends('backend.layout.main')

@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<style>
  .dt-details { background:#fafbfc; }
  .status-badge{font-size:12px;padding:.25rem .5rem;border-radius:999px}
  .status-1{background:#fff3cd;color:#856404;border:1px solid #ffeeba}      /* Pending */
  .status-2{background:#cce5ff;color:#004085;border:1px solid #b8daff}      /* In Transit */
  .status-3{background:#d4edda;color:#155724;border:1px solid #c3e6cb}      /* Delivered */
  .status-4{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}      /* Returned */
  .status-5{background:#eee;color:#6c757d;border:1px solid #ddd}            /* Cancelled */
  .dt-control{cursor:pointer}
  .table-sm td, .table-sm th{padding:.5rem .6rem}
  .icon-btn{border:none;background:transparent;padding:4px 6px}
  .icon-btn .fa{font-size:16px}

  /* Export buttons look like the screenshot: square, colored, icon-only */
  .dt-export .btn { 
    width:40px;height:40px;display:inline-flex;align-items:center;justify-content:center;
    border:none;border-radius:.5rem;box-shadow:0 1px 2px rgba(0,0,0,.08);
  }
  .dt-export .btn i{ font-size:16px }
  .btn-copy  { background:#6c757d; color:#fff; }   /* gray */
  .btn-excel { background:#28a745; color:#fff; }   /* green */
  .btn-csv   { background:#17a2b8; color:#fff; }   /* teal */
  .btn-pdf   { background:#dc3545; color:#fff; }   /* red */
  .btn-print { background:#0d6efd; color:#fff; }   /* blue */
  .btn-colv  { background:#ffc107; color:#212529;} /* yellow */
</style>

<div class="container-fluid">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Shipments</h5>
      <a href="{{ route('shipment.create') }}" class="btn btn-primary">
        <i class="fa fa-plus"></i> New Shipment
      </a>
    </div>

    <div class="card-body">
      <div class="table-responsive">
        <table id="shipments-table" class="table table-striped table-hover table-sm align-middle" style="width:100%">
          <thead>
          <tr>
            <th style="width:36px;"></th>
            <th>ID</th>
            <th>Reference</th>
            <th>Buyer</th>
            <th>From → To</th>
            <th>Status</th>
            <th>Items(Qty)</th>
            <th>Grand Total</th>
            <th>Created</th>
            <th style="width:110px;">Action</th>
          </tr>
          </thead>
        </table>
      </div>

      <!-- export buttons container (bottom-right like your picture) -->
      <div class="d-flex justify-content-end gap-2 mt-3 dt-export" id="exportBtns"></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables + Buttons (Bootstrap 5 bundle) -->
<link rel="stylesheet" href="https://cdn.datatables.net/v/bs5/dt-1.13.8/b-2.4.2/b-html5-2.4.2/b-print-2.4.2/r-2.5.0/datatables.min.css">
<script src="https://cdn.datatables.net/v/bs5/dt-1.13.8/b-2.4.2/b-html5-2.4.2/b-print-2.4.2/r-2.5.0/datatables.min.js"></script>

<!-- Export deps -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
  function statusBadge(s) {
    const map = {
      1: {t:'Pending', cls:'status-1'},
      2: {t:'In Transit', cls:'status-2'},
      3: {t:'Delivered', cls:'status-3'},
      4: {t:'Returned', cls:'status-4'},
      5: {t:'Cancelled', cls:'status-5'},
    };
    const obj = map[s] || {t:'—', cls:'status-5'};
    return `<span class="status-badge ${obj.cls}">${obj.t}</span>`;
  }

  function currency(n) {
    if (n == null) return '0.00';
    const v = parseFloat(n) || 0;
    return v.toLocaleString(undefined,{minimumFractionDigits:2, maximumFractionDigits:2});
  }

  function formatDetails(row) {
    const d = row;
    let fromDetails = `<div><strong>From:</strong> ${d.from || '—'}</div>`;
    if (d.ship_from_dock_hours && d.ship_from_dock_hours !== '—') {
      fromDetails += `<div class="mt-1"><strong>Dock Hours:</strong> ${d.ship_from_dock_hours}</div>`;
    }
    if (d.ship_from_lunch_hour && d.ship_from_lunch_hour !== '—') {
      fromDetails += `<div class="mt-1"><strong>Lunch Hour:</strong> ${d.ship_from_lunch_hour}</div>`;
    }
    if (d.ship_from_pickup_delivery_instructions && d.ship_from_pickup_delivery_instructions !== '—') {
      fromDetails += `<div class="mt-1"><strong>Pick up / Delivery Instructions:</strong> ${d.ship_from_pickup_delivery_instructions}</div>`;
    }
    if (d.ship_from_appointment && d.ship_from_appointment !== '—') {
      fromDetails += `<div class="mt-1"><strong>Appointment:</strong> ${d.ship_from_appointment}</div>`;
    }
    if (d.ship_from_accessorial && d.ship_from_accessorial !== '—') {
      fromDetails += `<div class="mt-1"><strong>Accessorial:</strong> ${d.ship_from_accessorial}</div>`;
    }
    
    let toDetails = `<div><strong>To:</strong> ${d.to || '—'}</div>`;
    if (d.ship_to_dock_hours && d.ship_to_dock_hours !== '—') {
      toDetails += `<div class="mt-1"><strong>Dock Hours:</strong> ${d.ship_to_dock_hours}</div>`;
    }
    if (d.ship_to_lunch_hour && d.ship_to_lunch_hour !== '—') {
      toDetails += `<div class="mt-1"><strong>Lunch Hour:</strong> ${d.ship_to_lunch_hour}</div>`;
    }
    if (d.ship_to_pickup_delivery_instructions && d.ship_to_pickup_delivery_instructions !== '—') {
      toDetails += `<div class="mt-1"><strong>Pick up / Delivery Instructions:</strong> ${d.ship_to_pickup_delivery_instructions}</div>`;
    }
    if (d.ship_to_appointment && d.ship_to_appointment !== '—') {
      toDetails += `<div class="mt-1"><strong>Appointment:</strong> ${d.ship_to_appointment}</div>`;
    }
    if (d.ship_to_accessorial && d.ship_to_accessorial !== '—') {
      toDetails += `<div class="mt-1"><strong>Accessorial:</strong> ${d.ship_to_accessorial}</div>`;
    }
    
    const addrFrom = fromDetails;
    const addrTo = toDetails;

    let pkgRows = '';
    (d.packages||[]).forEach((p, i) => {
      const dim = [p.length, p.width, p.height].filter(Boolean).join(' x ');
      const dimUnit = p.dim_unit || 'cm';
      const wt = `${p.weight||0} ${p.weight_unit||'kg'}`;
      pkgRows += `
        <tr>
          <td>${i+1}</td>
          <td>${p.packaging || '—'}</td>
          <td>${p.qty || 1}</td>
          <td>${p.package_class || '—'}</td>
          <td>${p.package_nmfc || '—'}</td>
          <td>${p.commodity_name || '—'}</td>
          <td>${wt}</td>
          <td>${dim ? (dim + ' ' + dimUnit) : '—'}</td>
          <td class="text-end">${currency(p.declared_value)}</td>
        </tr>`;
    });
    if(!pkgRows) pkgRows = `<tr><td colspan="9" class="text-muted">No packages</td></tr>`;

    const packagesTable = `
      <div class="mt-2">
        <div class="fw-bold mb-1">Packages</div>
        <table class="table table-bordered table-sm mb-0">
          <thead><tr>
            <th>#</th><th>Packaging</th><th>Qty</th><th>Class</th><th>NMFC</th><th>Commodity</th><th>Weight</th><th>Dimensions</th><th class="text-end">Declared</th>
          </tr></thead>
          <tbody>${pkgRows}</tbody>
        </table>
      </div>`;

    let itemRows = '';
    (d.items||[]).forEach((it, i) => {
      itemRows += `
        <tr>
          <td>${i+1}</td>
          <td>${it.product_code || '—'}</td>
          <td>${it.qty || 0}</td>
          <td class="text-end">${it.product_unit}</td>
          <td class="text-end">${currency(it.net_unit_cost)}</td>
          <td class="text-end">${currency(it.subtotal)}</td>
        </tr>`;
    });
    if(!itemRows) itemRows = `<tr><td colspan="5" class="text-muted">No items</td></tr>`;

    const itemsTable = `
      <div class="mt-3">
        <div class="fw-bold mb-1">Items</div>
        <table class="table table-bordered table-sm mb-0">
          <thead><tr>
            <th>#</th>
            <th>Code</th>
            <th>Qty</th>
            <th class="text-end">Unit</th>
            <th class="text-end">Unit Cost</th>
            <th class="text-end">Subtotal</th>
          </tr></thead>
          <tbody>${itemRows}</tbody>
        </table>
      </div>`;

    const totals = `
      <div class="mt-3">
        <div class="fw-bold mb-1">Totals</div>
        <div class="row g-2">
          <div class="col-sm-3"><div class="small text-muted">Items (Qty)</div><div>${d.totals.items} (${d.totals.qty})</div></div>
          <div class="col-sm-3"><div class="small text-muted">Tax</div><div>${currency(d.totals.tax)}</div></div>
          <div class="col-sm-3"><div class="small text-muted">Shipping</div><div>${currency(d.totals.shipping)}</div></div>
          <div class="col-sm-3"><div class="small text-muted">Grand Total</div><div class="fw-bold">${currency(d.totals.grand_total)}</div></div>
        </div>
      </div>`;

    const tracking = d.tracking ? `<span class="badge bg-info text-dark">Tracking: ${d.tracking}</span>` : '';

    return `
      <div class="dt-details p-3">
        <div class="row">
          <div class="col-md-6">${addrFrom}</div>
          <div class="col-md-6">${addrTo}</div>
        </div>
        ${tracking ? `<div class="mt-2">${tracking}</div>`:''}
        ${packagesTable}
        ${itemsTable}
        ${totals}
      </div>`;
  }

  $(document).ready(function(){
    const table = $('#shipments-table').DataTable({
      ajax: '{{ route('shipmentDatatable') }}',
      processing: true,
      searching: true,
      paging: true,
      pageLength: 25,
      order: [[1, 'desc']],
      responsive: true,

      /* place default length + search on top, table, then info + paging
         We’ll put export buttons in our custom #exportBtns container below the table. */
      dom: "<'row g-2 align-items-center mb-2'<'col-sm-6'l><'col-sm-6 text-sm-end'f>>" +
           "tr" +
           "<'row align-items-center mt-2'<'col-sm-6'i><'col-sm-6 text-sm-end'p>>",

      columns: [
        {
          data: null,
          className: 'dt-control text-center',
          orderable: false,
          defaultContent: '<i class="fa fa-chevron-right"></i>',
        },
        { data: 'id' },
        { data: 'reference_no', render: (d)=> d || '—' },
        { data: 'buyer', render: (d)=> d || '—' },
        { data: null, render: (row)=> (row.from || '—') + ' → ' + (row.to || '—') },
        { data: 'status', render: (d)=> statusBadge(d) },
        { data: null, render: (row)=> `${row.totals.items||0} (${row.totals.qty||0})` },
        { data: 'totals.grand_total', className:'text-end', render: (d)=> currency(d) },
        { 
          data: 'created_at', 
          className:'text-nowrap editable-date',
          render: function(d, type, row) {
            if (type === 'display') {
              const dateStr = d || '—';
              return `<span class="editable-date-value" data-id="${row.id}" data-date="${d}">${dateStr}</span> <i class="fa fa-edit text-muted small" style="cursor:pointer;margin-left:5px;" title="Click to edit"></i>`;
            }
            return d;
          }
        },
        {
          data: null,
          orderable: false,
          className: 'text-nowrap',
          render: function(row) {
            const viewUrl = `{{ url('/shipment') }}/${row.id}`;
            const editUrl = `{{ url('/shipment') }}/${row.id}/edit`;
            const delUrl  = `{{ url('/shipment') }}/${row.id}`;
            return `
              <a href="${viewUrl}" class="icon-btn text-primary" title="View"><i class="fa fa-eye"></i></a>
              <a href="${editUrl}" class="icon-btn text-warning" title="Edit"><i class="fa fa-pencil"></i></a>
              <form action="${delUrl}" method="POST" class="d-inline js-del-form">
                @csrf
                @method('DELETE')
                <button type="button" class="icon-btn text-danger js-del-btn" title="Delete">
                  <i class="fa fa-trash"></i>
                </button>
              </form>`;
          }
        }
      ],

      /* Buttons config (icon-only, colored) */
      buttons: [
        {
          extend: 'copyHtml5',
          text: '<i class="fa fa-copy"></i>',
          className: 'btn btn-copy',
          exportOptions: { columns: ':visible:not(:first-child):not(:last-child)' }
        },
        {
          extend: 'excelHtml5',
          text: '<i class="fa fa-file-excel-o"></i>',
          className: 'btn btn-excel',
          title: 'Shipments',
          exportOptions: { columns: ':visible:not(:first-child):not(:last-child)' }
        },
        {
          extend: 'csvHtml5',
          text: '<i class="fa fa-file-text-o"></i>',
          className: 'btn btn-csv',
          title: 'Shipments',
          fieldSeparator: ',',
          exportOptions: { columns: ':visible:not(:first-child):not(:last-child)' }
        },
        {
          extend: 'pdfHtml5',
          text: '<i class="fa fa-file-pdf-o"></i>',
          className: 'btn btn-pdf',
          title: 'Shipments',
          orientation: 'landscape',
          pageSize: 'A4',
          exportOptions: { columns: ':visible:not(:first-child):not(:last-child)' }
        },
        {
          extend: 'print',
          text: '<i class="fa fa-print"></i>',
          className: 'btn btn-print',
          title: 'Shipments',
          exportOptions: { columns: ':visible:not(:first-child):not(:last-child)' }
        },
        {
          extend: 'colvis',
          text: '<i class="fa fa-columns"></i>',
          className: 'btn btn-colv'
        }
      ],
      initComplete: function () {
        /* move buttons into our custom container (bottom-right) */
        table.buttons().container().appendTo('#exportBtns');
      }
    });

    // Expand/Collapse detail rows
    $('#shipments-table tbody').on('click', 'td.dt-control', function () {
      const tr = $(this).closest('tr');
      const row = table.row(tr);

      if (row.child.isShown()) {
        row.child.hide();
        tr.find('.dt-control i').removeClass('fa-chevron-down').addClass('fa-chevron-right');
      } else {
        row.child(formatDetails(row.data())).show();
        tr.find('.dt-control i').removeClass('fa-chevron-right').addClass('fa-chevron-down');
      }
    });

    // Inline date editing
    $(document).on('click', '.editable-date-value, .editable-date .fa-edit', function(e){
      e.stopPropagation();
      const $span = $(this).closest('.editable-date').find('.editable-date-value');
      const shipmentId = $span.data('id');
      const currentDate = $span.data('date') || '';
      
      // Convert Y-m-d H:i to datetime-local format
      let dateValue = '';
      if (currentDate) {
        const date = new Date(currentDate.replace(' ', 'T'));
        if (!isNaN(date.getTime())) {
          dateValue = date.toISOString().slice(0, 16);
        }
      }
      
      const $input = $('<input>', {
        type: 'datetime-local',
        class: 'form-control form-control-sm',
        value: dateValue,
        style: 'width:180px;display:inline-block;'
      });
      
      const $saveBtn = $('<button>', {
        type: 'button',
        class: 'btn btn-sm btn-success ms-1',
        html: '<i class="fa fa-check"></i>',
        title: 'Save'
      });
      
      const $cancelBtn = $('<button>', {
        type: 'button',
        class: 'btn btn-sm btn-secondary ms-1',
        html: '<i class="fa fa-times"></i>',
        title: 'Cancel'
      });
      
      $span.replaceWith($input);
      $input.after($saveBtn).after($cancelBtn);
      $input.focus();
      
      const saveDate = function(){
        const newDate = $input.val();
        if (!newDate) {
          Swal.fire({ icon: 'error', title: 'Date is required', timer: 2000 });
          return;
        }
        
        $.ajax({
          url: `{{ url('/shipment') }}/${shipmentId}/update-date`,
          method: 'POST',
          data: {
            _token: '{{ csrf_token() }}',
            created_at: newDate
          },
          success: function(response){
            const formattedDate = response.created_at || newDate;
            
            const $newSpan = $('<span>', {
              class: 'editable-date-value',
              'data-id': shipmentId,
              'data-date': formattedDate,
              text: formattedDate
            });
            const $icon = $('<i>', {
              class: 'fa fa-edit text-muted small',
              style: 'cursor:pointer;margin-left:5px;',
              title: 'Click to edit'
            });
            
            $input.remove();
            $saveBtn.remove();
            $cancelBtn.remove();
            $input.closest('td').html($newSpan).append(' ').append($icon);
            
            table.ajax.reload(null, false);
            Swal.fire({ icon: 'success', title: 'Date updated', timer: 1500 });
          },
          error: function(xhr){
            Swal.fire({ icon: 'error', title: 'Failed to update date', text: xhr.responseJSON?.message || 'Error' });
          }
        });
      };
      
      $saveBtn.on('click', saveDate);
      $cancelBtn.on('click', function(){
        const $newSpan = $('<span>', {
          class: 'editable-date-value',
          'data-id': shipmentId,
          'data-date': currentDate,
          text: currentDate || '—'
        });
        const $icon = $('<i>', {
          class: 'fa fa-edit text-muted small',
          style: 'cursor:pointer;margin-left:5px;',
          title: 'Click to edit'
        });
        $input.remove();
        $saveBtn.remove();
        $cancelBtn.remove();
        $input.closest('td').html($newSpan).append(' ').append($icon);
      });
      
      $input.on('keypress', function(e){
        if (e.which === 13) saveDate();
        if (e.which === 27) $cancelBtn.click();
      });
    });

    // SweetAlert delete confirmation
    $('#shipments-table tbody').on('click', '.js-del-btn', function(){
      const form = $(this).closest('.js-del-form');
      Swal.fire({
        title: 'Delete this shipment?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
      }).then((res)=>{
        if(res.isConfirmed){
          form.submit();
        }
      });
    });
  });
</script>
@endpush
