@extends('backend.layout.main')

@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">WooCommerce Orders</h5>
            <div>
                <a href="{{ route('woocommerce.api-settings') }}" class="btn btn-info">
                    <i class="fa fa-cog"></i> API Settings
                </a>
                <a href="{{ route('woocommerce.orders.create') }}" class="btn btn-primary">
                    <i class="fa fa-plus"></i> New Order
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="orders-table" class="table table-striped table-hover table-sm" style="width:100%">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Platform</th>
                            <th>Customer</th>
                            <th>Images</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Currency</th>
                            <th>Order Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Product Image</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" alt="Product Image" style="max-width:100%;max-height:600px;">
            </div>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        var table = $('#orders-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("woocommerce.orders.datatable") }}',
                type: 'GET'
            },
            columns: [
                { data: 'order_number', name: 'order_number' },
                { data: 'platform_name', name: 'platform_name' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'images', name: 'images', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status', orderable: false },
                { 
                    data: 'total', 
                    name: 'total',
                    render: function(data, type, row) {
                        return parseFloat(data).toFixed(2);
                    }
                },
                { data: 'currency', name: 'currency' },
                { 
                    data: 'order_date', 
                    name: 'order_date',
                    render: function(data) {
                        return data || '—';
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[7, 'desc']],
            pageLength: 25,
            language: {
                processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
            }
        });
    });

    function showImageModal(imageUrl) {
        $('#modalImage').attr('src', imageUrl);
        $('#imageModal').modal('show');
    }

    function deleteOrder(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/woocommerce/orders/${id}`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('Deleted!', response.message, 'success');
                        $('#orders-table').DataTable().ajax.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to delete order', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush

@endsection

