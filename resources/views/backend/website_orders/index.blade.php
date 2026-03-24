@extends('backend.layout.main')
@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<section class="forms">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h4 class="mb-0">Website Orders</h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Open API: server se seedha <code>GET</code> — filters query string par jaate hain
                            (<code>status</code>, <code>dateFrom</code>, <code>dateTo</code>). URL badalni ho to <code>.env</code> mein <code>WEBSITE_ORDERS_API_URL</code>.
                        </p>
                        <div class="row mb-3">
                            <div class="col-md-3 form-group">
                                <label>Status</label>
                                <div class="input-group">
                                    <select id="filter-status-preset" class="form-control" style="max-width:130px">
                                        <option value="">Custom</option>
                                        <option value="DELIVERED">DELIVERED</option>
                                        <option value="SHIPPED">SHIPPED</option>
                                        <option value="PLACED">PLACED</option>
                                        <option value="PENDING_APPROVAL">PENDING_APPROVAL</option>
                                        <option value="SHIPPED,DELIVERED">SHIPPED,DELIVERED</option>
                                    </select>
                                    <input type="text" id="filter-status" class="form-control" placeholder="e.g. SHIPPED,DELIVERED">
                                </div>
                            </div>
                            <div class="col-md-2 form-group">
                                <label>Date from</label>
                                <input type="date" id="filter-date-from" class="form-control">
                            </div>
                            <div class="col-md-2 form-group">
                                <label>Date to</label>
                                <input type="date" id="filter-date-to" class="form-control">
                            </div>
                            <div class="col-md-2 form-group d-flex align-items-end">
                                <button type="button" class="btn btn-primary" id="btn-load-orders">
                                    <i class="fa fa-refresh"></i> Load orders
                                </button>
                            </div>
                        </div>
                        <div id="api-error" class="alert alert-danger d-none" role="alert"></div>
                        <div class="table-responsive">
                            <table id="website-orders-table" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Order #</th>
                                        <th>User ID</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Tax</th>
                                        <th>Shipping</th>
                                        <th>Ship method</th>
                                        <th>Payment</th>
                                        <th>City</th>
                                        <th>Address</th>
                                        <th>Items</th>
                                        <th>Created</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="order-detail-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Order detail</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="order-detail-body"></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    var fetchUrl = @json(route('website-orders.fetch'));
    var ordersById = {};

    function esc(s) {
        if (s === null || s === undefined) return '';
        return $('<div/>').text(String(s)).html();
    }

    function escAttr(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;');
    }

    function fmtMoney(v) {
        if (v === null || v === undefined || v === '') return '—';
        var n = parseFloat(v);
        return isNaN(n) ? esc(v) : n.toFixed(2);
    }

    function fmtDate(iso) {
        if (!iso) return '—';
        var d = new Date(iso);
        return isNaN(d.getTime()) ? esc(iso) : d.toLocaleString();
    }

    function statusBadge(status) {
        var s = esc(status || '');
        var cls = 'badge-secondary';
        if (status === 'DELIVERED') cls = 'badge-success';
        else if (status === 'SHIPPED') cls = 'badge-info';
        else if (status === 'PLACED' || status === 'PENDING' || status === 'PENDING_APPROVAL') cls = 'badge-warning';
        else if (status === 'PROCESSING' || status === 'CONFIRMED') cls = 'badge-primary';
        else if (status === 'CANCELLED' || status === 'CANCELED') cls = 'badge-danger';
        return '<span class="badge ' + cls + '">' + s + '</span>';
    }

    function specsJsonHtml(specs) {
        if (!specs || typeof specs !== 'object') return '';
        var keys = Object.keys(specs);
        if (!keys.length) return '';
        var rows = keys.map(function (k) {
            var v = specs[k];
            if (v !== null && typeof v === 'object') v = JSON.stringify(v);
            return '<tr><td class="text-muted small">' + esc(k) + '</td><td class="small">' + esc(v) + '</td></tr>';
        }).join('');
        return '<table class="table table-sm table-borderless mb-0 mt-1"><tbody>' + rows + '</tbody></table>';
    }

    function productThumbCell(url, name) {
        if (!url) return '<span class="text-muted">—</span>';
        return '<img src="' + escAttr(url) + '" alt="' + escAttr(name || '') + '" class="rounded border" style="width:56px;height:56px;object-fit:cover">';
    }

    function truncate(str, n) {
        str = str || '';
        if (str.length <= n) return esc(str);
        return esc(str.substring(0, n)) + '…';
    }

    function buildDetailHtml(o) {
        var html = '<div class="row mb-3">';
        html += '<div class="col-md-6"><strong>ID:</strong> ' + esc(o.id) + '</div>';
        html += '<div class="col-md-6"><strong>Order # (orderNumber):</strong> ' + esc(o.orderNumber != null && o.orderNumber !== '' ? o.orderNumber : '—') + '</div>';
        html += '<div class="col-md-6"><strong>Status:</strong> ' + statusBadge(o.status) + '</div>';
        html += '<div class="col-md-6"><strong>User ID (userId):</strong> ' + esc(o.userId || '—') + '</div>';
        html += '<div class="col-md-6"><strong>Total:</strong> ' + fmtMoney(o.totalAmount) + '</div>';
        html += '<div class="col-md-6"><strong>Tax:</strong> ' + fmtMoney(o.taxAmount) + '</div>';
        html += '<div class="col-md-6"><strong>Shipping cost:</strong> ' + fmtMoney(o.shippingCost) + '</div>';
        html += '<div class="col-md-6"><strong>Shipping method:</strong> ' + esc(o.shippingMethod || '—') + '</div>';
        html += '<div class="col-md-6"><strong>Payment:</strong> ' + esc(o.paymentMethod || '—') + '</div>';
        html += '<div class="col-md-6"><strong>City:</strong> ' + esc(o.customerCity || '—') + '</div>';
        html += '<div class="col-12 mt-2"><strong>Shipping address:</strong><br>' + esc(o.shippingAddress || '—') + '</div>';
        html += '<div class="col-12 mt-2"><strong>Notes:</strong> ' + esc(o.notes || '—') + '</div>';
        html += '<div class="col-md-6 mt-2"><strong>Created:</strong> ' + fmtDate(o.createdAt) + '</div>';
        html += '<div class="col-md-6 mt-2"><strong>Updated:</strong> ' + fmtDate(o.updatedAt) + '</div>';
        html += '</div>';

        html += '<h6>Line items (items[])</h6>';
        html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
        html += '<thead><tr><th>Image</th><th>Product</th><th>Line ID</th><th>Product ID</th><th>Variant ID</th><th>Item #</th><th>Brand</th><th>Stock</th><th>UOM</th><th>Qty</th><th>Unit price</th></tr></thead><tbody>';
        var items = o.items || [];
        if (!items.length) {
            html += '<tr><td colspan="11" class="text-center text-muted">No line items</td></tr>';
        } else {
            items.forEach(function (line) {
                var p = line.product || {};
                var pname = p.name || line.productId || '—';
                html += '<tr>';
                html += '<td class="align-middle">' + productThumbCell(p.imageUrl, pname) + '</td>';
                html += '<td class="align-middle"><div><strong>' + esc(pname) + '</strong></div>';
                if (p.slug) html += '<div class="small text-muted">' + esc(p.slug) + '</div>';
                if (p.shortDescription) html += '<div class="small mt-1">' + truncate(p.shortDescription, 120) + '</div>';
                if (p.specsJson) html += '<div class="mt-1"><strong class="small">specsJson</strong>' + specsJsonHtml(p.specsJson) + '</div>';
                html += '</td>';
                html += '<td class="align-middle small"><code>' + esc(line.id || '—') + '</code></td>';
                html += '<td class="align-middle small"><code>' + esc(line.productId || p.id || '—') + '</code></td>';
                html += '<td class="align-middle small">' + (line.variantId ? '<code>' + esc(line.variantId) + '</code>' : '—') + '</td>';
                html += '<td class="align-middle">' + esc(p.itemNumber || '—') + '</td>';
                html += '<td class="align-middle">' + esc(p.brand || '—') + '</td>';
                html += '<td class="align-middle small">' + esc(p.stockStatus || '—') + '</td>';
                html += '<td class="align-middle">' + esc(line.uom || '—') + '</td>';
                html += '<td class="align-middle">' + esc(line.quantity) + '</td>';
                html += '<td class="align-middle">' + fmtMoney(line.unitPrice) + '</td>';
                html += '</tr>';
            });
        }
        html += '</tbody></table></div>';
        return html;
    }

    var table = $('#website-orders-table').DataTable({
        order: [[12, 'desc']],
        pageLength: 25,
        columns: [
            { data: 'id' },
            { data: 'orderNumber' },
            { data: 'userId' },
            { data: 'status', orderable: false },
            { data: 'totalAmount' },
            { data: 'taxAmount' },
            { data: 'shippingCost' },
            { data: 'shippingMethod' },
            { data: 'paymentMethod' },
            { data: 'customerCity' },
            { data: 'shippingAddress', orderable: false },
            { data: 'itemsCount', searchable: false },
            { data: 'createdAt' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        columnDefs: [
            {
                targets: 0,
                render: function (data, type, row) {
                    return '<span title="' + escAttr(row.rawId) + '">' + esc(data) + '</span>';
                }
            },
            {
                targets: 3,
                render: function (data) {
                    return statusBadge(data);
                }
            },
            { targets: [4, 5, 6], render: function (data) { return fmtMoney(data); } },
            { targets: 10, render: function (data) { return truncate(data, 48); } },
            { targets: 12, render: function (data) { return fmtDate(data); } },
            {
                targets: 13,
                render: function (data, type, row) {
                    return '<button type="button" class="btn btn-sm btn-info btn-view-order" data-id="' + escAttr(row.rawId) + '">View</button>';
                }
            }
        ]
    });

    function loadOrders() {
        $('#api-error').addClass('d-none').text('');
        var params = {};
        var st = $('#filter-status').val();
        var df = $('#filter-date-from').val();
        var dt = $('#filter-date-to').val();
        if (st) params.status = st;
        if (df) params.dateFrom = df;
        if (dt) params.dateTo = dt;

        $.getJSON(fetchUrl, params)
            .done(function (res) {
                if (!res.success) {
                    $('#api-error').removeClass('d-none').text(res.message || 'Unknown error');
                    return;
                }
                ordersById = {};
                table.clear();
                (res.orders || []).forEach(function (o) {
                    ordersById[o.id] = o;
                    var items = o.items || [];
                    table.row.add({
                        id: (o.id || '').substring(0, 12) + '…',
                        rawId: o.id,
                        orderNumber: (o.orderNumber != null && o.orderNumber !== '') ? o.orderNumber : '—',
                        userId: o.userId || '—',
                        status: o.status,
                        totalAmount: o.totalAmount,
                        taxAmount: o.taxAmount,
                        shippingCost: o.shippingCost,
                        shippingMethod: o.shippingMethod || '—',
                        paymentMethod: o.paymentMethod || '—',
                        customerCity: o.customerCity || '—',
                        shippingAddress: o.shippingAddress || '—',
                        itemsCount: items.length,
                        createdAt: o.createdAt,
                        actions: ''
                    });
                });
                table.draw();
            })
            .fail(function (xhr) {
                var msg = 'Request failed';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                } else if (xhr.statusText) {
                    msg = xhr.statusText;
                }
                $('#api-error').removeClass('d-none').text(msg);
            });
    }

    $('#filter-status-preset').on('change', function () {
        var v = $(this).val();
        if (v) $('#filter-status').val(v);
    });

    $('#btn-load-orders').on('click', loadOrders);
    $(document).on('click', '.btn-view-order', function () {
        var id = $(this).data('id');
        var o = ordersById[id];
        if (!o) return;
        $('#order-detail-body').html(buildDetailHtml(o));
        $('#order-detail-modal').modal('show');
    });

    $(function () {
        loadOrders();
    });
})();
</script>
@endpush
