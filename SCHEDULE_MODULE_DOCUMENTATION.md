# Appointment Scheduling Module Documentation

## Overview
This is a complete appointment scheduling system with public booking, user portal, and admin management capabilities.

---

## 🎯 Features

### Public Features
- ✅ Interactive calendar view (Google Calendar style)
- ✅ 1-hour time slots from 8 AM to 4 PM
- ✅ Saturday/Sunday/Public holidays automatically disabled
- ✅ Real-time slot availability checking
- ✅ Booked slots appear as disabled (dark)
- ✅ Booking form with patient information

### User Features
- ✅ View own appointments
- ✅ Reschedule appointments
- ✅ Cancel appointments
- ✅ View appointment details

### Admin Features
- ✅ View all appointments
- ✅ Change appointment status (pending/confirmed/cancelled/completed)
- ✅ Reschedule any appointment
- ✅ Delete appointments
- ✅ Statistics dashboard
- ✅ Export data (CSV, Excel, Print)

---

## 📁 Files Created

### 1. Database Migration
- **File:** `database/migrations/2025_11_03_000001_create_appointments_table.php`
- **Table:** `appointments`
- **Fields:** 
  - Personal info (first_name, last_name, phone, email)
  - Appointment details (date, time, slot, status)
  - Department, payment_mode, insurance, customer_type
  - Tracking fields (user_id, created_by, notes)

### 2. Model
- **File:** `app/Models/Appointment.php`
- **Features:**
  - Relationships with User model
  - Slot availability checking
  - Working day validation
  - Helper methods for time slots

### 3. Controller
- **File:** `app/Http/Controllers/ScheduleController.php`
- **Methods:**
  - `publicBooking()` - Display public booking page
  - `getAvailableSlots()` - Get available slots for a date
  - `storeAppointment()` - Book new appointment
  - `userAppointments()` - View user's appointments
  - `reschedule()` - Reschedule appointment
  - `cancel()` - Cancel appointment
  - `adminAppointments()` - Admin view all appointments
  - `updateStatus()` - Admin change status
  - `destroy()` - Admin delete appointment

### 4. Views
- **Public Booking:** `resources/views/schedule/public-booking.php`
- **User Portal:** `resources/views/schedule/user-appointments.php`
- **Admin Panel:** `resources/views/schedule/admin-appointments.php`

### 5. Routes
- **File:** `routes/web.php`
- All routes added under `/schedule` prefix

---

## 🔗 Access Links

### Public Access (No Login Required)
```
http://your-domain.com/schedule/booking
```
**Purpose:** Anyone can view calendar and book appointments

### User Portal (Login Required)
```
http://your-domain.com/schedule/my-appointments
```
**Purpose:** Users can view and manage their own appointments

### Admin Panel (Admin Login Required)
```
http://your-domain.com/schedule/admin
```
**Purpose:** Admin can view and manage all appointments

---

## 🚀 Installation Steps

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Clear Cache (Optional)
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### 3. Check if User Table has Admin Column
If your users table doesn't have an `is_admin` column, you'll need to add it or modify the admin check in the controller.

**Option A: Add is_admin column**
```php
// Create a new migration
php artisan make:migration add_is_admin_to_users_table

// In the migration file:
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->boolean('is_admin')->default(false);
    });
}

// Run migration
php artisan migrate
```

**Option B: Modify admin check in ScheduleController**
Replace `Auth::user()->is_admin` with your admin checking logic:
- `Auth::user()->role === 'admin'`
- `Auth::user()->user_type === 'admin'`
- Or any other admin checking mechanism you use

---

## 📝 Booking Form Fields

1. **First Name** (required)
2. **Last Name** (required)
3. **Phone** (required)
4. **Email** (required)
5. **User/Customer** (required) - Dropdown: user or customer
6. **Department** (required)
7. **Payment Mode** (required) - Dropdown: cash, card, bank
8. **Insurance** (optional)

---

## ⏰ Time Slots

Default slots (8 AM - 4 PM):
- 8:00 AM - 9:00 AM
- 9:00 AM - 10:00 AM
- 10:00 AM - 11:00 AM
- 11:00 AM - 12:00 PM
- 12:00 PM - 1:00 PM
- 1:00 PM - 2:00 PM
- 2:00 PM - 3:00 PM
- 3:00 PM - 4:00 PM

**To modify time slots:** Edit the `getTimeSlots()` method in `app/Models/Appointment.php`

---

## 🚫 Weekend/Holiday Configuration

### Current Settings
- **Saturday:** OFF
- **Sunday:** OFF

