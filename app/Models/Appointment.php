<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'customer_type',
        'department',
        'payment_mode',
        'insurance',
        'appointment_date',
        'appointment_time',
        'slot',
        'status',
        'user_id',
        'created_by',
        'notes'
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime',
    ];

    /**
     * Get the user who owns the appointment
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who created the appointment
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if a slot is available
     */
    public static function isSlotAvailable($date, $time)
    {
        return !self::where('appointment_date', $date)
            ->where('appointment_time', $time)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
    }

    /**
     * Get booked slots for a specific date
     */
    public static function getBookedSlots($date)
    {
        $bookedTimes = self::where('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->pluck('appointment_time')
            ->map(function($time) {
                // Convert to HH:MM:SS format for consistent comparison
                if ($time instanceof \DateTime) {
                    return $time->format('H:i:s');
                }
                // If it's already a string, ensure proper format
                if (strlen($time) == 5) { // HH:MM format
                    return $time . ':00';
                }
                return $time;
            })
            ->toArray();
        
        return $bookedTimes;
    }

    /**
     * Check if date is weekend or holiday
     */
    public static function isWorkingDay($date)
    {
        $carbon = Carbon::parse($date);
        
        // Check if Saturday or Sunday
        if ($carbon->isSaturday() || $carbon->isSunday()) {
            return false;
        }

        // You can add public holidays check here
        $publicHolidays = [
            // Add your public holidays in Y-m-d format
            // '2025-12-25', // Christmas
            // '2025-01-01', // New Year
        ];

        return !in_array($carbon->format('Y-m-d'), $publicHolidays);
    }

    /**
     * Get available time slots
     */
    public static function getTimeSlots()
    {
        return [
            '08:00:00' => '8:00 AM - 9:00 AM',
            '09:00:00' => '9:00 AM - 10:00 AM',
            '10:00:00' => '10:00 AM - 11:00 AM',
            '11:00:00' => '11:00 AM - 12:00 PM',
            '12:00:00' => '12:00 PM - 1:00 PM',
            '13:00:00' => '1:00 PM - 2:00 PM',
            '14:00:00' => '2:00 PM - 3:00 PM',
            '15:00:00' => '3:00 PM - 4:00 PM',
        ];
    }
}

