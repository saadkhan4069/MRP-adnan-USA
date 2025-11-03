<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation Detail</title>
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
        <div class="title">QUOTATION #</div>
        <div class="meta">
            PO #: {{ $po_no ?? '-' }} | Ref #: {{ $reference_no ?? '-' }} | Date: {{ $date ?? '-' }}
        </div>
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

    <div class="section-title" style="margin: 20px 15px 5px;">Product Details</div>
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
                <th>Discount</th>
                <th>Tax</th>
                <th>Shipping</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $key => $product)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->code }}</td>
                    <td>{{ $product->unit->unit_code ?? '' }}</td>
                    <td>{{ $product->qty }}</td>
                    <td>{{ $product->moq }}</td>
                    <td>{{ $product->lt_date }}</td>
                    <td>{{ number_format($product->net_unit_price, 2) }}</td>
                    <td>{{ number_format($product->discount, 2) }}</td>
                    <td>{{ number_format($product->tax, 2) }}</td>
                    <td>{{ number_format($product->ship_cost, 2) }}</td>
                    <td>{{ number_format(($product->net_unit_price * $product->qty) + $product->tax, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <th>Subtotal</th>
            <td>{{ number_format($totals['sub_total'], 2) }} </td>
        </tr>
        <tr>
            <th>Order Tax</th>
            <td>{{ number_format($totals['order_tax'], 2) }} </td>
        </tr>
        <tr>
            <th>Discount</th>
            <td>{{ number_format($totals['order_discount'], 2) }} </td>
        </tr>
        <tr>
            <th>Shipping</th>
            <td>{{ number_format($totals['shipping_cost'], 2) }} </td>
        </tr>
        <tr>
            <th>Grand Total</th>
            <td>{{ number_format($totals['grand_total'], 2) }} </td>
        </tr>
       
        
    </table>
   

        {{-- COMMENTS SECTION --}}
    <!--   @if($comments || $signature)
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
 -->
       

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
        © {{ date('Y') }} EZ-Solutions.co All rights reserved.
    </div>

</body>
</html>
