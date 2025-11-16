# Notification System Flow Diagram

## System Architecture

```mermaid
graph TD
    A[User Completes Payment] --> B[ReservationService::confirm]
    B --> C[Create Booking Record]
    C --> D[BookingCreated Event Dispatched]
    D --> E[SendBookingNotification Listener]
    E --> F{User Exists?}
    F -->|Yes| G[Queue BookingConfirmed Notification]
    F -->|No| H[Skip Notification]
    G --> I[Queue Worker Processes Job]
    I --> J{Check Channels}
    J --> K[Email Channel]
    J --> L[SMS Channel]
    K --> M[BookingConfirmation Mailable]
    M --> N[Render Blade Template]
    N --> O[Send Email]
    L --> P{SMS Enabled?}
    P -->|Yes| Q[Send via Nexmo/Twilio]
    P -->|No| R[Log to File]
    O --> S[User Receives Email]
    Q --> T[User Receives SMS]
    R --> U[Admin Checks Logs]
```

## Component Interaction

```mermaid
sequenceDiagram
    participant User
    participant Controller
    participant ReservationService
    participant Database
    participant EventDispatcher
    participant Listener
    participant NotificationQueue
    participant MailChannel
    participant SMSChannel
    participant UserDevice

    User->>Controller: Complete Payment
    Controller->>ReservationService: confirm(reservationId, paymentId)
    ReservationService->>Database: Create Booking
    Database-->>ReservationService: Booking Created
    ReservationService->>EventDispatcher: dispatch(BookingCreated)
    EventDispatcher->>Listener: SendBookingNotification
    Listener->>NotificationQueue: Queue BookingConfirmed
    
    Note over NotificationQueue: Queue Worker Processes
    
    NotificationQueue->>MailChannel: toMail()
    MailChannel->>UserDevice: Email Sent
    NotificationQueue->>SMSChannel: toNexmo()
    
    alt SMS Enabled
        SMSChannel->>UserDevice: SMS Sent
    else SMS Disabled
        SMSChannel->>SMSChannel: Log Message
    end
    
    ReservationService-->>Controller: Return Booking
    Controller-->>User: Booking Confirmed (Page)
    UserDevice-->>User: Email Received
    UserDevice-->>User: SMS Received (if enabled)
```

## Data Flow

```mermaid
flowchart LR
    A[Booking Model] --> B[BookingCreated Event]
    B --> C[SendBookingNotification]
    C --> D[BookingConfirmed Notification]
    D --> E[toMail Method]
    D --> F[toNexmo Method]
    E --> G[BookingConfirmation Mailable]
    G --> H[Load Relationships]
    H --> I[booking.reservation.event]
    H --> J[booking.reservation.ticketType]
    H --> K[booking.items]
    I --> L[Email View Data]
    J --> L
    K --> L
    L --> M[booking-confirmation.blade.php]
    M --> N[Rendered HTML Email]
    F --> O[SMS Message String]
    O --> P{SMS Enabled?}
    P -->|Yes| Q[Nexmo/Twilio API]
    P -->|No| R[Laravel Log]
```

## File Structure

```
tbs/
├── app/
│   ├── Events/
│   │   └── BookingCreated.php ..................... Event dispatched on booking creation
│   ├── Listeners/
│   │   └── SendBookingNotification.php ............. Handles BookingCreated event
│   ├── Notifications/
│   │   └── BookingConfirmed.php .................... Multi-channel notification
│   ├── Mail/
│   │   └── BookingConfirmation.php ................. Email mailable class
│   ├── Providers/
│   │   └── EventServiceProvider.php ................ Registers events/listeners
│   └── Services/
│       └── Reservation/
│           └── ReservationService.php .............. Dispatches event (modified)
├── resources/
│   └── views/
│       └── emails/
│           └── booking-confirmation.blade.php ...... Email template
├── config/
│   └── ticketing.php ............................... SMS config added
├── bootstrap/
│   └── providers.php ............................... EventServiceProvider registered
└── tests/
    └── Feature/
        └── Notifications/
            └── BookingNotificationTest.php ......... Notification tests
```

## Event-Listener Registration

```mermaid
graph LR
    A[bootstrap/providers.php] --> B[EventServiceProvider]
    B --> C[protected $listen array]
    C --> D[BookingCreated::class]
    D --> E[SendBookingNotification::class]
    E --> F[Auto-registered by Laravel]
    F --> G[Event fires]
    G --> H[Listener executes]
```