### To Add Public Holidays
Edit the `isWorkingDay()` method in `app/Models/Appointment.php`:

```php
$publicHolidays = [
    '2025-12-25', // Christmas
    '2025-01-01', // New Year
    '2025-07-04', // Independence Day
    // Add more holidays...
];
```

---

## 🎨 Customization

### Change Colors
Edit the CSS variables in each view file:

```css
:root {
    --primary-color: #4285f4;    /* Main blue */
    --secondary-color: #34a853;  /* Green */
    --danger-color: #ea4335;     /* Red */
    --warning-color: #fbbc04;    /* Yellow */
}
```

### Change Time Slots
Edit `app/Models/Appointment.php`:

```php
public static function getTimeSlots()
{
    return [
        '09:00:00' => '9:00 AM - 10:00 AM',
        '10:00:00' => '10:00 AM - 11:00 AM',
        // Add your slots...
    ];
}
```

---

## 🔐 API Endpoints

### Public Endpoints
- `POST /schedule/available-slots` - Get available slots for a date
- `POST /schedule/book` - Book an appointment

### Authenticated Endpoints
- `GET /schedule/my-appointments` - View user's appointments
- `POST /schedule/reschedule/{id}` - Reschedule appointment
- `POST /schedule/cancel/{id}` - Cancel appointment

### Admin Endpoints
- `GET /schedule/admin` - View all appointments
- `POST /schedule/update-status/{id}` - Update appointment status
- `DELETE /schedule/delete/{id}` - Delete appointment

---

## 📊 Status Types

1. **Pending** - Newly booked, awaiting confirmation
2. **Confirmed** - Appointment confirmed by admin
3. **Cancelled** - Cancelled by user or admin
4. **Completed** - Appointment has been completed

---

## 🔔 Important Notes

### Database Considerations
- The `appointments` table uses a unique constraint on `appointment_date` and `appointment_time` to prevent double booking
- If migration fails, check if the table already exists
- User relationships are optional (nullable) to allow public bookings without login

### Security
- Public booking page is accessible without authentication
- User portal requires login
- Admin panel requires admin privileges
- CSRF protection is enabled on all forms

### Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Responsive design works on mobile devices
- JavaScript required for interactive features

---

## 🐛 Troubleshooting

### Problem: Migration Error
**Solution:** The table might already exist. Check your database and either:
- Drop the table: `DROP TABLE appointments;` (if empty)
- Or skip the migration if already created

### Problem: Can't access admin panel
**Solution:** Make sure:
1. You're logged in
2. Your user has admin privileges (`is_admin = 1` or equivalent)
3. Modify the admin check in `ScheduleController.php` if needed

### Problem: Slots not loading
**Solution:**
1. Check JavaScript console for errors
2. Verify CSRF token is present
3. Check database connection
4. Verify routes are registered: `php artisan route:list | grep schedule`

### Problem: Routes not found
**Solution:**
```bash
php artisan route:clear
php artisan cache:clear
```

---

## 📱 Navigation Integration

### Add to Main Menu
Add these links to your application's navigation:

**For all users:**
```html
<a href="{{ route('schedule.public') }}">Book Appointment</a>
```

**For logged-in users:**
```html
<a href="{{ route('schedule.user-appointments') }}">My Appointments</a>
```

**For admins:**
```html
<a href="{{ route('schedule.admin') }}">Manage Appointments</a>
```

---

## 📧 Future Enhancements (Optional)

You can add these features later:
1. Email notifications for bookings/reminders
2. SMS notifications
3. Calendar sync (Google Calendar, Outlook)
4. Multiple service types/doctors
5. Payment integration
6. Waiting list functionality
7. Appointment notes/history
8. Custom appointment duration
9. Recurring appointments
10. Report generation

---

## 🆘 Support

If you encounter any issues:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Verify all files are in place
4. Check database connection
5. Verify routes are registered

---

## ✅ Testing Checklist

- [ ] Public booking page loads
- [ ] Calendar displays correctly
- [ ] Weekends are disabled
- [ ] Time slots load when date is selected
- [ ] Booking form submits successfully
- [ ] Booked slots appear as disabled
- [ ] User can view their appointments
- [ ] User can reschedule appointments
- [ ] User can cancel appointments
- [ ] Admin can view all appointments
- [ ] Admin can change status
- [ ] Admin can delete appointments
- [ ] Email/phone validation works

---

## 📄 License

This module is part of your Laravel application and follows the same license.

---

**Version:** 1.0  
**Created:** November 3, 2025  
**Compatible with:** Laravel 8.x, 9.x, 10.x, 11.x

