<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shipping Label - Shipment #{{ $shipment_id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            padding: 10px 0;
            border-bottom: 3px solid #981a1c;
            background: linear-gradient(to bottom, #ffffff, #f8f9fa);
        }
        .logo {
            height: 70px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #981a1c;
            margin-top: 5px;
        }
        .subtitle {
            font-size: 14px;
            color: #981a1c;
            font-weight: bold;
            margin-top: 5px;
        }
        .meta {
            font-size: 12px;
            color: #555;
            margin-top: 5px;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin: 20px 15px 0;
        }
        .left-column, .right-column {
            width: 48%;
        }
        .info-box {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background: #f8f9fa;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #981a1c;
            margin-bottom: 6px;
            border-bottom: 2px solid #981a1c;
            padding-bottom: 3px;
        }
        .info-box p {
            margin: 2px 0;
            font-size: 10px;
        }
        .label-details {
            margin: 20px 15px;
            border: 2px solid #981a1c;
            padding: 15px;
            border-radius: 5px;
            background: #fff;
        }
        .label-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 10px;
        }
        .label-item {
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        .label-item-label {
            font-weight: bold;
            color: #555;
            font-size: 10px;
        }
        .label-item-value {
            color: #333;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #981a1c;
            color: #fff;
            font-size: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .barcode-area {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            border: 2px dashed #981a1c;
            background: #f8f9fa;
        }
        .tracking-number {
            font-size: 18px;
            font-weight: bold;
            color: #981a1c;
            letter-spacing: 2px;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('images/logo.webp') }}" alt="EZ-Solutions" class="logo">
        <div class="title"> SHIPPING LABEL</div>
        <div class="subtitle">EZ-SOLUTIONS PORTAL GENERATED LABEL</div>
        <div class="meta">
            Shipment #{{ $shipment_id }} | Ref #: {{ $reference_no }} | Date: {{ $date }}
            @if($po_no && $po_no !== '—')
            | PO #: {{ $po_no }}
            @endif
        </div>
    </div>

    {{-- Tracking Number Barcode Area --}}
    @if($label['tracking_number'] && $label['tracking_number'] !== '—')
    <div class="barcode-area">
        <div style="font-size: 12px; color: #555; margin-bottom: 5px;">TRACKING NUMBER</div>
        <div class="tracking-number">{{ $label['tracking_number'] }}</div>
        <div style="font-size: 10px; color: #888; margin-top: 5px;">Scan or enter this number to track your shipment</div>
    </div>
    @endif

    {{-- Label Details --}}
    <div class="label-details">
        <div class="section-title">SHIPPING LABEL DETAILS</div>
        <div class="label-details-grid">
            <div class="label-item">
                <div class="label-item-label">Carrier</div>
                <div class="label-item-value">{{ $label['provider'] }}</div>
            </div>
            <div class="label-item">
                <div class="label-item-label">Service</div>
                <div class="label-item-value">{{ $label['service_name'] !== '—' ? $label['service_name'] : $label['service_code'] }}</div>
            </div>
            <div class="label-item">
                <div class="label-item-label">Label Format</div>
                <div class="label-item-value">{{ $label['label_format'] }}</div>
            </div>
            <div class="label-item">
                <div class="label-item-label">Payer</div>
                <div class="label-item-value">{{ $label['payer'] }}</div>
            </div>
            <div class="label-item">
                <div class="label-item-label">Account Number</div>
                <div class="label-item-value">{{ $label['account_number'] }}</div>
            </div>
            <div class="label-item">
                <div class="label-item-label">Signature Option</div>
                <div class="label-item-value">{{ $label['signature_option'] }}</div>
            </div>
            <div class="label-item">
                <div class="label-item-label">Saturday Delivery</div>
                <div class="label-item-value">{{ $label['saturday_delivery'] }}</div>
            </div>
            <div class="label-item">
                <div class="label-item-label">Declared Value</div>
                <div class="label-item-value">{{ number_format($label['declared_value_total'], 2) }}</div>
            </div>
            @if($label['pickup_date_time'] !== '—')
            <div class="label-item">
                <div class="label-item-label">Pickup Date & Time</div>
                <div class="label-item-value">{{ $label['pickup_date_time'] }}</div>
            </div>
            @endif
            @if($label['dropoff_date_time'] !== '—')
            <div class="label-item">
                <div class="label-item-label">Dropoff Date & Time</div>
                <div class="label-item-value">{{ $label['dropoff_date_time'] }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="info-section">
        <div class="left-column">
            <div class="info-box">
                <div class="section-title">SHIPPER </div>
                <p><strong>Name:</strong> {{ $shipper['name'] }}</p>
                <p><strong>Company:</strong> {{ $shipper['company'] }}</p>
                <p><strong>Phone:</strong> {{ $shipper['phone'] }}</p>
                <p><strong>Email:</strong> {{ $shipper['email'] }}</p>
                <p><strong>Address:</strong> {{ $shipper['address'] }}</p>
                @if($shipper['dock_hours'] && $shipper['dock_hours'] !== '—')
                <p><strong>Dock Hours:</strong> {{ $shipper['dock_hours'] }}</p>
                @endif
            </div>
        </div>

        <div class="right-column">
            <div class="info-box">
                <div class="section-title">CONSIGNEE </div>
                <p><strong>Name:</strong> {{ $recipient['name'] }}</p>
                <p><strong>Company:</strong> {{ $recipient['company'] }}</p>
                <p><strong>Phone:</strong> {{ $recipient['phone'] }}</p>
                <p><strong>Email:</strong> {{ $recipient['email'] }}</p>
                <p><strong>Address:</strong> {{ $recipient['address'] }}</p>
                @if($recipient['dock_hours'] && $recipient['dock_hours'] !== '—')
                <p><strong>Dock Hours:</strong> {{ $recipient['dock_hours'] }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Packages --}}
    @if(count($packages) > 0)
    <div class="section-title" style="margin: 20px 15px 5px;">PACKAGES</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Packaging</th>
                <th>Qty</th>
                <th>Class</th>
                <th>NMFC</th>
                <th>Commodity Name</th>
                
                <th>Single Weight</th>
                <th>Total Weight</th>
                <th>Dimensions</th>
                <th class="text-end">Declared Value</th>
            </tr>
        </thead>
        <tbody>
        @php
            $totalQty = 0;
            $totalWeight = 0;
        @endphp
        @foreach ($packages as $i => $pkg)
            @php
                $totalQty += ($pkg->qty ?? 1);
                $totalWeight += (float)( $pkg->qty * $pkg->weight ?? 0);
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $pkg->packaging ?? '—' }}</td>
                <td>{{ $pkg->qty ?? 1 }}</td>
                <td>{{ $pkg->package_class ?? '—' }}</td>
                <td>{{ $pkg->package_nmfc ?? '—' }}</td>
                <td>{{ $pkg->commodity_name ?? '—' }}</td>
                <td>{{ number_format($pkg->weight ?? 0, 3) }} {{ $pkg->weight_unit ?? 'kg' }}</td>
                <td>{{ number_format($pkg->qty * $pkg->weight ?? 0, 3) }} {{ $pkg->weight_unit ?? 'kg' }}</td>
                <td>
                    @php
                        $dims = array_filter([$pkg->length, $pkg->width, $pkg->height]);
                        echo $dims ? implode(' x ', $dims) . ' ' . ($pkg->dim_unit ?? 'cm') : '—';
                    @endphp
                </td>
                <td class="text-end">{{ number_format($pkg->declared_value ?? 0, 2) }}</td>
            </tr>
        @endforeach
        @if(count($packages) > 0)
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                <td><strong>{{ $totalQty }}</strong></td>
                <td colspan="3"></td> <td colspan="4"></td>
                <td><strong>{{ number_format($totalWeight, 2) }} </strong></td>
                <td colspan="2"></td> <td colspan="4"></td>
            </tr>
        @endif
        </tbody>
    </table>
    @endif

    {{-- Items --}}
    @if(count($items) > 0)
    <div class="section-title" style="margin: 20px 15px 5px;">ITEMS</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Code</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Unit</th>
                <th class="text-end">Unit Cost</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($items as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->product_code ?? '—' }}</td>
                <td>{{ $item->product->name ?? $item->product_code ?? '—' }}</td>
                <td>{{ number_format($item->qty ?? 0, 3) }}</td>
                <td>{{ $item->product_unit ?? '—' }}</td>
                <td class="text-end">{{ number_format($item->net_unit_cost ?? 0, 2) }}</td>
                <td class="text-end">{{ number_format($item->subtotal ?? 0, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif

    {{-- Additional Information --}}
    @if($meta['reference'] !== '—' || $meta['currency'] !== '—' || $meta['notes'] !== '—' || $rate['amount'])
    <div class="label-details" style="margin-top: 20px;">
        <div class="section-title">ADDITIONAL INFORMATION</div>
        <div class="label-details-grid">
            @if($meta['reference'] !== '—')
            <div class="label-item">
                <div class="label-item-label">Reference</div>
                <div class="label-item-value">{{ $meta['reference'] }}</div>
            </div>
            @endif
            @if($meta['currency'] !== '—')
            <div class="label-item">
                <div class="label-item-label">Currency</div>
                <div class="label-item-value">{{ $meta['currency'] }}</div>
            </div>
            @endif
            @if($rate['amount'])
            <!-- <div class="label-item">
                <div class="label-item-label">Estimated Rate</div>
                <div class="label-item-value">{{ ($rate['currency'] ? $rate['currency'] . ' ' : '') }}{{ number_format($rate['amount'], 2) }}</div>
            </div> -->   ya comment kraa hai mane 
            @endif
        </div>
        @if($meta['notes'] !== '—')
        <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 3px;">
            <div class="label-item-label">Notes</div>
            <div style="font-size: 10px; color: #333; white-space: pre-wrap;">{{ $meta['notes'] }}</div>
        </div>
        @endif
    </div>
    @endif

    <div class="footer">
        <div style="font-weight: bold; color: #981a1c; margin-bottom: 5px;">EZ-SOLUTIONS PORTAL SHIPPING LABEL</div>
        <div>This is a Portal-generated shipping label. Third-party carrier labels (FEDEX, DHL, UPS, Skynet, etc.) will be available separately when carrier APIs are integrated.</div>
        <div style="margin-top: 10px;">© {{ date('Y') }} EZ-Solutions.co All rights reserved.</div>
    </div>

</body>
</html>

