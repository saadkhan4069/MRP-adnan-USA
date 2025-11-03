<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Manage All Appointments - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css">
    <style>
        :root {
            --primary-color: #4285f4;
            --secondary-color: #34a853;
            --danger-color: #ea4335;
            --warning-color: #fbbc04;
            --dark-color: #5f6368;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Google Sans', Roboto, Arial, sans-serif;
        }

        .main-container {
            max-width: 1600px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color), #1a73e8);
            color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(66, 133, 244, 0.3);
            margin-bottom: 30px;
        }

        .page-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 500;
        }

        .page-header p {
            margin: 10px 0 0 0;
            opacity: 0.95;
        }

        .stats-row {
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0;
        }

        .stat-card .label {
            color: var(--dark-color);
            font-size: 0.95rem;
        }

        .stat-pending { color: var(--warning-color); }
        .stat-confirmed { color: var(--secondary-color); }
        .stat-cancelled { color: var(--danger-color); }
        .stat-completed { color: var(--primary-color); }

        .appointments-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            padding: 30px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #842029;
        }

        .status-completed {
            background-color: #cfe2ff;
            color: #084298;
        }

        .btn-action {
            padding: 5px 10px;
            font-size: 0.85rem;
            margin: 2px;
        }

        .table {
            margin-top: 20px;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-user-shield"></i> Appointment Management</h1>
                    <p>Admin Panel - View and manage all scheduled appointments</p>
                </div>
                <div>
                    <a href="<?php echo route('schedule.public'); ?>" class="btn btn-light">
                        <i class="fas fa-plus"></i> New Appointment
                    </a>
                    <a href="<?php echo url('/dashboard'); ?>" class="btn btn-outline-light">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row stats-row">
            <?php
            $stats = [
                'pending' => $appointments->where('status', 'pending')->count(),
                'confirmed' => $appointments->where('status', 'confirmed')->count(),
                'cancelled' => $appointments->where('status', 'cancelled')->count(),
                'completed' => $appointments->where('status', 'completed')->count()
            ];
            $total = $appointments->count();
            ?>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="icon stat-pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="number"><?php echo $stats['pending']; ?></div>
                    <div class="label">Pending</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="icon stat-confirmed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="number"><?php echo $stats['confirmed']; ?></div>
                    <div class="label">Confirmed</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="icon stat-cancelled">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="number"><?php echo $stats['cancelled']; ?></div>
                    <div class="label">Cancelled</div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="icon stat-completed">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="number"><?php echo $stats['completed']; ?></div>
                    <div class="label">Completed</div>
                </div>
            </div>
        </div>

        <div class="appointments-card">
            <!-- Filters -->
            <div class="filter-section">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Status Filter</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Date From</label>
                        <input type="date" class="form-control" id="dateFrom">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Date To</label>
                        <input type="date" class="form-control" id="dateTo">
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary w-100" id="applyFilters">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover" id="appointmentsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Time Slot</th>
                            <th>Patient Name</th>
                            <th>Contact</th>
                            <th>Department</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($appointments as $appointment): ?>
                        <tr>
                            <td>#<?php echo $appointment->id; ?></td>
                            <td><?php echo date('M d, Y', strtotime($appointment->appointment_date)); ?></td>
                            <td><i class="far fa-clock"></i> <?php echo $appointment->slot; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($appointment->customer->name ?? $appointment->first_name); ?></strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-id-card"></i> Customer ID: <?php echo $appointment->customer_id; ?>
                                </small>
                            </td>
                            <td>
                                <i class="fas fa-phone"></i> <?php echo htmlspecialchars($appointment->phone); ?><br>
                                <i class="fas fa-envelope"></i> <small><?php echo htmlspecialchars($appointment->email); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($appointment->department); ?></td>
                            <td>
                                <?php
                                $paymentIcons = ['cash' => 'money-bill', 'card' => 'credit-card', 'bank' => 'university'];
                                $icon = $paymentIcons[$appointment->payment_mode] ?? 'dollar-sign';
                                ?>
                                <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo ucfirst($appointment->payment_mode); ?>
                            </td>
                            <td>
                                <select class="form-select form-select-sm status-select" data-id="<?php echo $appointment->id; ?>">
                                    <option value="pending" <?php echo $appointment->status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $appointment->status === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="cancelled" <?php echo $appointment->status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="completed" <?php echo $appointment->status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info btn-action view-btn" 
                                        data-appointment='<?php echo json_encode($appointment); ?>'>
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning btn-action edit-btn" 
                                        data-id="<?php echo $appointment->id; ?>"
                                        data-date="<?php echo $appointment->appointment_date; ?>"
                                        data-time="<?php echo $appointment->appointment_time; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-action delete-btn" 
                                        data-id="<?php echo $appointment->id; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                <?php echo $appointments->links(); ?>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">
                        <i class="fas fa-info-circle"></i> Appointment Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="appointmentDetails">
                    <!-- Details will be populated here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit/Reschedule Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        <i class="fas fa-edit"></i> Edit Appointment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="edit_id">
                        
                        <div class="mb-3">
                            <label for="edit_date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="edit_date" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Time Slot *</label>
                            <div id="edit_slots" class="time-slots">
                                <p class="text-muted">Please select a date first</p>
                            </div>
                        </div>

                        <div id="editErrors" class="alert alert-danger" style="display:none;"></div>
                        <div id="editSuccess" class="alert alert-success" style="display:none;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmEdit">
                        <i class="fas fa-check"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            const table = $('#appointmentsTable').DataTable({
                order: [[1, 'desc']],
                pageLength: 15,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        className: 'btn btn-sm btn-primary'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-sm btn-success'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-sm btn-success'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-sm btn-info'
                    }
                ]
            });

            // Apply filters
            $('#applyFilters').click(function() {
                table.draw();
            });

            // Custom filter for DataTables
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                const status = $('#statusFilter').val();
                const dateFrom = $('#dateFrom').val();
                const dateTo = $('#dateTo').val();
                const appointmentDate = data[1]; // Date column

                // Status filter
                if (status && !data[7].includes(status)) {
                    return false;
                }

                // Date range filter (simplified)
                // You might need to parse and compare dates properly
                
                return true;
            });

            // View appointment details
            $('.view-btn').click(function() {
                const appointment = $(this).data('appointment');
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary"><i class="fas fa-calendar"></i> Appointment Information</h6>
                            <p><strong>Date:</strong> ${new Date(appointment.appointment_date).toLocaleDateString()}</p>
                            <p><strong>Time Slot:</strong> ${appointment.slot}</p>
                            <p><strong>Status:</strong> <span class="status-badge status-${appointment.status}">${appointment.status}</span></p>
                            <hr>
                            <h6 class="text-primary"><i class="fas fa-user"></i> Patient Information</h6>
                            <p><strong>First Name:</strong> ${appointment.first_name}</p>
                            <p><strong>Last Name:</strong> ${appointment.last_name}</p>
                            <p><strong>Phone:</strong> ${appointment.phone}</p>
                            <p><strong>Email:</strong> ${appointment.email}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary"><i class="fas fa-briefcase"></i> Additional Details</h6>
                            <p><strong>Type:</strong> ${appointment.customer_type}</p>
                            <p><strong>Department:</strong> ${appointment.department}</p>
                            <p><strong>Payment Mode:</strong> ${appointment.payment_mode}</p>
                            <p><strong>Insurance:</strong> ${appointment.insurance || 'N/A'}</p>
                            <hr>
                            <h6 class="text-primary"><i class="fas fa-info-circle"></i> System Information</h6>
                            <p><strong>Booking ID:</strong> #${appointment.id}</p>
                            <p><strong>Created:</strong> ${new Date(appointment.created_at).toLocaleString()}</p>
                        </div>
                    </div>
                    ${appointment.notes ? `<hr><p><strong>Notes:</strong> ${appointment.notes}</p>` : ''}
                `;
                $('#appointmentDetails').html(html);
                new bootstrap.Modal(document.getElementById('viewModal')).show();
            });

            // Change status
            $('.status-select').change(function() {
                const id = $(this).data('id');
                const status = $(this).val();
                
                if (!confirm(`Change status to "${status}"?`)) {
                    $(this).val($(this).data('original-value'));
                    return;
                }

                $.post(`<?php echo url('/schedule/update-status'); ?>/${id}`, { status: status })
                    .done(function(response) {
                        if (response.success) {
                            alert('Status updated successfully!');
                            location.reload();
                        }
                    })
                    .fail(function() {
                        alert('Error updating status');
                        location.reload();
                    });
            });

            // Store original values
            $('.status-select').each(function() {
                $(this).data('original-value', $(this).val());
            });

            // Edit appointment
            let selectedEditTime = null;

            $('.edit-btn').click(function() {
                const id = $(this).data('id');
                const date = $(this).data('date');
                
                $('#edit_id').val(id);
                $('#edit_date').val(date);
                selectedEditTime = null;
                
                new bootstrap.Modal(document.getElementById('editModal')).show();
            });

            $('#edit_date').change(function() {
                const date = $(this).val();
                loadEditSlots(date);
            });

            function loadEditSlots(date) {
                const container = $('#edit_slots');
                container.html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading slots...</p>');
                
                $.post('<?php echo route("schedule.available-slots"); ?>', { date: date })
                    .done(function(response) {
                        if (response.success && response.slots.length > 0) {
                            let html = '<div class="row">';
                            response.slots.forEach(slot => {
                                const disabled = !slot.available ? 'disabled' : '';
                                html += `
                                    <div class="col-md-6 mb-2">
                                        <button class="btn btn-outline-primary w-100 time-slot-btn ${disabled}" 
                                                data-time="${slot.time}" ${disabled}>
                                            ${slot.label} ${!slot.available ? '(Booked)' : ''}
                                        </button>
                                    </div>
                                `;
                            });
                            html += '</div>';
                            container.html(html);

                            $('.time-slot-btn:not(.disabled)').click(function() {
                                $('.time-slot-btn').removeClass('btn-primary').addClass('btn-outline-primary');
                                $(this).removeClass('btn-outline-primary').addClass('btn-primary');
                                selectedEditTime = $(this).data('time');
                            });
                        } else {
                            container.html('<p class="text-warning">No slots available for this date.</p>');
                        }
                    })
                    .fail(function() {
                        container.html('<p class="text-danger">Error loading slots.</p>');
                    });
            }

            $('#confirmEdit').click(function() {
                const id = $('#edit_id').val();
                const date = $('#edit_date').val();
                
                if (!date || !selectedEditTime) {
                    alert('Please select both date and time slot');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                
                $.post(`<?php echo url('/schedule/reschedule'); ?>/${id}`, {
                    appointment_date: date,
                    appointment_time: selectedEditTime
                })
                .done(function(response) {
                    if (response.success) {
                        $('#editSuccess').text(response.message).show();
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    }
                })
                .fail(function(xhr) {
                    let errorMsg = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    $('#editErrors').text(errorMsg).show();
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-check"></i> Save Changes');
                });
            });

            // Delete appointment
            $('.delete-btn').click(function() {
                if (!confirm('Are you sure you want to delete this appointment? This action cannot be undone.')) {
                    return;
                }

                const id = $(this).data('id');
                const btn = $(this);
                btn.prop('disabled', true);

                $.ajax({
                    url: `<?php echo url('/schedule/delete'); ?>/${id}`,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            alert('Appointment deleted successfully!');
                            location.reload();
                        }
                    },
                    error: function() {
                        alert('Error deleting appointment');
                        btn.prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html>

