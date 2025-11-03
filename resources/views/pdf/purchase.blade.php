<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order</title>
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
            border-bottom: 1px solid #ccc;
        }
        .logo {
            height: 70px;
        }
        .title {
            font-size: 22px;
            font-weight: bold;
            color: #3a3273;
            margin-top: 5px;
        }
        .meta {
            font-size: 12px;
            color: #555;
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
            color: #3a3273;
            margin-bottom: 6px;
            border-bottom: 1px solid #3a3273;
            padding-bottom: 3px;
        }
        .info-box p {
            margin: 2px 0;
        }
        a.email-link {
            color: #3a3273;
            text-decoration: underline;
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
            background-color: #3a3273;
            color: #fff;
            font-size: 11px;
        }
        .totals-table {
            width: 50%;
            
            margin-top: 10px;
            font-size: 11px;
        }
        .totals-table th {
            background-color: #3a3273;
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
        .block-section {
            width: 70%;
            margin: 30px auto 0;
            padding: 10px;
            word-wrap: break-word;
            white-space: pre-line;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('images/brandvolt.jpg') }}" alt="BrandVolt" class="logo">
        <div class="title">PURCHASE ORDER</div>
        <div class="meta">
            PO #: {{ $po_no ?? '-' }} | Ref #: {{ $reference_no ?? '-' }} | Date: {{ $date ?? '-' }}
        </div>
          <h3>PO #: {{ $system_po_no  }}</h3>
    </div>

    <div class="info-section">
        <div class="left-column">
            <div class="info-box">
                <div class="section-title">Customer Info</div>
                <p><strong>Name:</strong> {{ $customer['name'] ?? '-' }}</p>
                <p><strong>Company:</strong> {{ $customer['company'] ?? '-' }}</p>
                <p><strong>Phone:</strong> {{ $customer['phone'] ?? '-' }}</p>
                <p><strong>Email:</strong>
                    <a href="mailto:{{ $customer['email'] }}" class="email-link">{{ $customer['email'] ?? '-' }}</a>
                </p>
                <p><strong>Address:</strong> {{ $customer['address'] ?? '-' }}</p>
            </div>

            <div class="info-box">
                <div class="section-title">Supplier Info</div>
                <p><strong>Name:</strong> {{ $supplier['name'] ?? '-' }}</p>
                <p><strong>Company:</strong> {{ $supplier['company'] ?? '-' }}</p>
                <p><strong>Phone:</strong> {{ $supplier['phone'] ?? '-' }}</p>
                <p><strong>Email:</strong>
                    <a href="mailto:{{ $supplier['email'] }}" class="email-link">{{ $supplier['email'] ?? '-' }}</a>
                </p>
                <p><strong>Address:</strong> {{ $supplier['address'] ?? '-' }}</p>
            </div>
        </div>

        <div class="right-column">
            <div class="info-box">
                <div class="section-title">Warehouse / Production Info</div>
                <p><strong>Name:</strong> {{ $warehouse['name'] ?? '-' }}</p>
                <p><strong>Company:</strong> {{ $warehouse['company'] ?? '-' }}</p>
                <p><strong>Phone:</strong> {{ $warehouse['phone'] ?? '-' }}</p>
                <p><strong>Email:</strong>
                    <a href="mailto:{{ $warehouse['email'] }}" class="email-link">{{ $warehouse['email'] ?? '-' }}</a>
                </p>
                <p><strong>Address:</strong> {{ $warehouse['address'] ?? '-' }}</p>
            </div>
        </div>
    </div>

    {{-- ======================= Product Details ======================= --}}
<div class="section-title" style="margin: 20px 15px 5px;">Product Details</div>

@php
    // Totals accumulators (row-wise)
    $sumSub   = 0.0;   // sum of row subtotals (product->total)
    $sumShip  = 0.0;   // sum of row shipping (ship_cost)
    $sumTax   = 0.0;   // optional: sum of row tax (if you want to show order tax)
@endphp

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Code</th>
            <th>Unit</th>
            <th>Qty</th>
            <th>MOQ</th>
            <th>Lead Time</th>
            <th>Unit Cost</th>
            <th>Shipping Term</th>
            <th>Tax</th>
            <th>Shipping</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($products as $key => $product)
        @php
            $qty       = (float) ($product->qty ?? 0);
            $unitCode  = $product->unit->unit_code ?? '';
            $moq       = $product->moq ?? '';
            $shipTerm  = $product->ship_term ?? '';
            $netUnit   = (float) ($product->net_unit_cost ?? 0);
            $shipCost  = (float) ($product->ship_cost ?? 0);
            $taxAmt    = (float) ($product->tax ?? 0);
            $taxRate   = isset($product->tax_rate) ? (float) $product->tax_rate : null;

            // Row-level numbers (JS parity maintained)
            $subtotal  = (float) ($product->total ?? 0);         // subN
            $perUnit   = $qty > 0 ? round($subtotal / $qty, 2) : 0;
            $rowTotal  = round($subtotal + $shipCost, 2);        // subN + scN (tax shown separately)

            // Accumulate for totals table
            $sumSub  += $subtotal;
            $sumShip += $shipCost;
            $sumTax  += $taxAmt;
        @endphp
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ $product->name }}</td>
            <td>{{ $product->code }}</td>
            <td>{{ $unitCode }}</td>
            <td>{{ number_format($qty, 2) }}</td>
            <td>{{ $moq }}</td>
            <td>{{ $product->etd_date }}</td>
            <td>{{ number_format($perUnit, 2) }}</td>   {{-- per-unit (like JS perUnit) --}}
            <td>{{ $shipTerm }}</td>
            <td>
                {{ number_format($taxAmt, 2) }}
                @if(!is_null($taxRate)) ({{ number_format($taxRate, 2) }}%) @endif
            </td>
            <td>{{ number_format($shipCost, 2) }}</td>
            <td>{{ number_format($rowTotal, 2) }}</td>  {{-- subtotal + ship_cost --}}
        </tr>
    @endforeach
    </tbody>
