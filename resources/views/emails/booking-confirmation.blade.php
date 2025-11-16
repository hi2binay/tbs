<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 30px 20px;
        }
        .success-badge {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .booking-code {
            background-color: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .booking-code-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .booking-code-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            font-family: 'Courier New', monospace;
        }
        .event-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .event-details h2 {
            margin-top: 0;
            color: #667eea;
            font-size: 22px;
        }
        .detail-row {
            display: flex;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #6c757d;
            min-width: 120px;
        }
        .detail-value {
            color: #333;
        }
        .ticket-info {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .ticket-info h3 {
            margin-top: 0;
            color: #856404;
            font-size: 18px;
        }
        .price-total {
            text-align: right;
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
            margin-top: 15px;
        }
        .qr-placeholder {
            background-color: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
        }
        .qr-placeholder-text {
            color: #6c757d;
            font-size: 14px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
        .button {
            display: inline-block;
            background-color: #667eea;
            color: white !important;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Booking Confirmed!</h1>
        </div>
        
        <div class="content">
            <div class="success-badge">✓ Payment Successful</div>
            
            <p>Thank you for your booking! We're excited to see you at the event.</p>
            
            <div class="booking-code">
                <div class="booking-code-label">Booking Code</div>
                <div class="booking-code-value">{{ $booking->code }}</div>
            </div>
            
            <div class="event-details">
                <h2>{{ $event->name }}</h2>
                
                <div class="detail-row">
                    <div class="detail-label">📅 Date & Time:</div>
                    <div class="detail-value">{{ $event->starts_at->format('l, F j, Y \a\t g:i A') }}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">📍 Venue:</div>
                    <div class="detail-value">{{ $event->venue }}</div>
                </div>
                
                @if($event->location)
                <div class="detail-row">
                    <div class="detail-label">📌 Location:</div>
                    <div class="detail-value">{{ $event->location }}</div>
                </div>
                @endif
            </div>
            
            <div class="ticket-info">
                <h3>🎫 Ticket Details</h3>
                
                <div class="detail-row">
                    <div class="detail-label">Ticket Type:</div>
                    <div class="detail-value">{{ $ticketType->name }}</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Quantity:</div>
                    <div class="detail-value">{{ $reservation->quantity }} ticket(s)</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label">Unit Price:</div>
                    <div class="detail-value">{{ $ticketType->currency }} {{ number_format($ticketType->price_cents / 100, 2) }}</div>
                </div>
                
                <div class="price-total">
                    Total: {{ $booking->currency }} {{ number_format($booking->total_amount_cents / 100, 2) }}
                </div>
            </div>
            
            <div class="qr-placeholder">
                <div style="width: 200px; height: 200px; margin: 0 auto; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                    <span style="font-size: 48px;">📱</span>
                </div>
                <p class="qr-placeholder-text">QR Code will be generated here</p>
                <p class="qr-placeholder-text" style="margin-top: 10px; font-weight: 600;">Present this code at the venue entrance</p>
            </div>
            
            <div style="text-align: center;">
                <a href="#" class="button">View Booking Details</a>
            </div>
            
            <div style="margin-top: 30px; padding: 15px; background-color: #e7f3ff; border-radius: 6px;">
                <p style="margin: 0; font-size: 14px; color: #004085;">
                    <strong>Important:</strong> Please bring a printed copy of this email or show it on your mobile device at the venue entrance.
                </p>
            </div>
        </div>
        
        <div class="footer">
            <p>If you have any questions, please contact our support team.</p>
            <p>
                <a href="#">Help Center</a> • 
                <a href="#">Contact Support</a> • 
                <a href="#">Terms of Service</a>
            </p>
            <p style="margin-top: 15px; font-size: 12px;">
                © {{ date('Y') }} Ticket Booking System. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
