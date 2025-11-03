<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display the public booking page
     */
    public function publicBooking()
    {
        // Get all customers from database
        $customers = \App\Models\Customer::select('id', 'name', 'phone_number', 'email')
            ->where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get();
        
        // Get all departments
        $departments = \App\Models\Department::select('id', 'name')
            ->where('is_active', 1)
            ->orderBy('name', 'asc')
            ->get();
        
        return view('schedule.public-booking', compact('customers', 'departments'));
    }

    /**
     * Get available slots for a specific date
     */
    public function getAvailableSlots(Request $request)
    {
        $date = $request->input('date');
        
        // Check if it's a working day
        if (!Appointment::isWorkingDay($date)) {
            return response()->json([
                'success' => false,
                'message' => 'This day is not available for booking (Weekend or Holiday)',
                'slots' => []
            ]);
        }

        // Get booked slots for this date
        $bookedSlots = Appointment::getBookedSlots($date);
        $allSlots = Appointment::getTimeSlots();
        
        // Build available slots array
        $availableSlots = [];
        foreach ($allSlots as $time => $label) {
            $isBooked = in_array($time, $bookedSlots);
            $availableSlots[] = [
                'time' => $time,
                'label' => $label,
                'available' => !$isBooked,
                'is_booked' => $isBooked  // Extra field for clarity
            ];
        }

        return response()->json([
            'success' => true,
            'slots' => $availableSlots,
            'date' => $date,
            'booked_times' => $bookedSlots,  // Debug info
            'total_bookings' => count($bookedSlots)  // Debug info
        ]);
    }

    /**
     * Store a new appointment from public booking
     */
    public function storeAppointment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'department_id' => 'required|exists:departments,id',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'payment_mode' => 'required|in:cash,card,bank',
            'insurance' => 'nullable|string|max:255',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if slot is still available
        if (!Appointment::isSlotAvailable($request->appointment_date, $request->appointment_time)) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, this slot has already been booked. Please select another slot.'
            ], 409);
        }

        // Check if it's a working day
        if (!Appointment::isWorkingDay($request->appointment_date)) {
            return response()->json([
                'success' => false,
                'message' => 'Appointments cannot be booked on weekends or public holidays.'
            ], 400);
        }

        // Get customer and department details
        $customer = \App\Models\Customer::find($request->customer_id);
        $department = \App\Models\Department::find($request->department_id);
        
        $slots = Appointment::getTimeSlots();
        $slot = $slots[$request->appointment_time] ?? '';

        // Create appointment with user-entered phone and email
        $appointment = Appointment::create([
            'customer_id' => $request->customer_id,  // Customer ID for tracking
            'first_name' => $customer->name ?? 'N/A',
            'last_name' => '',
            'phone' => $request->phone,  // User entered
            'email' => $request->email,  // User entered
            'customer_type' => 'customer',
            'department' => $department->name ?? '',
            'payment_mode' => $request->payment_mode,
            'insurance' => $request->insurance,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'slot' => $slot,
            'status' => 'pending',
            'user_id' => Auth::check() ? Auth::id() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully!',
            'appointment' => $appointment
        ]);
    }

    /**
     * Display user's own appointments (based on their customers)
     */
    public function userAppointments()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Get customer IDs that belong to this user
        $customerIds = \App\Models\Customer::where('user_id', Auth::id())
            ->pluck('id')
            ->toArray();

        // Get appointments for user's customers
        $appointments = Appointment::with('customer')
            ->whereIn('customer_id', $customerIds)
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(15);

        return view('schedule.user-appointments', compact('appointments'));
    }

    /**
     * Reschedule an appointment
     */
    public function reschedule(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        // Check if user has permission to reschedule
        // User can reschedule if the appointment's customer belongs to them OR if they are admin
        $userCustomerIds = \App\Models\Customer::where('user_id', Auth::id())->pluck('id')->toArray();
        $isAdmin = Auth::check() && Auth::user()->role_id <= 2;
        
        if (!Auth::check() || (!in_array($appointment->customer_id, $userCustomerIds) && !$isAdmin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You can only reschedule your own customer appointments'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if new slot is available (exclude current appointment)
        $existingAppointment = Appointment::where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->where('id', '!=', $id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($existingAppointment) {
            return response()->json([
                'success' => false,
                'message' => 'This slot is not available. Please select another slot.'
            ], 409);
        }

        // Check if it's a working day
        if (!Appointment::isWorkingDay($request->appointment_date)) {
            return response()->json([
                'success' => false,
                'message' => 'Appointments cannot be scheduled on weekends or public holidays.'
            ], 400);
        }

        $slots = Appointment::getTimeSlots();
        $slot = $slots[$request->appointment_time] ?? '';

        $appointment->update([
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'slot' => $slot,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment rescheduled successfully!',
            'appointment' => $appointment
        ]);
    }

    /**
     * Cancel an appointment
     */
    public function cancel($id)
    {
        $appointment = Appointment::findOrFail($id);

        // Check if user has permission to cancel
        // User can cancel if the appointment's customer belongs to them OR if they are admin
        $userCustomerIds = \App\Models\Customer::where('user_id', Auth::id())->pluck('id')->toArray();
        $isAdmin = Auth::check() && Auth::user()->role_id <= 2;
        
        if (!Auth::check() || (!in_array($appointment->customer_id, $userCustomerIds) && !$isAdmin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - You can only cancel your own customer appointments'
            ], 403);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully!'
        ]);
    }

    /**
     * Admin: View all appointments
     */
    public function adminAppointments()
    {
        if (!Auth::check() || Auth::user()->role_id > 2) {
            abort(403, 'Unauthorized - Admin access required');
        }

        $appointments = Appointment::with(['customer', 'user', 'creator'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(20);

        return view('schedule.admin-appointments', compact('appointments'));
    }

    /**
     * Admin: Update appointment status
     */
    public function updateStatus(Request $request, $id)
    {
        if (!Auth::check() || Auth::user()->role_id > 2) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Admin access required'
            ], 403);
        }

        $appointment = Appointment::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $appointment->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!',
            'appointment' => $appointment
        ]);
    }

    /**
     * Admin: Delete appointment
     */
    public function destroy($id)
    {
        if (!Auth::check() || Auth::user()->role_id > 2) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Admin access required'
            ], 403);
        }

        $appointment = Appointment::findOrFail($id);
        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appointment deleted successfully!'
        ]);
    }
}

