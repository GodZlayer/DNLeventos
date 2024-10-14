<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Invoice</title>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .ticket-details {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
        }
        .ticket-details h3 {
            margin-top: 0;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="ticket-details">
        <h3>Ticket for Event: {{ $eventInfo->title ?? 'N/A' }}</h3>
        <p><strong>Booking ID:</strong> {{ $ticketInfo['booking_id'] ?? 'N/A' }}</p>
        <p><strong>Ticket ID:</strong> {{ $ticketInfo['ticket_id'] ?? 'N/A' }}</p>
        <div class="text-center">
            <img src="{{ public_path('assets/admin/qrcodes/' . $ticketInfo['booking_id'] . '.svg') }}" alt="QR Code" style="width: 150px; height: 150px;">
        </div>
        <p>Please present this ticket at the entrance.</p>
    </div>
</body>
</html>
