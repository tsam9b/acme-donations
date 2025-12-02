<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You for Your Donation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #FF2D20;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 5px 5px;
        }
        .donation-details {
            background-color: white;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #FF2D20;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #FF2D20;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Thank You for Your Donation!</h1>
    </div>

    <div class="content">
        <p>Dear {{ $donor->name }},</p>

        <p>Thank you so much for your generous donation! Your support makes a real difference and helps us continue our mission.</p>

        <div class="donation-details">
            <p><strong>Campaign:</strong> {{ $campaign->name }}</p>
            <p><strong>Amount:</strong> <span class="amount">${{ number_format($donation->amount, 2) }}</span></p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($donation->created_at)->format('F j, Y') }}</p>
        </div>

        <p>Your contribution will help us:</p>
        <p>{{ $campaign->description }}</p>

        <p>We are grateful for your support and commitment to making a positive impact.</p>

        <p>With heartfelt thanks,<br>
        <strong>The ACME Donations Team</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated receipt for your donation. Please keep this email for your records.</p>
        <p>&copy; {{ date('Y') }} ACME Corp. All rights reserved.</p>
    </div>
</body>
</html>

