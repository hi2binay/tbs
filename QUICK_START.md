# Quick Start Guide - Ticketing System Frontend

## ✅ What's Been Created

### 1. Bootstrap 5 Setup
- ✅ Installed Bootstrap 5.3.3 and Popper.js via npm
- ✅ Configured Vite to bundle Bootstrap
- ✅ Removed Tailwind CSS (replaced with Bootstrap)
- ✅ Added custom CSS for animations

### 2. Web Controllers
- ✅ **EventController** - List and show events
- ✅ **BookingController** - Create and view bookings
- ✅ **DashboardController** - User booking dashboard

### 3. Blade Views
- ✅ **layouts/app.blade.php** - Main layout with navbar and footer
- ✅ **welcome.blade.php** - Homepage with upcoming events
- ✅ **events/index.blade.php** - Event listing with search/filter
- ✅ **events/show.blade.php** - Event details with ticket types
- ✅ **bookings/create.blade.php** - Booking form with live total
- ✅ **bookings/show.blade.php** - Booking confirmation
- ✅ **dashboard/index.blade.php** - User bookings list
- ✅ **auth/login.blade.php** - Login form
- ✅ **auth/register.blade.php** - Registration form

### 4. Routes
- ✅ **web.php** - Public and authenticated routes
- ✅ **auth.php** - Authentication routes

### 5. Security
- ✅ **BookingPolicy** - Authorization for viewing bookings
- ✅ User model relationship to bookings

## 🚀 Getting Started

### Step 1: Ensure Database is Ready
Make sure you have:
- Created and run migrations
- Seeded the database with sample data

If not, run:
```bash
php artisan migrate:fresh --seed
```

### Step 2: Build Frontend Assets
```bash
npm run build
```

Or for development with hot reload:
```bash
npm run dev
```

### Step 3: Start the Application
```bash
composer dev
```

This runs the server, queue worker, and vite concurrently.

Or start manually:
```bash
php artisan serve
```

### Step 4: Access the Application
Open your browser to:
```
http://localhost:8000
```

## 📋 Test the Application

### As a Guest:
1. Visit homepage - see upcoming events
2. Browse all events (`/events`)
3. Search and filter events
4. View event details
5. Try to book - redirected to login

### As a Registered User:
1. Register a new account (`/register`)
2. Browse and select an event
3. Click "Book Tickets"
4. Select ticket types and quantities
5. Choose payment method
6. Confirm booking
7. View booking confirmation with QR code placeholder
8. Check "My Bookings" in dashboard

### Test Credentials (if seeded):
```
Email: admin@example.com
Password: password
```

## 🎨 Pages Available

| URL | Page | Auth Required |
|-----|------|---------------|
| `/` | Homepage with upcoming events | No |
| `/events` | All events with search/filter | No |
| `/events/{id}` | Event details | No |
| `/login` | Login form | No |
| `/register` | Registration form | No |
| `/events/{id}/bookings/create` | Booking form | Yes |
| `/bookings/{id}` | Booking confirmation | Yes (own booking) |
| `/dashboard` | User's bookings | Yes |

## 📁 Key Files

### Controllers
- `app/Http/Controllers/EventController.php`
- `app/Http/Controllers/BookingController.php`
- `app/Http/Controllers/DashboardController.php`

### Views
- `resources/views/layouts/app.blade.php` (main layout)
- `resources/views/welcome.blade.php` (homepage)
- `resources/views/events/` (event pages)
- `resources/views/bookings/` (booking pages)
- `resources/views/dashboard/` (user dashboard)
- `resources/views/auth/` (authentication)

### Routes
- `routes/web.php` (main routes)
- `routes/auth.php` (authentication)

### Assets
- `resources/css/app.css` (Bootstrap + custom styles)
- `resources/js/app.js` (Bootstrap bundle)

## ⚡ Features

### Event Browsing
- ✅ Responsive card grid layout
- ✅ Search by title/description
- ✅ Filter by category
- ✅ Sort by date or title
- ✅ Pagination

### Event Details
- ✅ Full event information
- ✅ Venue details
- ✅ Ticket types with pricing
- ✅ Availability status
- ✅ Book button

### Booking Process
- ✅ Multi-ticket type selection
- ✅ Quantity selection
- ✅ Real-time total calculation
- ✅ Payment method selection
- ✅ Form validation
- ✅ Confirmation page
- ✅ QR code placeholder

### User Dashboard
- ✅ All user bookings
- ✅ Booking status badges
- ✅ Payment status
- ✅ Quick access to details

### Authentication
- ✅ Login with remember me
- ✅ Registration with validation
- ✅ Logout functionality
- ✅ Protected routes

## 🔧 Common Tasks

### Add Sample Data
```bash
php artisan db:seed
```

### Clear Cache
```bash
php artisan cache:clear
php artisan view:clear
```

### Rebuild Assets
```bash
npm run build
```

### Run Tests
```bash
php artisan test
```

## 📝 Notes

- All forms include CSRF protection
- Authorization ensures users only see their own bookings
- Responsive design works on mobile, tablet, and desktop
- Flash messages show success/error feedback
- Pagination uses Bootstrap styling

## 🐛 Troubleshooting

### Assets not loading?
```bash
npm run build
php artisan view:clear
```

### Database errors?
```bash
php artisan migrate:fresh --seed
```

### Permission errors?
Check storage and cache directories are writable

## 📚 Additional Resources

- See `FRONTEND.md` for detailed documentation
- See `AGENTS.md` for project commands
- See `README.md` for project overview
