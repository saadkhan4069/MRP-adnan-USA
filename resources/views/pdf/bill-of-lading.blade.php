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
            /* color: #ff0000; */
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
            padding-right: 15px;
        }
        .header-left-inner {
            display: table;
            width: 100%;
        }
        .header-left-logo {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }
        .header-left-info {
            display: table-cell;
            width: 60%;
            vertical-align: top;
            padding-left: 10px;
        }
        .header-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-left: 15px;
            text-align: right;
        }
        .bol-number {
            margin-bottom: 10px;
        }
        .bol-number-label {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .bol-number-value {
            font-weight: bold;
            font-size: 36px;
            margin-bottom: 5px;
        }
        .logo-container {
            margin-bottom: 8px;
        }
        .logo-container img {
            max-height: 90px;
            max-width: 250px;
        }
        .logo-left {
            text-align: left;
        }
        .logo-right {
            text-align: right;
        }
        .company-info {
            font-size: 9px;
            line-height: 1.5;
        }
        .third-party-info {
            font-size: 9px;
            line-height: 1.5;
            margin-top: 10px;
        }
        .two-column {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .two-column > div {
            display: table-cell;
            width: 50%;
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
        .footer-row {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        .footer-row > div {
            display: table-cell;
            vertical-align: top;
            padding: 5px;
            border: 1px solid #000;
        }
        .pro-number-box {
            width: 25%;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }
        .signature-block {
            width: 25%;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 25px;
            font-size: 8px;
        }
        .signature-text {
            font-size: 9px;
            /* line-height: 1.2; */
        }
        .signature-mark {
            /* margin-top: 15px; */
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .special-instructions {
            margin-top: 5px;
            font-size: 10px;
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
            /* text-align: center; */
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
    
    {{-- Header Content with Company Info and BOL Number --}}
    <div class="header-content">
        <div class="header-left">
            <div class="header-left-inner">
                <div class="header-left-logo">
                    {{-- Company Logo (Ez-solution) --}}
                    <div class="logo-container logo-left">
                    <img src="{{ public_path('images/logo.webp') }}" alt="Company Logo">
                    </div>
                    
                    {{-- Company Address and Contact --}}
                    <div class="company-info" style="margin-top: 8px; font-size: 9px; line-height: 1.5;">
                        @if($company_address)
                        <div>{{ $company_address }}</div>
                        @endif
                        @if($company_phone)
                        <div>Phone: {{ $company_phone }}</div>
                        @endif
                        @if($company_fax)
                        <div>Fax: {{ $company_fax }}</div>
                        @endif
                    </div>
                </div>
                
                <div class="header-left-info">
                    {{-- Pickup Schedule Info --}}
                    <div style="font-size: 10px; line-height: 1.6; font-weight: normal;">
                        @if(!empty($carrier['name']))
                        <div style="font-size: 11px; font-weight: bold; margin-bottom: 3px;">{{ $carrier['name'] }}</div>
                        @endif
                        @if(!empty($carrier['phone']))
                        <div style="font-size: 10px; margin-bottom: 5px;">{{ $carrier['phone'] }}</div>
                        @endif
                        @if($pickup_date)
                        <div style="margin-top: 5px; font-size: 10px;"><strong>Pickup Date:</strong> {{ $pickup_date }}</div>
                        @endif
                        @if($ready_time)
                        <div style="font-size: 10px;"><strong>Ready Time:</strong> {{ $ready_time }}</div>
                        @endif
                        @if($closing_time)
                        <div style="font-size: 10px;"><strong>Closing Time:</strong> {{ $closing_time }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <div class="header-right">
            {{-- Third Party Bill-To-Information --}}
            <div class="third-party-info">
                <div style="font-weight: bold; margin-bottom: 5px;">Third Party Bill-To-Information</div>
                @if(!empty($third_party['name']))
                <div><strong>Carrier:</strong> {{ $third_party['name'] }}</div>
                @endif
                @if($company_address)
                <div>{{ $company_address }}</div>
                @endif
                @if(!empty($third_party['account']))
                <div><strong>Account:</strong> {{ $third_party['account'] }}</div>
                @endif
            </div>
            
            {{-- Third Party Logo (Right side) --}}
            @if(!empty($logos['third_party_logo']))
            <div class="logo-container logo-right" style="margin-top: 10px;">
                <img src="{{ $logos['third_party_logo'] }}" alt="Third Party Logo">
            </div>
            @endif
            
            {{-- BOL Number with Barcode (Rightmost) --}}
            <div class="bol-number" style="margin-top: 10px;">
                <div class="bol-number-label">BOL Number:</div>
                <div class="bol-number-value">{{ $bol_number }}</div>
                {{-- Barcode for BOL Number --}}
                <div class="barcode-container">
                    @php
                        try {
                            if (class_exists('\Picqer\Barcode\BarcodeGeneratorPNG')) {
                                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                                $barcode = base64_encode($generator->getBarcode($bol_number, $generator::TYPE_CODE_128));
                                echo '<img src="data:image/png;base64,' . $barcode . '" alt="Barcode" style="max-width: 200px; height: auto;">';
                            } elseif (class_exists('\DNS1D')) {
                                echo '<img src="data:image/png;base64,' . \DNS1D::getBarcodePNG($bol_number, 'C128') . '" alt="Barcode" style="max-width: 200px; height: auto;">';
                            }
                        } catch (\Exception $e) {
                            // Barcode generation failed, skip it
                        }
                    @endphp
                </div>
            </div>
        </div>
    </div>
    

    {{-- Two Column Layout: Shipper | Consignee --}}
    <div class="two-column">
        {{-- Shipper Column (Left) --}}
        <div>
            <div class="column-title">SHIPPER</div>
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
                <td><strong>Total Weight: {{ number_format($totalWeight, 1) }} {{ $package->weight_unit ?? 'kg' }}</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Footer Section --}}
    <div class="footer">
        {{-- Footer Row: 4 Boxes in One Row --}}
        <div class="footer-row">
            {{-- Box 1: Pro Number --}}
            <div class="pro-number-box">
                <div style="margin-bottom: 5px;"><strong>Pro Number: {{ $pro_number }}</strong></div>
                {{-- Barcode for Pro Number --}}
                <div class="barcode-container">
                    @php
                        try {
                            if (class_exists('\Picqer\Barcode\BarcodeGeneratorPNG')) {
                                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                                $barcode = base64_encode($generator->getBarcode($pro_number, $generator::TYPE_CODE_128));
                                echo '<img src="data:image/png;base64,' . $barcode . '" alt="Barcode">';
                            } elseif (class_exists('\DNS1D')) {
                                echo '<img src="data:image/png;base64,' . \DNS1D::getBarcodePNG($pro_number, 'C128') . '" alt="Barcode">';
                            }
                        } catch (\Exception $e) {
                            // Barcode generation failed, skip it
                        }
                    @endphp
                </div>
            </div>

            {{-- Box 2: Shipper Signature --}}
            <div class="signature-block">
                <div class="signature-title">Shipper Signature / Date</div>
                <div class="signature-text">
                    This is to certify that the above named materials are properly classified, described, packaged, marked and labeled, and are in proper condition for transportation according to the applicable regulations of the U.S. Department of Transportation.
                </div>
                <!-- <div class="signature-mark">X_____________</div> -->
            </div>

            {{-- Box 3: Carrier Signature --}}
            <div class="signature-block">
                <div class="signature-title">Carrier Signature / Date</div>
                <div class="signature-text">
                    Carrier acknowledges receipt of packages and required placards. Carrier certifies emergency response information was made available and/or carrier has the U.S. Department of Transportation emergency response guidebook or equivalent documentation in the vehicle. Property described above is received in good order, except as noted.
                </div>
                <!-- <div class="signature-mark">X_____________</div> -->
            </div>

            {{-- Box 4: Consignee Signature --}}
            <div class="signature-block">
                <div class="signature-title">Consignee Signature / Date</div>
                <div class="signature-text">
                    RECEIVED, subject to individually determined rates or contracts that have been agreed upon in writing between the carrier and shipper, if applicable, otherwise to the rates, classifications and rules that have been established by the carrier and are available to the shipper, on request, and to all applicable state and federal regulations.
                </div>
                <!-- <div class="signature-mark">X_____________</div> -->
            </div>
        </div>
    </div>
</body>
</html>
