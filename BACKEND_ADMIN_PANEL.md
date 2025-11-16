# Backend Admin Panel Documentation

## Overview

A comprehensive backend admin panel for managing the Ticket Booking System with full CRUD operations, role-based access control, and a professional Bootstrap 5 interface.

## Features

### 1. Admin Role System
- **Migration**: Adds `is_admin` boolean field to users table
- **Middleware**: `IsAdmin` middleware protects all backend routes
- **Authorization**: Only users with `is_admin = true` can access the admin panel

### 2. Dashboard
- **Statistics Cards**:
  - Total Events
  - Total Bookings
  - Total Revenue (confirmed bookings)
  - Total Users
- **Recent Bookings Table**: Shows last 10 bookings with quick view links

### 3. Events Management
- **List View**: Paginated table showing all events with:
  - Event details (name, venue, location, dates)
  - Status badges (draft, published, cancelled)
  - Ticket type count
  - Edit and delete actions
- **Create/Edit Forms**: Full event management with validation
- **Nested Ticket Types**: Manage ticket types directly from event edit page

### 4. Ticket Types Management
- **Nested Routes**: Ticket types are managed under their parent event
- **CRUD Operations**:
  - Create new ticket types
  - Edit existing ticket types
  - Delete ticket types
  - Price in cents for precision
  - Quantity management

### 5. Users Management
- **User List**: View all users with booking counts
- **User Details**: View individual user profiles and booking history
- **Admin Toggle**: Make users admins (cannot remove your own admin access)
- **User Deletion**: Delete users (cannot delete yourself)

### 6. Bookings Management
- **Advanced Filtering**:
  - Search by booking code, customer name, or email
  - Filter by status (pending, confirmed, cancelled, refunded)
- **Booking Details**: Comprehensive view showing:
  - Booking information
  - Customer details
  - Event details
  - Booked tickets breakdown
  - Payment information
- **Refund Action**: Refund bookings with status updates

## Installation & Setup

### 1. Run Migration
```bash
php artisan migrate
```

This will add the `is_admin` column to the users table.

### 2. Create Admin User

**Option A: Using Seeder**
```bash
php artisan db:seed --class=AdminUserSeeder
```

This creates an admin user with:
- Email: `admin@example.com`
- Password: `password`

**Option B: Using Tinker**
```bash
php artisan tinker
```

```php
$user = User::find(1); // or any user ID
$user->is_admin = true;
$user->save();
```

**Option C: Direct Database**
```sql
UPDATE users SET is_admin = 1 WHERE email = 'your@email.com';
```

### 3. Access Admin Panel

Visit: `http://yourdomain.com/backend/dashboard`

You must be:
1. Logged in
2. Have `is_admin = true` in your user record

## Routes

All backend routes are prefixed with `/backend` and protected by `auth` and `admin` middleware:

### Dashboard
- `GET /backend/dashboard` - Admin dashboard

### Events
- `GET /backend/events` - List all events
- `GET /backend/events/create` - Create event form
- `POST /backend/events` - Store new event
- `GET /backend/events/{event}/edit` - Edit event form
- `PUT /backend/events/{event}` - Update event
- `DELETE /backend/events/{event}` - Delete event

### Ticket Types (Nested under events)
- `GET /backend/events/{event}/ticket-types/create` - Create ticket type
- `POST /backend/events/{event}/ticket-types` - Store ticket type
- `GET /backend/events/{event}/ticket-types/{ticketType}/edit` - Edit ticket type
- `PUT /backend/events/{event}/ticket-types/{ticketType}` - Update ticket type
- `DELETE /backend/events/{event}/ticket-types/{ticketType}` - Delete ticket type

### Users
- `GET /backend/users` - List all users
- `GET /backend/users/{user}` - View user details
- `GET /backend/users/{user}/edit` - Edit user form
- `PUT /backend/users/{user}` - Update user
- `DELETE /backend/users/{user}` - Delete user

### Bookings
- `GET /backend/bookings` - List all bookings (with filters)
- `GET /backend/bookings/{booking}` - View booking details
- `POST /backend/bookings/{booking}/refund` - Refund a booking

## Controllers

### Backend Controllers Location
`app/Http/Controllers/Backend/`

