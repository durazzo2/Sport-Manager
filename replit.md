# Sportsalit - Sports Hall Management System

## Overview
A production-ready Sports Hall Management System built with the TALL stack (Tailwind CSS, Alpine.js, Livewire v3, Laravel 11) and FilamentPHP v3 admin panel. Users can browse sports facilities, book courts, and manage their bookings with QR codes.

## Recent Changes
- **2026-02-13**: Context-aware equipment rentals - rentals now filtered by court sport type in booking modal
- **2026-02-13**: Full system build - packages, database, models, admin panel, frontend, booking flow, dashboard, seeder

## Tech Stack
- **Framework**: Laravel 11.48.0
- **PHP Version**: 8.4
- **Frontend**: Livewire v3 + Blade + Tailwind CSS (CDN)
- **Admin Panel**: FilamentPHP v3 with Shield (roles/permissions), FullCalendar
- **Database**: SQLite (at `database/database.sqlite`)
- **Server**: `php artisan serve` on port 5000

## Key Packages
- filament/filament v3
- bezhansalleh/filament-shield (Roles & Permissions)
- saade/filament-fullcalendar (Admin calendar)
- spatie/laravel-medialibrary (Media management)
- spatie/opening-hours (Schedule management)
- simplesoftwareio/simple-qrcode (QR code generation)

## Database Schema (UUIDs for new tables, auto-increment for users)
- **Users**: Auth fields + phone, roles via Spatie Permission
- **Amenities**: name, icon (Heroicon string)
- **Rentals**: name, price (cents), suitable_for (JSON array of sport types)
- **Facilities**: name, description, city, address, image_path
- **Courts**: facility_id, name, type (Football/Tennis/Padel/Swimming), base_price_per_hour (cents), image_path
- **Bookings**: user_id, court_id, start/end_time, status, total_price (cents), qr_code
- **BookingRental** (pivot): booking_id, rental_id, quantity
- **Reviews**: user_id, facility_id, rating (1-5), comment

## Project Structure
- `app/Models/` - Eloquent models with UUID traits and business logic
- `app/Livewire/` - Livewire components (SearchFacilities, FacilityDetail, UserDashboard, LeaveReview)
- `app/Filament/Resources/` - Admin panel resources (Facility, Court, Rental, Amenity, Booking, Review)
- `app/Http/Controllers/AuthController.php` - Authentication
- `resources/views/layouts/app.blade.php` - Main layout
- `resources/views/livewire/` - Livewire component views
- `resources/views/auth/` - Login/register views
- `public/images/` - Stock images for facilities
- `database/seeders/DatabaseSeeder.php` - Sample data

## Business Logic
- **Dynamic Pricing**: Peak hours (18:00-22:00) +20%, Weekends +10%
- **Availability**: Prevents overlapping bookings on same court
- **QR Codes**: Generated on booking confirmation using UUID
- **Cancellation**: Only allowed if booking is confirmed and >24h from start

## Test Accounts
- Admin: admin@test.com / password (access /admin)
- Customer: user@test.com / password

## User Preferences
- None recorded yet