</table>

@php
   
    $currency = $currency_code ?: '$';

    // Order-level figures (if you have discount/paid coming from request/db, use them; otherwise 0)
    $order_discount = isset($order_discount) ? (float) $order_discount : 0.0;   // optional
    $paid_amount    = isset($paid_amount)    ? (float) $paid_amount    : 0.0;   // optional

    // Aap ki requirement: Grand Total = Subtotal (rows) + Shipping (rows)
    $subTotalRows   = round($sumSub, 2);
    $shippingRows   = round($sumShip, 2);

    // Agar aap Order Tax bhi totals mein dikhana chahen:
    $order_tax      = round($sumTax, 2); // optional (comment out if not needed)

    // Grand total strictly per your ask:
    $grandTotal     = round($subTotalRows + $shippingRows, 2);

    // Agar discount ko minus karna ho to niche wali line use kar sakte hain:
    // $grandTotal   = round($subTotalRows + $shippingRows + $order_tax - $order_discount, 2);

    // Due = Grand Total - Paid
    $dueAmount      = round($grandTotal - $paid_amount, 2);
@endphp

{{-- ======================= Totals Table ======================= --}}
<table class="totals-table">
    <tr>
        <th>Subtotal</th>
        <td>{{ $currency }} {{ number_format($subTotalRows, 2) }}</td>
    </tr>

    {{-- OPTIONAL: Agar Order Tax totals mein chahiye to is row ko rakhein, warna hata dein --}}
    <tr>
        <th>Order Tax</th>
        <td>{{ $currency }} {{ number_format($order_tax, 2) }}</td>
    </tr>

    <tr>
        <th>Discount</th>
        <td>{{ $currency }} {{ number_format($order_discount, 2) }}</td>
    </tr>

    <tr>
        <th>Shipping</th>
        <td>{{ $currency }} {{ number_format($shippingRows, 2) }}</td>
    </tr>

    <tr>
        <th>Grand Total</th>
        <td>{{ $currency }} {{ number_format($grandTotal, 2) }}</td>
    </tr>
    <tr>
        <th>Paid</th>
        <td>{{ $currency }} {{ number_format($paid_amount, 2) }}</td>
    </tr>
    <tr>
        <th>Due</th>
        <td>{{ $currency }} {{ number_format($dueAmount, 2) }}</td>
    </tr>
</table>

   

        {{-- COMMENTS SECTION --}}
      @if($comments || $signature)
    <div style="width: 100%; margin-top: 80px; font-size: 12px;">
         @if($comments)
            <div style="margin-bottom: 30px;">
                <h3 style="
                    font-size: 13px;
                    color: #2c2c6b;
                    border-bottom: 2px solid #2c2c6b;
                    padding-bottom: 5px;
                    margin-bottom: 10px;
                ">
                    Comments
                </h3>
                <div style="
                    border: 1px solid #ddd;
                    background-color: #fcfcfc;
                    border-radius: 4px;
                    color: #333;
                    white-space: pre-wrap;
                    word-wrap: break-word;
                    
                ">
                    {{ $comments }}
                   
                </div>
            </div>
        @endif

        {{-- Ship Instruction SECTION --}}
        @if($ship_instruction)
            <div style="margin-bottom: 30px;">
                <h3 style="
                    font-size: 13px;
                    color: #2c2c6b;
                    border-bottom: 2px solid #2c2c6b;
                    padding-bottom: 5px;
                    margin-bottom: 10px;
                ">
                    Shipping Instruction
                </h3>
                <div style="
                    border: 1px solid #ddd;
                    background-color: #fcfcfc;
                    border-radius: 4px;
                    color: #333;
                    white-space: pre-wrap;
                    word-wrap: break-word;
                    
                ">
                    {{ $ship_instruction }}
                   
                </div>
            </div>
        @endif

        {{-- SIGNATURE SECTION --}}
        @if($signature)
            <div style="margin-top: 30px; text-align: left;">
                <div style="
                    display: inline-block;
                    text-align: center;
                    border-top: 1px solid #555;
                    padding-top: 6px;
                    width: 180px;
                    font-size: 11px;
                    color: #333;
                ">
                    {{ $signature }}
                    <br>
                    <span style="font-style: italic;">Authorized Signature</span>
                </div>
            </div>
        @endif

    </div>
@endif
    

    <div class="footer">
        © {{ date('M-d-Y') }} EZ-Solutions.co All rights reserved.
    </div>

</body>
</html>
