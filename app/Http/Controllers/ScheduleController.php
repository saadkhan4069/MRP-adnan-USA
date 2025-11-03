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

        // Get customer details
        $customer = \App\Models\Customer::find($request->customer_id);
        $department = \App\Models\Department::find($request->department_id);
        
        $slots = Appointment::getTimeSlots();
        $slot = $slots[$request->appointment_time] ?? '';

        $appointment = Appointment::create([
            'first_name' => $customer->name ?? 'N/A',
            'last_name' => '',
            'phone' => $customer->phone_number ?? '',
            'email' => $customer->email ?? '',
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
     * Display user's own appointments
     */
    public function userAppointments()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $appointments = Appointment::where('user_id', Auth::id())
            ->orWhere('email', Auth::user()->email)
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
        if (!Auth::check() || (Auth::id() !== $appointment->user_id && !Auth::user()->is_admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
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
        if (!Auth::check() || (Auth::id() !== $appointment->user_id && !Auth::user()->is_admin)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
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
        // if (!Auth::check() || !Auth::user()->is_admin) {
        //     abort(403, 'Unauthorized');
        // }

        $appointments = Appointment::with(['user', 'creator'])
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
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
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
        if (!Auth::check() || !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
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

