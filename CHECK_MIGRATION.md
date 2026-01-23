# Migration Check Instructions

## Problem: New fields not inserting data

## Solution Steps:

1. **Run the migration:**
   ```bash
   php artisan migrate
   ```

2. **Check if columns exist in database:**
   ```sql
   DESCRIBE shipments;
   ```
   OR
   ```bash
   php artisan tinker
   >>> Schema::hasColumn('shipments', 'ship_from_lunch_hour')
   >>> Schema::hasColumn('shipments', 'ship_to_lunch_hour')
   ```

3. **If migration fails, check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **If columns don't exist, manually add them:**
   ```sql
   ALTER TABLE shipments 
   ADD COLUMN ship_from_lunch_hour VARCHAR(255) NULL AFTER ship_from_dock_hours,
   ADD COLUMN ship_from_pickup_delivery_instructions TEXT NULL AFTER ship_from_lunch_hour,
   ADD COLUMN ship_from_appointment VARCHAR(255) NULL AFTER ship_from_pickup_delivery_instructions,
   ADD COLUMN ship_from_accessorial TEXT NULL AFTER ship_from_appointment,
   ADD COLUMN ship_to_lunch_hour VARCHAR(255) NULL AFTER ship_to_dock_hours,
   ADD COLUMN ship_to_pickup_delivery_instructions TEXT NULL AFTER ship_to_lunch_hour,
   ADD COLUMN ship_to_appointment VARCHAR(255) NULL AFTER ship_to_pickup_delivery_instructions,
   ADD COLUMN ship_to_accessorial TEXT NULL AFTER ship_to_appointment;
   ```

5. **Check Laravel logs after form submission:**
   - Look for "Shipment Store Request Data" log entry
   - This will show if data is being received from form

6. **Verify form field names match:**
   - All fields should have `name="ship_from_lunch_hour"` etc.
   - Check browser network tab to see what data is being sent

