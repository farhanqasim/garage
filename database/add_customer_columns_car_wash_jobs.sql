-- Add customer_name, vehicle_no, mobile to car_wash_jobs if missing.
-- Run this in MySQL/phpMyAdmin if "php artisan migrate" is not an option.

-- Add customer_name (ignore error if column already exists)
ALTER TABLE car_wash_jobs ADD COLUMN customer_name VARCHAR(255) NULL AFTER customer_car_id;

-- Add vehicle_no (ignore error if column already exists)
ALTER TABLE car_wash_jobs ADD COLUMN vehicle_no VARCHAR(255) NULL AFTER customer_name;

-- Add mobile (ignore error if column already exists)
ALTER TABLE car_wash_jobs ADD COLUMN mobile VARCHAR(255) NULL AFTER vehicle_no;
