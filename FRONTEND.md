# Frontend Layer Documentation

## Overview
The ticketing system frontend is built with **Bootstrap 5** and Laravel Blade templating.

## Installation
Bootstrap 5 and Popper.js are already installed via npm:
```bash
npm install
npm run build  # Production build
npm run dev    # Development with hot reload
```

## Structure

### Controllers
Located in `app/Http/Controllers/`:

#### EventController
- `index()` - List all events with search, filter by category, and sorting
- `show($event)` - Display event details with ticket types

#### BookingController
- `create($event)` - Show booking form for an event
- `store($event)` - Process booking and create tickets
- `show($booking)` - Display booking confirmation with QR code placeholder

#### DashboardController
- `index()` - Show user's booking history

### Views
Located in `resources/views/`:

#### layouts/app.blade.php
Main layout with:
- Bootstrap navbar with authentication links
- Flash message display (success/error)
- Footer
- Responsive navigation

#### welcome.blade.php
Homepage featuring:
- Hero section
- 6 upcoming events in card grid
- Link to all events

#### events/index.blade.php
Events listing page with:
- Search bar
- Category filter
- Sort options (date, title)
- Responsive card grid
- Pagination

#### events/show.blade.php
Event details page with:
- Event image
- Full event information
- Venue details
- Ticket types with availability
- Book button (auth required)

#### bookings/create.blade.php
Booking form with:
- Event summary
- Ticket type selection with quantity inputs
- Real-time total calculation (JavaScript)
- Payment method selection
- Form validation

#### bookings/show.blade.php
Booking confirmation with:
- Success message
- Booking details and status
- Event information
- Ticket breakdown
- QR code placeholder
- Links to dashboard and events

#### dashboard/index.blade.php
User dashboard showing:
- All user bookings
- Booking status badges
- Event details
- Pagination

#### auth/login.blade.php & auth/register.blade.php
Authentication forms with:
- Bootstrap styled inputs
- Validation error display
- Remember me option (login)
- Password confirmation (register)

### Routes
Defined in `routes/web.php`:

```php
GET  /                              # Welcome page
GET  /events                        # Events listing
GET  /events/{event}                # Event details
GET  /events/{event}/bookings/create   # Booking form (auth)
POST /events/{event}/bookings          # Store booking (auth)
GET  /bookings/{booking}            # Booking details (auth)
GET  /dashboard                     # User dashboard (auth)
```

Authentication routes in `routes/auth.php`:
```php
GET  /login                         # Login form
POST /login                         # Process login
GET  /register                      # Registration form
POST /register                      # Process registration
POST /logout                        # Logout (auth)
```

## Bootstrap Features Used

### Components
- **Navbar** - Responsive navigation with dropdown
- **Cards** - Event and booking displays
- **Forms** - All input forms with validation
- **Badges** - Status indicators
- **Alerts** - Flash messages
- **Pagination** - Laravel pagination styled with Bootstrap

### Layout
- **Grid System** - Responsive layouts (col-md-*, col-lg-*)
- **Flexbox Utilities** - Alignment and spacing
- **Spacing** - Margin and padding utilities (mb-*, py-*, etc.)

### Styling
- **Colors** - Primary, success, danger, warning, info
- **Typography** - Headers, text utilities
- **Shadows** - Card shadows with hover effects

## Custom CSS
Additional styles in `resources/css/app.css`:
- Sticky footer layout
- Card hover animations
- Page transitions

## JavaScript
In `resources/js/app.js`:
- Bootstrap bundle import
- Interactive components (dropdowns, modals)

Inline JavaScript in `bookings/create.blade.php`:
- Real-time booking total calculation

## Security
- CSRF protection on all forms
- Authorization via BookingPolicy (users can only view their own bookings)
- Authentication middleware on protected routes
- Validation on all form submissions

## Responsive Design
All views are mobile-friendly:
- Collapsible navbar on mobile
- Responsive grid layouts
- Touch-friendly buttons and forms
- Optimized images

## Key Features

### Event Browsing
- Search by title/description
- Filter by category
- Sort by date or title
- Paginated results

### Booking Flow
1. Browse events
2. View event details
3. Select ticket types and quantities
4. Choose payment method
5. Confirm booking
6. View confirmation with QR code

### User Dashboard
- View all bookings
- See booking and payment status
- Quick access to booking details

## Testing the Frontend

1. Start the development server:
```bash
composer dev
```

2. Access the application at `http://localhost:8000`

3. Test flows:
   - Browse events without login
   - Register a new account
   - Book tickets for an event
   - View booking in dashboard
   - Logout and login

## Next Steps

To enhance the frontend:
1. Add QR code generation library (e.g., SimpleSoftwareIO/simple-qrcode)
2. Implement payment gateway integration
3. Add event image upload functionality
4. Create admin panel for event management
5. Add email notifications with booking confirmations
6. Implement booking cancellation feature
7. Add event calendar view
8. Create event favoriting/wishlist
