-- SQL Script to add missing shipment columns
-- Run this manually in your database if migration fails

ALTER TABLE shipments 
ADD COLUMN IF NOT EXISTS ship_from_lunch_hour VARCHAR(255) NULL AFTER ship_from_dock_hours,
ADD COLUMN IF NOT EXISTS ship_from_pickup_delivery_instructions TEXT NULL AFTER ship_from_lunch_hour,
ADD COLUMN IF NOT EXISTS ship_from_appointment VARCHAR(255) NULL AFTER ship_from_pickup_delivery_instructions,
ADD COLUMN IF NOT EXISTS ship_from_accessorial TEXT NULL AFTER ship_from_appointment,
ADD COLUMN IF NOT EXISTS ship_to_lunch_hour VARCHAR(255) NULL AFTER ship_to_dock_hours,
ADD COLUMN IF NOT EXISTS ship_to_pickup_delivery_instructions TEXT NULL AFTER ship_to_lunch_hour,
ADD COLUMN IF NOT EXISTS ship_to_appointment VARCHAR(255) NULL AFTER ship_to_pickup_delivery_instructions,
ADD COLUMN IF NOT EXISTS ship_to_accessorial TEXT NULL AFTER ship_to_appointment;

-- If IF NOT EXISTS doesn't work, use this version:
-- ALTER TABLE shipments 
-- ADD COLUMN ship_from_lunch_hour VARCHAR(255) NULL,
-- ADD COLUMN ship_from_pickup_delivery_instructions TEXT NULL,
-- ADD COLUMN ship_from_appointment VARCHAR(255) NULL,
-- ADD COLUMN ship_from_accessorial TEXT NULL,
-- ADD COLUMN ship_to_lunch_hour VARCHAR(255) NULL,
-- ADD COLUMN ship_to_pickup_delivery_instructions TEXT NULL,
-- ADD COLUMN ship_to_appointment VARCHAR(255) NULL,
-- ADD COLUMN ship_to_accessorial TEXT NULL;

