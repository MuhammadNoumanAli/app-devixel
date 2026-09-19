<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'Carrier Reassigned' }}</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      background-color: #f4f5fa;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      color: #383842;
      -webkit-font-smoothing: antialiased;
    }
    .wrapper {
      width: 100%;
      table-layout: fixed;
      background-color: #f4f5fa;
      padding: 30px 0;
    }
    .main {
      background-color: #ffffff;
      margin: 0 auto;
      max-width: 600px;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
    }
    .header {
      background: linear-gradient(135deg, #ea5455 0%, #c43d3e 100%);
      padding: 28px 30px;
      text-align: center;
      color: #ffffff;
    }
    .header h1 {
      margin: 0;
      font-size: 20px;
      font-weight: 700;
    }
    .header p {
      margin: 6px 0 0;
      font-size: 13px;
      opacity: 0.9;
    }
    .content {
      padding: 30px;
    }
    .alert-box {
      background-color: #fff0f0;
      border: 1px solid #ffccd0;
      border-radius: 8px;
      padding: 16px 20px;
      margin-bottom: 24px;
      font-size: 14px;
      color: #b02a37;
      line-height: 1.5;
    }
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    .info-table td {
      padding: 10px 12px;
      font-size: 13.5px;
      border-bottom: 1px solid #f0f0f4;
    }
    .info-label {
      width: 40%;
      color: #6e6b7b;
      font-weight: 600;
    }
    .info-value {
      width: 60%;
      color: #2b2b36;
      font-weight: 500;
    }
    .badge {
      display: inline-block;
      padding: 3px 8px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 4px;
      background-color: #f8d7da;
      color: #721c24;
    }
    .footer {
      background-color: #f8f8fb;
      padding: 20px 30px;
      text-align: center;
      font-size: 12px;
      color: #a0a0b0;
      border-top: 1px solid #ebe9f1;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="main">
      <div class="header">
        <h1>Carrier Assignment Notice</h1>
        <p>Notification of Carrier Status Change</p>
      </div>

      <div class="content">
        <div class="alert-box">
          <strong>Notice:</strong> The carrier profile below has been unassigned or reassigned in the Devixel dispatch system.
        </div>

        <table class="info-table">
          <tr>
            <td class="info-label">Carrier Company:</td>
            <td class="info-value"><strong>{{ $carrier_data->company_name ?? $carrier_data['company_name'] ?? 'N/A' }}</strong></td>
          </tr>
          <tr>
            <td class="info-label">Contact Person:</td>
            <td class="info-value">{{ $carrier_data->name ?? $carrier_data['name'] ?? 'N/A' }}</td>
          </tr>
          <tr>
            <td class="info-label">MC Number:</td>
            <td class="info-value"><span class="badge">{{ $carrier_data->mc_number ?? $carrier_data['mc_number'] ?? 'N/A' }}</span></td>
          </tr>
          <tr>
            <td class="info-label">DOT Number:</td>
            <td class="info-value">{{ $carrier_data->dot ?? $carrier_data['dot'] ?? 'Not Provided' }}</td>
          </tr>
          <tr>
            <td class="info-label">Contact Phone:</td>
            <td class="info-value">{{ $carrier_data->number ?? $carrier_data['number'] ?? 'N/A' }}</td>
          </tr>
        </table>

        <p style="font-size: 13px; color: #6e6b7b; line-height: 1.6; margin: 0;">
          If you believe this reassignment was made in error, please contact your team manager or administration.
        </p>
      </div>

      <div class="footer">
        <p style="margin: 0 0 6px;">This email was generated automatically by <strong>Devixel Carrier Management System</strong>.</p>
        <p style="margin: 0;">&copy; {{ date('Y') }} Devixel. All rights reserved.</p>
      </div>
    </div>
  </div>
</body>
</html>
