# Quick Setup Guide for Backend Admin Panel

## Step-by-Step Setup

### 1. Run the Migration
```bash
php artisan migrate
```

This adds the `is_admin` column to the users table.

### 2. Create Your First Admin User

**Method 1: Using the Seeder (Recommended)**
```bash
php artisan db:seed --class=AdminUserSeeder
```

This creates:
- Email: `admin@example.com`
- Password: `password`
- Admin: `true`

**Method 2: Make Existing User Admin**
```bash
php artisan tinker
```

Then run:
```php
$user = User::where('email', 'your@email.com')->first();
$user->is_admin = true;
$user->save();
exit
```

**Method 3: Direct Database Update**
```sql
UPDATE users SET is_admin = 1 WHERE email = 'your@email.com';
```

### 3. Access the Admin Panel

1. Log in to your application
2. Visit: `http://localhost/backend/dashboard`
3. You should see the admin dashboard with statistics

## Admin Panel Features

### Dashboard (`/backend/dashboard`)
- View total events, bookings, revenue, and users
- See recent bookings

### Events Management (`/backend/events`)
- Create, edit, and delete events
- Manage event details (name, venue, location, dates, status)
- View and manage ticket types directly from event edit page

### Ticket Types (`/backend/events/{event}/ticket-types`)
- Add ticket types to events
- Set prices and quantities
- Edit or delete ticket types

### Users Management (`/backend/users`)
- View all users and their booking counts
- View individual user profiles and booking history
- Make users admins
- Edit user details
- Delete users (except yourself)

### Bookings Management (`/backend/bookings`)
- View all bookings with filters
- Search by booking code, customer name, or email
- Filter by status (pending, confirmed, cancelled, refunded)
- View detailed booking information
- Refund bookings

## Important Notes

1. **Security**: Only users with `is_admin = true` can access the admin panel
2. **Self-Protection**: You cannot delete yourself or remove your own admin privileges
3. **Destructive Actions**: Delete operations show confirmation dialogs
4. **Bootstrap 5**: The admin panel uses Bootstrap 5 via CDN

## Troubleshooting

### "403 Forbidden" Error
Your user doesn't have admin access. Run:
```bash
php artisan tinker
User::find(YOUR_USER_ID)->update(['is_admin' => true]);
```

### Routes Not Working
Clear the route cache:
```bash
php artisan route:clear
php artisan cache:clear
```

### Database Not Connected
Make sure your `.env` file has correct database credentials.

## Default Admin Credentials (if using seeder)

```
Email: admin@example.com
Password: password
```

**⚠️ CHANGE THESE IN PRODUCTION!**

## Navigation

The admin panel has a sidebar with links to:
- Dashboard
- Events
- Bookings
- Users
- Back to Site (returns to main site)

Top bar shows:
- Your name
- Logout button

## Next Steps

1. ✅ Run migration
2. ✅ Create admin user
3. ✅ Login and access `/backend/dashboard`
4. ✅ Create your first event
5. ✅ Add ticket types to the event
6. ✅ Publish the event
7. ✅ Monitor bookings as they come in

Enjoy your new admin panel! 🎉
