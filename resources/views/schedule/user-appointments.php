<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>My Appointments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
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
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .page-header h1 {
            margin: 0;
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: 500;
        }

        .page-header p {
            margin: 10px 0 0 0;
            color: var(--dark-color);
        }

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

        .btn-close-white {
            filter: brightness(0) invert(1);
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1><i class="fas fa-calendar-alt"></i> My Appointments</h1>
                    <p>View and manage your scheduled appointments</p>
                </div>
                <div>
                    <a href="<?php echo route('schedule.public'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Book New Appointment
                    </a>
                    <a href="<?php echo url('/'); ?>" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>

        <div class="appointments-card">
            <div class="table-responsive">
                <table class="table table-hover" id="appointmentsTable">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time Slot</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Payment Mode</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($appointments as $appointment): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($appointment->appointment_date)); ?></td>
                            <td><i class="far fa-clock"></i> <?php echo $appointment->slot; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($appointment->customer->name ?? $appointment->first_name); ?></strong>
                                <br><small class="text-muted">Phone: <?php echo htmlspecialchars($appointment->phone); ?></small>
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
                                <span class="status-badge status-<?php echo $appointment->status; ?>">
                                    <?php echo ucfirst($appointment->status); ?>
                                </span>
                            </td>
                            <td>
                                <?php if(in_array($appointment->status, ['pending', 'confirmed'])): ?>
                                <button class="btn btn-sm btn-warning btn-action reschedule-btn" 
                                        data-id="<?php echo $appointment->id; ?>"
                                        data-date="<?php echo $appointment->appointment_date; ?>"
                                        data-time="<?php echo $appointment->appointment_time; ?>">
                                    <i class="fas fa-calendar-alt"></i> Reschedule
                                </button>
                                <button class="btn btn-sm btn-danger btn-action cancel-btn" 
                                        data-id="<?php echo $appointment->id; ?>">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-info btn-action view-btn" 
                                        data-appointment='<?php echo json_encode($appointment); ?>'>
                                    <i class="fas fa-eye"></i> View
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

    <!-- Reschedule Modal -->
    <div class="modal fade" id="rescheduleModal" tabindex="-1" aria-labelledby="rescheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="rescheduleModalLabel">
                        <i class="fas fa-calendar-alt"></i> Reschedule Appointment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rescheduleForm">
                        <input type="hidden" id="reschedule_id">
                        
                        <div class="mb-3">
                            <label for="reschedule_date" class="form-label">New Date *</label>
                            <input type="date" class="form-control" id="reschedule_date" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Time Slot *</label>
                            <div id="reschedule_slots" class="time-slots">
                                <p class="text-muted">Please select a date first</p>
                            </div>
                        </div>

                        <div id="rescheduleErrors" class="alert alert-danger" style="display:none;"></div>
                        <div id="rescheduleSuccess" class="alert alert-success" style="display:none;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="confirmReschedule">
                        <i class="fas fa-check"></i> Confirm Reschedule
                    </button>
                </div>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).ready(function() {
            $('#appointmentsTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 10
            });

            // View appointment details
            $('.view-btn').click(function() {
                const appointment = $(this).data('appointment');
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Date:</strong> ${new Date(appointment.appointment_date).toLocaleDateString()}</p>
                            <p><strong>Time Slot:</strong> ${appointment.slot}</p>
                            <p><strong>First Name:</strong> ${appointment.first_name}</p>
                            <p><strong>Last Name:</strong> ${appointment.last_name}</p>
                            <p><strong>Phone:</strong> ${appointment.phone}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> ${appointment.email}</p>
                            <p><strong>Type:</strong> ${appointment.customer_type}</p>
                            <p><strong>Department:</strong> ${appointment.department}</p>
                            <p><strong>Payment Mode:</strong> ${appointment.payment_mode}</p>
                            <p><strong>Insurance:</strong> ${appointment.insurance || 'N/A'}</p>
                        </div>
                    </div>
                    <hr>
                    <p><strong>Status:</strong> <span class="status-badge status-${appointment.status}">${appointment.status}</span></p>
                    ${appointment.notes ? `<p><strong>Notes:</strong> ${appointment.notes}</p>` : ''}
                `;
                $('#appointmentDetails').html(html);
                new bootstrap.Modal(document.getElementById('viewModal')).show();
            });

            // Reschedule appointment
            let selectedRescheduleTime = null;

            $('.reschedule-btn').click(function() {
                const id = $(this).data('id');
                const date = $(this).data('date');
                
                $('#reschedule_id').val(id);
                $('#reschedule_date').val(date);
                selectedRescheduleTime = null;
                
                new bootstrap.Modal(document.getElementById('rescheduleModal')).show();
            });

            $('#reschedule_date').change(function() {
                const date = $(this).val();
                loadRescheduleSlots(date);
            });

            function loadRescheduleSlots(date) {
                const container = $('#reschedule_slots');
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
                                selectedRescheduleTime = $(this).data('time');
                            });
                        } else {
                            container.html('<p class="text-warning">No slots available for this date.</p>');
                        }
                    })
                    .fail(function() {
                        container.html('<p class="text-danger">Error loading slots.</p>');
                    });
            }

            $('#confirmReschedule').click(function() {
                const id = $('#reschedule_id').val();
                const date = $('#reschedule_date').val();
                
                if (!date || !selectedRescheduleTime) {
                    alert('Please select both date and time slot');
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Rescheduling...');
                
                $.post(`<?php echo url('/schedule/reschedule'); ?>/${id}`, {
                    appointment_date: date,
                    appointment_time: selectedRescheduleTime
                })
                .done(function(response) {
                    if (response.success) {
                        $('#rescheduleSuccess').text(response.message).show();
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
                    $('#rescheduleErrors').text(errorMsg).show();
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-check"></i> Confirm Reschedule');
                });
            });

            // Cancel appointment
            $('.cancel-btn').click(function() {
                if (!confirm('Are you sure you want to cancel this appointment?')) {
                    return;
                }

                const id = $(this).data('id');
                const btn = $(this);
                btn.prop('disabled', true);

                $.post(`<?php echo url('/schedule/cancel'); ?>/${id}`)
                    .done(function(response) {
                        if (response.success) {
                            alert(response.message);
                            location.reload();
                        }
                    })
                    .fail(function(xhr) {
                        alert('Error cancelling appointment');
                        btn.prop('disabled', false);
                    });
            });
        });
    </script>
</body>
</html>

