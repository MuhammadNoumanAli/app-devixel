<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'Carrier Details' }}</title>
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
      max-width: 650px;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
    }
    .header {
      background: linear-gradient(135deg, #7367f0 0%, #5e50ee 100%);
      padding: 32px 30px;
      text-align: center;
      color: #ffffff;
    }
    .header h1 {
      margin: 0;
      font-size: 22px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }
    .header p {
      margin: 8px 0 0;
      font-size: 14px;
      opacity: 0.9;
    }
    .content {
      padding: 30px;
    }
    .section-title {
      font-size: 15px;
      font-weight: 700;
      color: #7367f0;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-top: 24px;
      margin-bottom: 12px;
      padding-bottom: 6px;
      border-bottom: 2px solid #ebe9f1;
    }
    .section-title:first-child {
      margin-top: 0;
    }
    .info-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 16px;
    }
    .info-table td {
      padding: 9px 12px;
      font-size: 13.5px;
      line-height: 1.5;
      border-bottom: 1px solid #f0f0f4;
    }
    .info-label {
      width: 38%;
      color: #6e6b7b;
      font-weight: 600;
    }
    .info-value {
      width: 62%;
      color: #2b2b36;
      font-weight: 500;
    }
    .badge {
      display: inline-block;
      padding: 3px 8px;
      font-size: 12px;
      font-weight: 600;
      border-radius: 4px;
      background-color: #e8e7fc;
      color: #7367f0;
    }
    .badge-success {
      background-color: #d8f7e6;
      color: #28c76f;
    }
    .badge-info {
      background-color: #e0f4fc;
      color: #00cfe8;
    }
    .comment-box {
      background-color: #f8f8fb;
      border-left: 4px solid #7367f0;
      padding: 14px 16px;
      border-radius: 0 8px 8px 0;
      font-size: 13.5px;
      color: #4a4a5a;
      line-height: 1.6;
      margin-top: 8px;
    }
    .attachments-note {
      background-color: #fff9e6;
      border: 1px dashed #ff9f43;
      border-radius: 8px;
      padding: 14px 18px;
      margin-top: 22px;
      font-size: 13px;
      color: #7a5100;
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
      <!-- Header -->
      <div class="header">
        <h1>{{ $carrier_data->company_name ?? $carrier_data['company_name'] ?? 'Carrier Details' }}</h1>
        <p>{{ $title ?? 'Carrier Profile and Compliance Information' }}</p>
      </div>

      <!-- Content -->
      <div class="content">
        <!-- Section 1: Carrier Profile -->
        <div class="section-title">Carrier Profile</div>
        <table class="info-table">
          <tr>
            <td class="info-label">Company Name:</td>
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
            <td class="info-label">Email:</td>
            <td class="info-value"><a href="mailto:{{ $carrier_data->email ?? $carrier_data['email'] ?? '#' }}" style="color: #7367f0; text-decoration: none;">{{ $carrier_data->email ?? $carrier_data['email'] ?? 'N/A' }}</a></td>
          </tr>
          <tr>
            <td class="info-label">Contact Number:</td>
            <td class="info-value">{{ $carrier_data->number ?? $carrier_data['number'] ?? 'N/A' }}</td>
          </tr>
          @if(!empty($carrier_data['user_name']))
          <tr>
            <td class="info-label">Registered By (Agent):</td>
            <td class="info-value">{{ $carrier_data['user_name'] }}</td>
          </tr>
          @endif
        </table>

        <!-- Section 2: Equipment & Rates -->
        <div class="section-title">Equipment & Rate Details</div>
        <table class="info-table">
          <tr>
            <td class="info-label">Truck Type:</td>
            <td class="info-value">{{ $carrier_data['truck_type'] ?? ($carrier_data->truckType->name ?? 'N/A') }}</td>
          </tr>
          <tr>
            <td class="info-label">Truck Size:</td>
            <td class="info-value">{{ $carrier_data['truck_size'] ?? ($carrier_data->truckSize->name ?? 'N/A') }}</td>
          </tr>
          <tr>
            <td class="info-label">Maximum Weight:</td>
            <td class="info-value">{{ number_format((float) ($carrier_data->maximum_weight ?? $carrier_data['maximum_weight'] ?? 0)) }} lbs</td>
          </tr>
          <tr>
            <td class="info-label">Payment Type:</td>
            <td class="info-value">{{ $carrier_data['payment_type'] ?? ($carrier_data->paymentType->name ?? 'N/A') }}</td>
          </tr>
          <tr>
            <td class="info-label">Rate Structure:</td>
            <td class="info-value">
              @php
                $rateType = $carrier_data->percent_flat ?? $carrier_data['percent_flat'] ?? '';
              @endphp
              {{ $rateType === 'percentage' ? 'Percentage (%)' : ($rateType === 'flat_rate' ? 'Flat Rate ($)' : ucfirst($rateType)) }}
              — <strong>{{ $carrier_data->charge_type ?? $carrier_data['charge_type'] ?? 'N/A' }}</strong>
            </td>
          </tr>
          <tr>
            <td class="info-label">Expected RPM:</td>
            <td class="info-value"><span class="badge badge-success">${{ number_format((float)($carrier_data->rpm ?? $carrier_data['rpm'] ?? 0), 2) }} / mile</span></td>
          </tr>
        </table>

        <!-- Section 3: Operating Location & Preferred Zones -->
        <div class="section-title">Operating Address & Zones</div>
        <table class="info-table">
          <tr>
            <td class="info-label">Physical Address:</td>
            <td class="info-value">
              {{ $carrier_data->street_address ?? $carrier_data['street_address'] ?? '' }},
              {{ $carrier_data->city_name ?? $carrier_data['city_name'] ?? '' }},
              {{ $carrier_data['state_name'] ?? ($carrier_data->state->name ?? '') }}
              {{ $carrier_data->zip_code ?? $carrier_data['zip_code'] ?? '' }}
            </td>
          </tr>
          <tr>
            <td class="info-label">Operating Zones:</td>
            <td class="info-value">
              @php
                $allZones = $carrier_data->all_zones ?? $carrier_data['all_zones'] ?? 0;
                $activeZones = [];
                for ($i = 0; $i <= 9; $i++) {
                  $key = 'z'.$i;
                  if (!empty($carrier_data->$key) || !empty($carrier_data[$key])) {
                    $activeZones[] = 'Zone ' . $i;
                  }
                }
              @endphp
              @if($allZones)
                <span class="badge badge-info">Nationwide (All Zones)</span>
              @elseif(!empty($activeZones))
                {{ implode(', ', $activeZones) }}
              @else
                <span style="color: #a0a0b0;">No specific zones assigned</span>
              @endif
            </td>
          </tr>
        </table>

        <!-- Section 4: Dispatch Instructions / Comments -->
        @php
          $comment = $carrier_data->comment ?? $carrier_data['comment'] ?? '';
        @endphp
        @if(!empty($comment))
          <div class="section-title">Internal Notes & Instructions</div>
          <div class="comment-box">
            {{ $comment }}
          </div>
        @endif

        <!-- Attachments Note -->
        <div class="attachments-note">
          📎 <strong>Compliance Documents:</strong> Available documentation (MC Authority, W-9, Certificate of Insurance, NOA, VOID Cheque) has been attached directly to this email for your review.
        </div>
      </div>

      <!-- Footer -->
      <div class="footer">
        <p style="margin: 0 0 6px;">This email was generated automatically by <strong>Devixel Carrier Management System</strong>.</p>
        <p style="margin: 0;">&copy; {{ date('Y') }} Devixel. All rights reserved.</p>
      </div>
    </div>
  </div>
</body>
</html>
