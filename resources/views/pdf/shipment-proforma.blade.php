<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Proforma Invoice - Shipment #{{ $shipment_id }}</title>
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
            border-bottom: 2px solid #981a1c;
        }
        .logo {
            height: 70px;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            color: #981a1c;
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
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #981a1c;
            margin-bottom: 6px;
            border-bottom: 1px solid #981a1c;
            padding-bottom: 3px;
        }
        .info-box p {
            margin: 2px 0;
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
            font-size: 11px;
        }
        .totals-table {
            width: 50%;
            margin-top: 10px;
            font-size: 11px;
        }
        .totals-table th {
            background-color: #981a1c;
            color: #fff;
        }
        .totals-table td {
            font-weight: bold;
            color: #222;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #888;
            clear: both;
        }
        .text-end {
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('images/logo.webp') }}" alt="BrandVolt" class="logo">
        <div class="title">SHIPPING INVOICE</div>
        <div class="meta" style="color: #981a1c; font-weight: bold; margin-top: 8px;">
            Portal Generated Invoice
        </div>
        <div class="meta">
            Shipment #{{ $shipment_id }} | Ref #: {{ $reference_no }} | Date: {{ $date }} | Status: {{ $status }}
        </div>
        @if($po_no && $po_no !== '—')
        <div class="meta"><strong>PO #: {{ $po_no }}</strong></div>
        @endif
    </div>

    <div class="info-section">
        <div class="left-column">
            <div class="info-box">
                <div class="section-title">Customer Info</div>
                <p><strong>Name:</strong> {{ $customer['name'] }}</p>
                <p><strong>Company:</strong> {{ $customer['company'] }}</p>
                <p><strong>Phone:</strong> {{ $customer['phone'] }}</p>
                <p><strong>Email:</strong> {{ $customer['email'] }}</p>
                <p><strong>Address:</strong> {{ $customer['address'] }}</p>
            </div>

            <div class="info-box">
                <div class="section-title">Shipper </div>
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
                <div class="section-title">Consignee </div>
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
    <div class="section-title" style="margin: 20px 15px 5px;">Packages</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Packaging</th>
                <th>Qty</th>
                <th>Class</th>
                <th>NMFC</th>
                <th>Weight</th>
                <th>Dimensions</th>
                <th class="text-end">Declared Value</th>
                <th>Note</th>
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
                $totalWeight +=  (float)( $pkg->qty * $pkg->weight ?? 0);
            @endphp
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $pkg->packaging ?? '—' }}</td>
                <td>{{ $pkg->qty ?? 1 }}</td>
                <td>{{ $pkg->package_class ?? '—' }}</td>
                <td>{{ $pkg->package_nmfc ?? '—' }}</td>
                <td>{{ number_format($item->qty * $pkg->weight ?? 0, 3) }} {{ $pkg->weight_unit ?? 'kg' }}</td>
                <td>
                    @php
                        $dims = array_filter([$pkg->length, $pkg->width, $pkg->height]);
                        echo $dims ? implode(' x ', $dims) . ' ' . ($pkg->dim_unit ?? 'cm') : '—';
                    @endphp
                </td>
                <td class="text-end">{{ $currency_code }} {{ number_format($pkg->declared_value ?? 0, 2) }}</td>
                <td>{{ $pkg->dimensions_note ?? '—' }}</td>
            </tr>
        @endforeach
        @if(count($packages) > 0)
            <tr style="background-color: #f0f0f0; font-weight: bold;">
                <td colspan="2" style="text-align: right;"><strong>Total:</strong></td>
                <td><strong>{{ $totalQty }}</strong></td>
                <td colspan="2"></td>
                <td><strong>{{ number_format($totalWeight, 2) }} </strong></td>
                <td colspan="3"></td>
            </tr>
        @endif
        </tbody>
    </table>
    @endif

    {{-- Items --}}
    @if(count($items) > 0)
    <div class="section-title" style="margin: 20px 15px 5px;">Items</div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Product Code</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Unit</th>
                <th class="text-end">Unit Cost</th>
                <th class="text-end">Discount</th>
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
                <td class="text-end">{{ $currency_code }} {{ number_format($item->net_unit_cost ?? 0, 2) }}</td>
                <td class="text-end">{{ $currency_code }} {{ number_format($item->discount ?? 0, 2) }}</td>
                <td class="text-end">{{ $currency_code }} {{ number_format($item->subtotal ?? 0, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif

    {{-- Totals --}}
    <table class="totals-table">
        <tr>
            <th>Items (Count)</th>
            <td>{{ $totals['items_count'] }}</td>
        </tr>
        <tr>
            <th>Total Qty</th>
            <td>{{ number_format($totals['total_qty'], 3) }}</td>
        </tr>
        <tr>
            <th>Subtotal</th>
            <td>{{ $currency_code }} {{ number_format($totals['subtotal'], 2) }}</td>
        </tr>
        <tr>
            <th>Order Tax</th>
            <td>{{ $currency_code }} {{ number_format($totals['order_tax'], 2) }}</td>
        </tr>
        <tr>
            <th>Discount</th>
            <td>{{ $currency_code }} {{ number_format($totals['order_discount'], 2) }}</td>
        </tr>
        <tr>
            <th>Shipping Cost</th>
            <td>{{ $currency_code }} {{ number_format($totals['shipping_cost'], 2) }}</td>
        </tr>
        <tr>
            <th>Grand Total</th>
            <td><strong>{{ $currency_code }} {{ number_format($totals['grand_total'], 2) }}</strong></td>
        </tr>
    </table>

    {{-- Comments --}}
    @if($comments)
    <div style="width: 100%; margin-top: 30px; font-size: 12px;">
        <div style="margin-bottom: 20px;">
            <h3 style="
                font-size: 13px;
                color: #981a1c;
                border-bottom: 2px solid #981a1c;
                padding-bottom: 5px;
                margin-bottom: 10px;
            ">
                Comments / Instructions
            </h3>
            <div style="
                border: 1px solid #ddd;
                background-color: #fcfcfc;
                border-radius: 4px;
                padding: 10px;
                color: #333;
                white-space: pre-wrap;
                word-wrap: break-word;
            ">
                {{ $comments }}
            </div>
        </div>
    </div>
    @endif

    <div class="footer">
        © {{ date('Y') }} EZ-Solutions.co All rights reserved.
    </div>

</body>
</html>

