@extends('backend.layout.main')

@section('content')

<x-success-message key="message" />
<x-error-message key="not_permitted" />

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">WooCommerce API Settings</h5>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addApiModal">
                <i class="fa fa-plus"></i> Add API Connection
            </button>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Platform</th>
                            <th>Website URL</th>
                            <th>Consumer Key</th>
                            <th>Status</th>
                            <th>Last Sync</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settings as $setting)
                            <tr>
                                <td>{{ $setting->platform_name }}</td>
                                <td><a href="{{ $setting->website_url }}" target="_blank">{{ $setting->website_url }}</a></td>
                                <td><code>{{ substr($setting->consumer_key, 0, 20) }}...</code></td>
                                <td>
                                    @if($setting->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $setting->last_sync_at ? $setting->last_sync_at->format('Y-m-d H:i:s') : 'Never' }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm" onclick="testConnection({{ $setting->id }})" title="Test Connection">
                                            <i class="fa fa-plug"></i>
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm" onclick="syncOrders({{ $setting->id }})" title="Sync Orders">
                                            <i class="fa fa-refresh"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="editApi({{ $setting->id }})" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No API settings found. Add your first API connection.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit API Modal -->
<div class="modal fade" id="addApiModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add API Connection</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="apiForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Platform Name <span class="text-danger">*</span></label>
                        <input type="text" name="platform_name" class="form-control" value="WooCommerce" required>
                    </div>
                    <div class="form-group">
                        <label>Website URL <span class="text-danger">*</span></label>
                        <input type="url" name="website_url" class="form-control" placeholder="https://example.com" required>
                        <small class="text-muted">Enter your WooCommerce store URL (without trailing slash)</small>
                    </div>
                    <div class="form-group">
                        <label>Consumer Key <span class="text-danger">*</span></label>
                        <input type="text" name="consumer_key" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Consumer Secret <span class="text-danger">*</span></label>
                        <input type="text" name="consumer_secret" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Sync Interval (minutes)</label>
                        <input type="number" name="sync_interval" class="form-control" value="60" min="1">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group" id="activeField" style="display:none;">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="is_active" value="1" checked> Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let editingId = null;

    function testConnection(id) {
        Swal.fire({
            title: 'Testing Connection...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `/woocommerce/api-settings/${id}/test`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Connection Failed',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to test connection'
                });
            }
        });
    }

    function syncOrders(id) {
        Swal.fire({
            title: 'Sync Orders',
            html: `
                <div class="text-left">
                    <p>Select date range to fetch orders (optional):</p>
                    <div class="form-group">
                        <label>From Date:</label>
                        <input type="date" id="syncAfterDate" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>To Date:</label>
                        <input type="date" id="syncBeforeDate" class="form-control">
                    </div>
                    <small class="text-muted">Leave empty to fetch all new orders</small>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Sync Orders',
            cancelButtonText: 'Cancel',
            didOpen: () => {
                // Set today as default "to" date
                document.getElementById('syncBeforeDate').valueAsDate = new Date();
            },
            preConfirm: () => {
                return {
                    after_date: document.getElementById('syncAfterDate').value || null,
                    before_date: document.getElementById('syncBeforeDate').value || null
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Syncing Orders...',
                    text: 'This may take a few moments',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `/woocommerce/api-settings/${id}/sync`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        after_date: result.value.after_date,
                        before_date: result.value.before_date
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                html: response.message + '<br><small>New: ' + response.total_synced + ', Skipped: ' + (response.total_skipped || 0) + '</small>'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Sync Failed',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Failed to sync orders'
                        });
                    }
                });
            }
        });
    }

    function editApi(id) {
        // Fetch setting data and populate form
        // For now, just show the modal with edit mode
        editingId = id;
        $('#modalTitle').text('Edit API Connection');
        $('#apiForm').attr('action', `/woocommerce/api-settings/${id}`);
        $('#methodField').html('<input type="hidden" name="_method" value="PUT">');
        $('#activeField').show();
        $('#addApiModal').modal('show');
    }

    $('#addApiModal').on('hidden.bs.modal', function() {
        editingId = null;
        $('#modalTitle').text('Add API Connection');
        $('#apiForm').attr('action', '{{ route("woocommerce.api-settings.store") }}');
        $('#methodField').empty();
        $('#activeField').hide();
        $('#apiForm')[0].reset();
    });

    $('#apiForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const url = $(this).attr('action');
        const method = $(this).find('input[name="_method"]').val() || 'POST';

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'API settings saved successfully'
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMsg = 'Failed to save settings';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMsg
                });
            }
        });
    });
</script>
@endpush

@endsection

