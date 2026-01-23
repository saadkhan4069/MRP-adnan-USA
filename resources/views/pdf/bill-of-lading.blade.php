<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill of Lading - Shipment #{{ $shipment_id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #000;
            /* font-size: 9px; */
            margin: 0;
            padding: 10px;
        }
        .header-bar {
            background-color: #808080;
            color: #fff;
            padding: 8px 10px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .header-content {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .header-left {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            text-align: right;
        }
        .bol-number p {
            font-weight: bold;
            font-size: 30px;
            margin-bottom: 5px;
        }
        .logo-container {
            margin-top: 5px;
        }
        .logo-container img {
            max-height: 80px;
            max-width: 140px;
        }
        .logo-left {
            text-align: left;
        }
        .logo-right {
            text-align: right;
        }
        .three-column {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .three-column > div {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            padding: 5px;
            border: 1px solid #000;
        }
        .column-title {
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 4px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
            text-transform: uppercase;
        }
        .info-row {
            margin-bottom: 3px;
            font-size: 9px;
            line-height: 1.3;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            min-width: 65px;
        }
        .shipment-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 7px;
        }
        .shipment-table th,
        .shipment-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: center;
        }
        .shipment-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .shipment-table td {
            font-size: 7px;
        }
        .footer {
            margin-top: 15px;
            font-size: 8px;
        }
        .pro-number {
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 11px;
        }
        .freight-terms {
            margin-bottom: 10px;
            font-weight: bold;
        }
        .signature-block {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .signature-block > div {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            padding: 5px;
            border: 1px solid #000;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 25px;
            font-size: 8px;
        }
        .signature-text {
            font-size: 6.5px;
            line-height: 1.2;
        }
        .signature-mark {
            margin-top: 15px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .special-instructions {
            margin-top: 5px;
            font-size: 7px;
            min-height: 20px;
        }
        .reference-section {
            margin-top: 5px;
            font-size: 7px;
        }
        .reference-row {
            margin-bottom: 2px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .barcode-container {
            text-align: center;
            margin-top: 5px;
        }
        .barcode-container img {
            max-width: 200px;
            height: auto;
        }
    </style>
</head>
<body>
    {{-- Header Bar --}}
    <div class="header-bar">STRAIGHT BILL OF LADING (NON-NEGOTIABLE)</div>
    
    {{-- Header Content with Logos and BOL Number --}}
    <div class="header-content">
        <div class="header-left">
            @if(!empty($logos['company_logo']))
            <div class="logo-container logo-left">
                <img src="{{ public_path('images/logo.webp') }}" alt="Company Logo">
            </div>
            @endif
        </div>
        <div class="header-right">
            <div class="bol-number">BOL Number: <p>{{ $bol_number }}</p></div>
            @if(!empty($logos['third_party_logo']))
            <div class="logo-container logo-right">
                <img src="{{ $logos['third_party_logo'] }}" alt="Third Party Logo">
            </div>
            @endif
        </div>
    </div>

    {{-- Three Column Layout: Shipper | Third Party | Consignee --}}
    <div class="three-column">
        {{-- Shipper Column (Left) --}}
        <div>
            <div class="column-title">CARRIER</div>
            <div class="info-row">
                @if(!empty($carrier['name']))
                <div><strong>{{ $carrier['name'] }}</strong></div>
                @endif
                @if(!empty($carrier['phone']))
                <div>Phone: {{ $carrier['phone'] }}</div>
                @endif
                @if(!empty($carrier['address']))
                <div>{{ $carrier['address'] }}</div>
                @endif
            </div>

            <div class="column-title" style="margin-top: 6px;">PICKUP SCHEDULE</div>
            <div class="info-row">
                <div><span class="info-label">Pickup Date:</span> {{ $pickup_date }}</div>
                <div><span class="info-label">Ready Time:</span> {{ $ready_time }}</div>
                <div><span class="info-label">Closing Time:</span> {{ $closing_time }}</div>
            </div>

            <div class="column-title" style="margin-top: 6px;">SHIPPER</div>
            <div class="info-row">
                <div><strong>{{ $shipper['company'] ?: $shipper['name'] }}</strong></div>
                <div>{{ $shipper['address'] }}</div>
                @if($shipper['city'] && $shipper['state'] && $shipper['zipcode'])
                <div>{{ $shipper['city'] }}, {{ $shipper['state'] }} {{ $shipper['zipcode'] }}</div>
                @endif
                @if($shipper['contact'])
                <div>Phone: {{ $shipper['contact'] }}</div>
                @endif
                @if($shipper['contact_person'] && $shipper['contact_person'] != '—')
                <div>Contact: {{ $shipper['contact_person'] }}</div>
                @endif
            </div>

            <div class="info-row" style="margin-top: 4px;">
                <div><span class="info-label">Pickup Hours:</span> {{ $shipper['pickup_hours'] }}</div>
                @if($shipper['lunch_hour'] && $shipper['lunch_hour'] != '—')
                <div><span class="info-label">Lunch Hour:</span> {{ $shipper['lunch_hour'] }}</div>
                @endif
            </div>

            <div class="column-title" style="margin-top: 6px;">REFERENCE INFORMATION</div>
            <div class="reference-section">
                <div class="reference-row"><span class="info-label">Shipper#:</span> {{ $reference_info['shipper_number'] ?: '—' }}</div>
                <div class="reference-row"><span class="info-label">PO#:</span> {{ $reference_info['po_number'] ?: '—' }}</div>
                <div class="reference-row"><span class="info-label">Quote#:</span> {{ $reference_info['quote_number'] ?: '—' }}</div>
                <div class="reference-row"><span class="info-label">Customer#:</span> {{ $reference_info['customer_number'] ?: '—' }}</div>
                <div class="reference-row"><span class="info-label">BOL#:</span> {{ $reference_info['bol_number'] }}</div>
            </div>

            <div class="column-title" style="margin-top: 6px;">SPECIAL INSTRUCTIONS</div>
            <div class="special-instructions">
                @if($shipper['pickup_delivery_instructions'] && $shipper['pickup_delivery_instructions'] != '—')
                    {{ $shipper['pickup_delivery_instructions'] }}
                @else
                    —
                @endif
            </div>

            @if(!empty($service_type))
            <div class="info-row" style="margin-top: 4px;">
                <div><span class="info-label">Service Type:</span> {{ $service_type }}</div>
            </div>
            @endif

            @if($shipper['appointment'] && $shipper['appointment'] != '—')
            <div class="info-row">
                <div><span class="info-label">Appointment:</span> {{ $shipper['appointment'] }}</div>
            </div>
            @endif

            @if($shipper['accessorial'] && $shipper['accessorial'] != '—')
            <div class="info-row">
                <div><span class="info-label">Accessorial:</span> {{ $shipper['accessorial'] }}</div>
            </div>
            @endif
        </div>

        {{-- Third Party Bill-To Column (Center) --}}
        <div>
            <div class="column-title">THIRD PARTY BILL-TO</div>
            <div class="info-row">
                @if(!empty($third_party['name']))
                <div><strong>Carrier: {{ $third_party['name'] }}</strong></div>
                @endif
                @if(!empty($third_party['address']))
                <div>{{ $third_party['address'] }}</div>
                @endif
            </div>
            @if(!empty($third_party['account']))
            <div class="info-row" style="margin-top: 4px;">
                <div><span class="info-label">Account:</span> {{ $third_party['account'] }}</div>
            </div>
            @endif
        </div>

        {{-- Consignee Column (Right) --}}
        <div>
            <div class="column-title">CONSIGNEE</div>
            <div class="info-row">
                <div><strong>{{ $consignee['company'] ?: $consignee['name'] }}</strong></div>
                <div>{{ $consignee['address'] }}</div>
                @if($consignee['city'] && $consignee['state'] && $consignee['zipcode'])
                <div>{{ $consignee['city'] }}, {{ $consignee['state'] }} {{ $consignee['zipcode'] }}</div>
                @endif
                @if($consignee['contact'])
                <div>Phone: {{ $consignee['contact'] }}</div>
                @endif
            </div>

            <div class="info-row" style="margin-top: 4px;">
                <div><span class="info-label">Delivery Hours:</span> 
                    @if($consignee['delivery_hours'] && $consignee['delivery_hours'] != '—')
                        {{ $consignee['delivery_hours'] }}
                    @else
                        —
                    @endif
                </div>
                @if($consignee['lunch_hour'] && $consignee['lunch_hour'] != '—')
                <div><span class="info-label">Lunch Hour:</span> {{ $consignee['lunch_hour'] }}</div>
                @endif
            </div>

            <div class="info-row" style="margin-top: 4px;">
                <div><span class="info-label">Ref 1:</span> —</div>
            </div>

            @if($consignee['pickup_delivery_instructions'] && $consignee['pickup_delivery_instructions'] != '—')
            <div class="column-title" style="margin-top: 6px;">SPECIAL INSTRUCTIONS</div>
            <div class="special-instructions">
                {{ $consignee['pickup_delivery_instructions'] }}
            </div>
            @endif

            @if($consignee['appointment'] && $consignee['appointment'] != '—')
            <div class="info-row" style="margin-top: 4px;">
                <div><span class="info-label">Appointment:</span> {{ $consignee['appointment'] }}</div>
            </div>
            @endif

            @if($consignee['accessorial'] && $consignee['accessorial'] != '—')
            <div class="info-row">
                <div><span class="info-label">Accessorial:</span> {{ $consignee['accessorial'] }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Shipment Details Table --}}
    <table class="shipment-table">
        <thead>
            <tr>
                <th style="width: 5%;">Units</th>
                <th style="width: 5%;">Piece</th>
                <th style="width: 8%;">Type</th>
                <th style="width: 3%;">Haz</th>
                <th style="width: 20%;">Commodity Description</th>
                <th style="width: 8%;">Ref#</th>
                <th style="width: 5%;">L</th>
                <th style="width: 5%;">W</th>
                <th style="width: 5%;">H</th>
                <th style="width: 5%;">Class</th>
                <th style="width: 8%;">NMFC</th>
                <th style="width: 8%;">Weight</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalUnits = 0;
                $totalWeight = 0;
            @endphp
            @foreach($packages as $package)
                @php
                    $units = $package->qty ?? 1;
                    $totalUnits += $units;
                    $weight = ($package->weight ?? 0) * $units;
                    $totalWeight += $weight;
                    $commodityName = $package->commodity_name ?? 'Wine';
                    $type = $package->packaging ?? 'Pallet';
                @endphp
                <tr>
                    <td>{{ $units }}</td>
                    <td>{{ $units }}</td>
                    <td>{{ $type }}s</td>
                    <td></td>
                    <td>{{ $commodityName }}</td>
                    <td></td>
                    <td>{{ $package->length ?? '—' }}</td>
                    <td>{{ $package->width ?? '—' }}</td>
                    <td>{{ $package->height ?? '—' }}</td>
                    <td>{{ $package->package_class ?? '—' }}</td>
                    <td>{{ $package->package_nmfc ?? '—' }}</td>
                    <td>{{ number_format($weight, 1) }}</td>
                </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f0f0f0;">
                <td colspan="11" class="text-right"><strong>Total Units: {{ $totalUnits }}</strong></td>
                <td><strong>Total Weight: {{ number_format($totalWeight, 1) }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Footer Section --}}
    <div class="footer">
        <div class="pro-number">
            <div style="margin-bottom: 5px;">Pro Number: {{ $pro_number }}</div>
            {{-- Barcode for BOL Number --}}
            <div class="barcode-container">
                @php
                    try {
                        if (class_exists('\Picqer\Barcode\BarcodeGeneratorPNG')) {
                            $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                            $barcode = base64_encode($generator->getBarcode($bol_number, $generator::TYPE_CODE_128));
                            echo '<img src="data:image/png;base64,' . $barcode . '" alt="Barcode">';
                        } elseif (class_exists('\DNS1D')) {
                            echo '<img src="data:image/png;base64,' . \DNS1D::getBarcodePNG($bol_number, 'C128') . '" alt="Barcode">';
                        }
                    } catch (\Exception $e) {
                        // Barcode generation failed, skip it
                    }
                @endphp
            </div>
        </div>

        <div class="freight-terms">
            <strong>Freight Charge Terms:</strong><br>
            {{ $freight_charge_terms }}
        </div>

        {{-- Signature Blocks --}}
        <div class="signature-block">
            <div>
                <div class="signature-title">Shipper Signature / Date</div>
                <div class="signature-text">
                    This is to certify that the above named materials are properly classified, described, packaged, marked and labeled, and are in proper condition for transportation according to the applicable regulations of the U.S. Department of Transportation.
                </div>
                <div class="signature-mark">X</div>
            </div>
            <div>
                <div class="signature-title">Carrier Signature / Date</div>
                <div class="signature-text">
                    Carrier acknowledges receipt of packages and required placards. Carrier certifies emergency response information was made available and/or carrier has the U.S. Department of Transportation emergency response guidebook or equivalent documentation in the vehicle. Property described above is received in good order, except as noted.
                </div>
                <div class="signature-mark">X</div>
            </div>
            <div>
                <div class="signature-title">Consignee Signature / Date</div>
                <div class="signature-text">
                    RECEIVED, subject to individually determined rates or contracts that have been agreed upon in writing between the carrier and shipper, if applicable, otherwise to the rates, classifications and rules that have been established by the carrier and are available to the shipper, on request, and to all applicable state and federal regulations.
                </div>
                <div class="signature-mark">X</div>
            </div>
        </div>
    </div>
</body>
</html>