1. **DashboardController** - Dashboard statistics and recent bookings
2. **EventController** - Full event CRUD operations
3. **TicketTypeController** - Ticket type management (nested under events)
4. **UserController** - User management and admin assignment
5. **BookingController** - Booking management and refunds

## Views

### Layout
`resources/views/backend/layouts/admin.blade.php`

Features:
- Responsive sidebar navigation
- Bootstrap 5 styling
- Professional blue gradient sidebar
- Alert notifications (success/error)
- User information and logout in topbar

### View Files
```
resources/views/backend/
├── layouts/
│   └── admin.blade.php
├── dashboard/
│   └── index.blade.php
├── events/
│   ├── index.blade.php
│   ├── create.blade.php
│   └── edit.blade.php
├── ticket-types/
│   ├── create.blade.php
│   └── edit.blade.php
├── users/
│   ├── index.blade.php
│   ├── show.blade.php
│   └── edit.blade.php
└── bookings/
    ├── index.blade.php
    └── show.blade.php
```

## Middleware

### IsAdmin Middleware
**Location**: `app/Http/Middleware/IsAdmin.php`

**Function**: Checks if the authenticated user has admin privileges

**Registration**: Registered as 'admin' alias in `bootstrap/app.php`

## Security Features

1. **Authentication Required**: All routes require user to be logged in
2. **Admin Authorization**: All routes require `is_admin = true`
3. **CSRF Protection**: All forms include CSRF tokens
4. **Self-Protection**: Users cannot delete themselves or remove their own admin access
5. **Validation**: All forms include server-side validation
6. **Confirmation Dialogs**: Destructive actions require JavaScript confirmation

## Styling

- **Framework**: Bootstrap 5.3.2
- **Icons**: Bootstrap Icons 1.11.1
- **Color Scheme**: Professional blue gradient
- **Layout**: Responsive sidebar with main content area
- **Components**: Cards, tables, forms, badges, alerts

## Data Relationships

### Event → Ticket Types
- One-to-Many relationship
- Ticket types are created/managed within event context
- Deleting an event should consider ticket type dependencies

### User → Bookings
- One-to-Many relationship
- Users can view all their bookings
- Admins can manage all bookings

### Booking → Payment
- One-to-One relationship
- Payment details displayed in booking view
- Refund updates both booking and payment status

## Validation Rules

### Event Validation
- Name: required, string, max:255
- Description: required, string
- Venue: required, string, max:255
- Location: required, string, max:255
- Status: required, in:draft,published,cancelled
- Starts At: required, date
- Ends At: required, date, after:starts_at

### Ticket Type Validation
- Name: required, string, max:255
- Description: nullable, string
- Price (cents): required, integer, min:0
- Total Quantity: required, integer, min:1

### User Validation
- Name: required, string, max:255
- Email: required, email, unique (except current user)
- Phone: nullable, string, max:20
- Is Admin: boolean

## Best Practices

1. **Always use the seeder or manual update to create first admin user**
2. **Test admin access before deploying to production**
3. **Keep at least one admin user to prevent lockout**
4. **Use strong passwords for admin accounts**
5. **Regularly review admin user list**
6. **Test refund functionality in staging before production use**

## Troubleshooting

### Cannot Access Admin Panel
- Verify you're logged in
- Check `is_admin` field in database: `SELECT is_admin FROM users WHERE id = ?`
- Clear cache: `php artisan cache:clear`
- Check middleware is registered in `bootstrap/app.php`

### 403 Forbidden Error
- Your user account doesn't have `is_admin = true`
- Run the seeder or update database manually

### Routes Not Found
- Run `php artisan route:list` to verify routes are registered
- Check `routes/web.php` for backend route group

### Styling Issues
- Check if Bootstrap 5 CDN is accessible
- Verify internet connection for CDN resources
- Check browser console for CSS loading errors

## Future Enhancements

Potential features to add:
- Role-based permissions (super admin, moderator, etc.)
- Activity logs for admin actions
- Bulk operations (bulk delete, bulk status change)
- Export functionality (CSV, PDF reports)
- Advanced analytics and charts
- Email notifications for admin actions
- API endpoints for mobile admin app
