<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Book Appointment - Schedule Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a73e8;
            --primary-dark: #1557b0;
            --secondary-color: #34a853;
            --danger-color: #ea4335;
            --warning-color: #fbbc04;
            --dark-color: #5f6368;
            --light-bg: #f1f3f4;
            --border-color: #dadce0;
            --hover-bg: #e8f0fe;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .booking-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 600;
        }

        .header p {
            margin: 10px 0 0 0;
            opacity: 0.95;
        }

        .calendar-section {
            padding: 30px;
            background: #f8f9fa;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .calendar-header h3 {
            font-size: 1.4rem;
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
        }

        .btn-nav {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-nav:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        /* MAIN LAYOUT: Date boxes on left, Time slots on right */
        .booking-layout {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        /* LEFT SIDE: Compact Calendar */
        .calendar-wrapper {
            flex: 0 0 350px;
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }

        .day-header {
            text-align: center;
            font-weight: 700;
            color: var(--primary-color);
            padding: 8px 4px;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        /* SMALLER DATE BOXES */
        .day-cell {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            font-size: 0.9rem;
            background: white;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        }

        .day-cell:hover:not(.disabled):not(.weekend) {
            border-color: var(--primary-color);
            background: var(--hover-bg);
            transform: scale(1.05);
        }

        .day-cell.selected {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-color: var(--primary-color);
            transform: scale(1.1);
        }

        .day-cell.today {
            border-color: var(--secondary-color);
            border-width: 3px;
        }

        .day-cell.weekend, .day-cell.disabled {
            background: #e0e0e0;
            color: #999;
            cursor: not-allowed;
            opacity: 0.5;
        }

        /* RIGHT SIDE: Time Slots (shown when date selected) */
        .time-slots-wrapper {
            flex: 1;
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            min-height: 400px;
        }

        .time-slots-wrapper.hidden {
            display: none;
        }

        .time-slots-header {
            color: var(--primary-color);
            font-size: 1.3rem;
            margin-bottom: 20px;
            font-weight: 600;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
        }

        .time-slot {
            padding: 18px;
            border: 3px solid #e0e0e0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            font-size: 1rem;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }
        
        .time-slot::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(26, 115, 232, 0.1), transparent);
            transition: left 0.5s;
        }
        
        .time-slot:hover::before:not(.disabled) {
            left: 100%;
        }

        .time-slot:hover:not(.disabled) {
            border-color: var(--primary-color);
            background: linear-gradient(135deg, #e8f0fe, #d2e3fc);
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 8px 20px rgba(26, 115, 232, 0.25);
        }
        
        .time-slot i {
            font-size: 1.3rem;
            margin-bottom: 8px;
            display: block;
        }

        .time-slot.disabled {
            position: relative;
            background: linear-gradient(135deg, #e74c3c, #c0392b) !important;
            border: 3px solid #a93226 !important;
            color: white !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            user-select: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            opacity: 0.95 !important;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.4) !important;
        }
        
        .time-slot.disabled::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: repeating-linear-gradient(
                45deg,
                rgba(0, 0, 0, 0.1),
                rgba(0, 0, 0, 0.1) 10px,
                transparent 10px,
                transparent 20px
            );
            z-index: 1;
        }
        
        .time-slot.disabled::after {
            content: '🔒 BOOKED';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 1px;
            z-index: 10;
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        
        .time-slot.disabled i,
        .time-slot.disabled br {
            position: relative;
            z-index: 2;
            opacity: 0.5;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
        }

        .time-slot.disabled small {
            color: #ff5252;
            font-weight: 700;
        }

        .time-slot.disabled:hover {
            transform: none !important;
            cursor: not-allowed !important;
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.6) !important;
            border-color: #ff0000 !important;
        }
        
        .time-slot.disabled:hover::after {
            animation: pulseBooked 0.6s ease-in-out;
        }
        
        @keyframes pulseBooked {
            0%, 100% {
                transform: translate(-50%, -50%) scale(1);
            }
            50% {
                transform: translate(-50%, -50%) scale(1.1);
            }
        }

        .time-slot.selected {
            background: linear-gradient(135deg, #27ae60, #229954) !important;
            color: white !important;
            border: 3px solid #1e8449 !important;
            transform: scale(1.05) !important;
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.5) !important;
            animation: selectedPulse 1s ease-in-out infinite;
        }
        
        .time-slot.selected i {
            color: white !important;
            opacity: 1 !important;
        }
        
        @keyframes selectedPulse {
            0%, 100% {
                box-shadow: 0 8px 25px rgba(39, 174, 96, 0.5);
            }
            50% {
                box-shadow: 0 10px 30px rgba(39, 174, 96, 0.7);
            }
        }

        .loading {
            text-align: center;
            padding: 40px;
        }

        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: var(--primary-color);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--dark-color);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--border-color);
            margin-bottom: 20px;
        }

        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 20px 25px;
        }

        .modal-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid var(--border-color);
            padding: 10px 14px;
            font-size: 0.95rem;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 115, 232, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 115, 232, 0.3);
        }

        .btn-secondary {
            background: var(--dark-color);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 12px 16px;
            font-weight: 500;
        }

        .alert-info {
            background: #e3f2fd;
            color: #1565c0;
        }

        .customer-info-box {
            background: var(--hover-bg);
            padding: 12px;
            border-radius: 8px;
            margin-top: 8px;
            display: none;
        }

        .customer-info-box.active {
            display: block;
        }

        .customer-info-box p {
            margin: 4px 0;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .booking-layout {
                flex-direction: column;
            }
            
            .calendar-wrapper {
                flex: 1;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <div class="header">
            <h1><i class="fas fa-calendar-check"></i> Book Your Appointment</h1>
            <p>Select a date to view available time slots</p>
        </div>

        <div class="calendar-section">
            <div class="calendar-header">
                <button class="btn btn-nav" id="prevMonth">
                    <i class="fas fa-chevron-left"></i> Previous
                </button>
                <h3 id="currentMonth"></h3>
                <button class="btn btn-nav" id="nextMonth">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- SIDE BY SIDE LAYOUT -->
            <div class="booking-layout">
                <!-- LEFT: COMPACT CALENDAR -->
                <div class="calendar-wrapper">
                    <div class="calendar-grid" id="calendarGrid">
                        <!-- Calendar will be generated here -->
                    </div>
                </div>

                <!-- RIGHT: TIME SLOTS -->
                <div class="time-slots-wrapper hidden" id="timeSlotsWrapper">
                    <div class="time-slots-header">
                        <i class="fas fa-clock"></i> Available Times for <span id="selectedDateText"></span>
                    </div>
                    
                    <div class="loading" id="slotsLoading" style="display:none;">
                        <div class="spinner-border" role="status"></div>
                        <p class="mt-2">Loading slots...</p>
                    </div>
                    
                    <div id="slotsMessage"></div>
                    
                    <div class="time-slots-grid" id="timeSlotsGrid">
                        <!-- Time slots will be generated here -->
                    </div>
                </div>

                <!-- EMPTY STATE (when no date selected) -->
                <div class="time-slots-wrapper" id="emptyState">
                    <div class="empty-state">
                        <i class="fas fa-hand-pointer"></i>
                        <h4>Select a Date</h4>
                        <p>Click on a date from the calendar to view available time slots</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Form Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus"></i> Complete Your Booking
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong><i class="fas fa-info-circle"></i> Selected:</strong> <span id="modalSelectedInfo"></span>
                    </div>
                    
                    <form id="bookingForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customerId" class="form-label">Select Customer *</label>
                                <select class="form-select" id="customerId" name="customer_id" required>
                                    <option value="">-- Select Customer --</option>
                                    <?php foreach($customers as $customer): ?>
                                    <option value="<?php echo $customer->id; ?>" 
                                            data-phone="<?php echo $customer->phone_number; ?>" 
                                            data-email="<?php echo $customer->email; ?>">
                                        <?php echo htmlspecialchars($customer->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <div class="customer-info-box" id="customerInfoBox">
                                    <p><strong>Phone:</strong> <span id="customerPhone"></span></p>
                                    <p><strong>Email:</strong> <span id="customerEmail"></span></p>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="departmentId" class="form-label">Select Department *</label>
                                <select class="form-select" id="departmentId" name="department_id" required>
                                    <option value="">-- Select Department --</option>
                                    <?php foreach($departments as $department): ?>
                                    <option value="<?php echo $department->id; ?>">
                                        <?php echo htmlspecialchars($department->name); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="paymentMode" class="form-label">Payment Mode *</label>
                                <select class="form-select" id="paymentMode" name="payment_mode" required>
                                    <option value="">-- Select Payment Mode --</option>
                                    <option value="cash">💵 Cash</option>
                                    <option value="card">💳 Card</option>
                                    <option value="bank">🏦 Bank Transfer</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="insurance" class="form-label">Insurance</label>
                                <input type="text" class="form-control" id="insurance" name="insurance">
                            </div>
                        </div>

                        <input type="hidden" id="appointmentDate" name="appointment_date">
                        <input type="hidden" id="appointmentTime" name="appointment_time">

                        <div id="formErrors" class="alert alert-danger" style="display:none;"></div>
                        <div id="formSuccess" class="alert alert-success" style="display:none;"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmBooking">
                        <i class="fas fa-check"></i> Confirm Booking
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        let currentDate = new Date();
        let selectedDate = null;
        let selectedTime = null;
        let selectedSlotLabel = null;

        const daysOfWeek = ['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'];
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'];

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#customerId').change(function() {
            const selectedOption = $(this).find('option:selected');
            if (selectedOption.val()) {
                $('#customerPhone').text(selectedOption.data('phone'));
                $('#customerEmail').text(selectedOption.data('email'));
                $('#customerInfoBox').addClass('active');
            } else {
                $('#customerInfoBox').removeClass('active');
            }
        });

        function renderCalendar() {
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();
            
            document.getElementById('currentMonth').textContent = `${monthNames[month]} ${year}`;
            
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            let calendarHTML = '';
            
            daysOfWeek.forEach(day => {
                calendarHTML += `<div class="day-header">${day}</div>`;
            });
            
            for (let i = 0; i < firstDay; i++) {
                calendarHTML += '<div class="day-cell disabled"></div>';
            }
            
            const today = new Date();
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
                const dateStr = formatDate(date);
                const dayOfWeek = date.getDay();
                const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
                const isPast = date < new Date(today.getFullYear(), today.getMonth(), today.getDate());
                const isToday = date.toDateString() === today.toDateString();
                
                let classes = 'day-cell';
                if (isWeekend) classes += ' weekend';
                if (isPast && !isToday) classes += ' disabled';
                if (isToday) classes += ' today';
                if (selectedDate === dateStr) classes += ' selected';
                
                calendarHTML += `<div class="${classes}" data-date="${dateStr}" ${(isWeekend || isPast) && !isToday ? 'style="pointer-events:none"' : ''}>${day}</div>`;
            }
            
            document.getElementById('calendarGrid').innerHTML = calendarHTML;
            
            document.querySelectorAll('.day-cell:not(.disabled):not(.weekend)').forEach(cell => {
                cell.addEventListener('click', function() {
                    selectDate(this.dataset.date);
                });
            });
        }

        function formatDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function selectDate(date) {
            selectedDate = date;
            renderCalendar();
            loadTimeSlots(date);
            
            // Show time slots wrapper, hide empty state
            $('#timeSlotsWrapper').removeClass('hidden');
            $('#emptyState').hide();
        }

        function loadTimeSlots(date) {
            const slotsLoading = $('#slotsLoading');
            const timeSlotsGrid = $('#timeSlotsGrid');
            const slotsMessage = $('#slotsMessage');
            
            // Clear all existing event listeners first
            timeSlotsGrid.find('.time-slot').off('click');
            
            slotsLoading.show();
            timeSlotsGrid.html('');
            slotsMessage.html('');
            
            const dateObj = new Date(date);
            $('#selectedDateText').text(
                `${monthNames[dateObj.getMonth()]} ${dateObj.getDate()}, ${dateObj.getFullYear()}`
            );
            
            $.post('<?php echo route("schedule.available-slots"); ?>', { date: date })
                .done(function(response) {
                    slotsLoading.hide();
                    
                    // DEBUG: Log the response to console
                    console.log('=== SLOTS API RESPONSE ===');
                    console.log('Date:', response.date);
                    console.log('Total Bookings:', response.total_bookings);
                    console.log('Booked Times:', response.booked_times);
                    console.log('All Slots:', response.slots);
                    
                    if (response.success && response.slots.length > 0) {
                        let slotsHTML = '';
                        let bookedCount = 0;
                        
                        response.slots.forEach(slot => {
                            const disabledClass = !slot.available ? 'disabled' : '';
                            const icon = slot.available ? 'fa-clock' : 'fa-lock';
                            const disabledTitle = !slot.available ? 'This slot is already booked and unavailable' : 'Click to book this slot';
                            
                            if (!slot.available) {
                                bookedCount++;
                                console.log('🔒 DISABLED SLOT:', slot.time, slot.label);
                            }
                            
                            slotsHTML += `
                                <div class="time-slot ${disabledClass}" 
                                     data-time="${slot.time}" 
                                     data-label="${slot.label}"
                                     data-available="${slot.available}"
                                     title="${disabledTitle}">
                                    <i class="far ${icon}"></i><br>${slot.label}
                                </div>
                            `;
                        });
                        
                        console.log('✅ Total slots rendered:', response.slots.length);
                        console.log('🔒 Disabled slots:', bookedCount);
                        timeSlotsGrid.html(slotsHTML);
                        
                        // Remove all previous event listeners
                        $('.time-slot').off('click');
                        
                        // Add click event ONLY to available (non-disabled) slots
                        $('.time-slot:not(.disabled)').on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            
                            // Double check - if disabled, don't proceed
                            if ($(this).hasClass('disabled')) {
                                alert('⚠️ This slot is already booked!\n\nPlease select another available time slot.');
                                return false;
                            }
                            
                            selectTimeSlot($(this).data('time'), $(this).data('label'));
                        });
                        
                        // Add warning for disabled slots
                        $('.time-slot.disabled').on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            alert('🔒 SLOT ALREADY BOOKED!\n\nThis time slot is not available.\nPlease choose another time.');
                            return false;
                        });
                    } else {
                        slotsMessage.html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> No slots available</div>');
                    }
                })
                .fail(function() {
                    slotsLoading.hide();
                    slotsMessage.html('<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Error loading slots</div>');
                });
        }

        function selectTimeSlot(time, label) {
            // Extra validation - check if clicked element is disabled
            const clickedElement = event.target.closest('.time-slot');
            if (clickedElement && $(clickedElement).hasClass('disabled')) {
                alert('🔒 This slot is already booked!\n\nPlease select another available time slot.');
                return false;
            }
            
            selectedTime = time;
            selectedSlotLabel = label;
            
            $('.time-slot').removeClass('selected');
            if (clickedElement) {
                clickedElement.classList.add('selected');
            }
            
            $('#appointmentDate').val(selectedDate);
            $('#appointmentTime').val(selectedTime);
            $('#modalSelectedInfo').text($('#selectedDateText').text() + ' at ' + label);
            
            new bootstrap.Modal($('#bookingModal')).show();
        }

        $('#prevMonth').click(function() {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });

        $('#nextMonth').click(function() {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        $('#confirmBooking').click(function() {
            const form = $('#bookingForm')[0];
            const data = Object.fromEntries(new FormData(form));
            
            $('#formErrors, #formSuccess').hide();
            
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Booking...');
            
            $.post('<?php echo route("schedule.book"); ?>', data)
                .done(function(response) {
                    if (response.success) {
                        $('#formSuccess').html('<i class="fas fa-check-circle"></i> ' + response.message + '<br><small>The selected time slot is now disabled and unavailable for others.</small>').show();
                        form.reset();
                        $('#customerInfoBox').removeClass('active');
                        
                        setTimeout(function() {
                            bootstrap.Modal.getInstance($('#bookingModal')[0]).hide();
                            // Reload time slots to show the booked slot as disabled
                            loadTimeSlots(selectedDate);
                            
                            // Show confirmation that slot is now disabled
                            alert('✅ Booking Confirmed!\n\n🔒 The time slot is now LOCKED and DISABLED.\nNo one else can book this slot.');
                        }, 1500);
                    }
                })
                .fail(function(xhr) {
                    let errorMsg = 'An error occurred';
                    if (xhr.responseJSON?.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON?.errors) {
                        errorMsg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                    }
                    $('#formErrors').html('<i class="fas fa-exclamation-circle"></i> ' + errorMsg).show();
                })
                .always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-check"></i> Confirm Booking');
                });
        });

        renderCalendar();
    </script>
</body>
</html>