## Notification Channel Decision

```mermaid
flowchart TD
    A[BookingConfirmed::via] --> B{Check Config}
    B --> C[Add 'mail' to channels]
    B --> D{SMS Enabled?}
    D -->|ticketing.sms_notifications_enabled = true| E[Add 'nexmo' to channels]
    D -->|ticketing.sms_notifications_enabled = false| F[Skip SMS]
    E --> G[Return channels array]
    F --> G
    C --> G
    G --> H{For each channel}
    H --> I[Call toMail]
    H --> J[Call toNexmo]
```

## Queue Processing

```mermaid
graph TD
    A[BookingCreated Event] --> B[Listener implements ShouldQueue]
    C[BookingConfirmed Notification] --> D[implements ShouldQueue]
    B --> E[Job added to Queue]
    D --> E
    E --> F{Queue Driver}
    F -->|database| G[jobs table]
    F -->|redis| H[Redis Queue]
    F -->|sync| I[Process Immediately]
    G --> J[Queue Worker]
    H --> J
    I --> K[Execute Now]
    J --> L[php artisan queue:work]
    L --> M[Process Jobs]
    M --> N[Send Notifications]
    K --> N
```

## Email Template Structure

```
┌─────────────────────────────────────┐
│  Header (Gradient Purple)          │
│  🎉 Booking Confirmed!              │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│  Content Section                    │
│  ┌───────────────────────────────┐  │
│  │ ✓ Payment Successful          │  │
│  └───────────────────────────────┘  │
│                                     │
│  ┌───────────────────────────────┐  │
│  │   Booking Code                │  │
│  │   BK-XXXXXXXXXX               │  │
│  └───────────────────────────────┘  │
│                                     │
│  ┌───────────────────────────────┐  │
│  │ Event Details                 │  │
│  │ 📅 Date & Time                │  │
│  │ 📍 Venue                      │  │
│  │ 📌 Location                   │  │
│  └───────────────────────────────┘  │
│                                     │
│  ┌───────────────────────────────┐  │
│  │ 🎫 Ticket Details             │  │
│  │ Ticket Type, Quantity, Price  │  │
│  │ Total Amount                  │  │
│  └───────────────────────────────┘  │
│                                     │
│  ┌───────────────────────────────┐  │
│  │     QR Code Placeholder       │  │
│  │         📱                    │  │
│  └───────────────────────────────┘  │
│                                     │
│  [View Booking Details Button]      │
│                                     │
│  ⚠️ Important Instructions          │
└─────────────────────────────────────┘
┌─────────────────────────────────────┐
│  Footer (Gray)                      │
│  Help • Support • Terms             │
│  © 2025 Ticket Booking System       │
└─────────────────────────────────────┘
```

## Testing Flow

```mermaid
graph TD
    A[Test: Notification System] --> B[Notification::fake]
    A --> C[Event::fake]
    B --> D[Execute Action]
    C --> D
    D --> E[service.confirm]
    E --> F[Create Booking]
    F --> G[Dispatch Event]
    G --> H[Trigger Listener]
    H --> I[Send Notification]
    I --> J[Notification::assertSentTo]
    G --> K[Event::assertDispatched]
    J --> L[Test Passes ✓]
    K --> L
```

## Configuration Flow

```mermaid
flowchart TD
    A[.env File] --> B[SMS_NOTIFICATIONS_ENABLED]
    B --> C[config/ticketing.php]
    C --> D[sms_notifications_enabled]
    D --> E[BookingConfirmed::via]
    E --> F{Check config value}
    F -->|true| G[Include nexmo channel]
    F -->|false| H[Email only]
    G --> I[toNexmo method called]
    H --> J[toMail method called]
    I --> K{Mock or Real?}
    K -->|Development| L[Log::info]
    K -->|Production| M[Nexmo API]
```

## Summary

This notification system provides:
- ✅ Automatic notifications on booking confirmation
- ✅ Professional email templates
- ✅ Optional SMS notifications
- ✅ Queue-based background processing
- ✅ Mock SMS for testing
- ✅ Event-driven architecture
- ✅ Easy configuration
- ✅ Comprehensive testing
