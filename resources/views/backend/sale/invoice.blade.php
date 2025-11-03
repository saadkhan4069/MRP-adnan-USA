<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>{{ $general_setting->site_title }} — Invoice</title>
  <link rel="icon" type="image/png" href="{{ url('logo', $general_setting->site_logo) }}" />

  <!--
    THEME QUICK TUNING
    --------------------------------------------------
    Update these CSS variables only. When you send colors,
    we’ll just replace the values under :root (or add a .theme-* class).
  -->
  <style>
    :root{
      --brand: #6449e7;        /* Primary brand */
      --brand-600:#4b34c2;     /* Hover/darker */
      --accent:#f97316;        /* Accent highlights */
      --bg:#0f172a;            /* Page background (slate-900) */
      --card:#0b1024;          /* Card background */
      --muted:#8892b0;         /* Secondary text */
      --text:#e5e7eb;          /* Main text */
      --line:#1f2937;          /* Hairlines */
      --good:#22c55e;          /* Success */
      --warn:#f59e0b;          /* Warning */
      --bad:#ef4444;           /* Danger */
      --paper:#ffffff;         /* Print background */
    }

    /* RESET */
    *,*::before,*::after{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;background:var(--bg);color:var(--text);font:14px/1.6 'Inter',system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Noto Sans',sans-serif}
    img{max-width:100%;display:block}
    a{color:inherit;text-decoration:none}

    /* LAYOUT */
    .container{max-width:760px;margin:24px auto;padding:0 16px}
    .card{background:linear-gradient(180deg,rgba(255,255,255,0.02),rgba(255,255,255,0.00)) , var(--card);border:1px solid var(--line);border-radius:18px;box-shadow:0 10px 30px rgba(0,0,0,.35);overflow:hidden}
    .card__body{padding:24px}

    /* HEADER */
    .toolbar{display:flex;gap:8px;justify-content:flex-end;padding:12px;background:rgba(255,255,255,0.03);border-bottom:1px solid var(--line)}
    .btn{appearance:none;border:1px solid var(--line);background:#0a0f22;color:var(--text);padding:8px 12px;border-radius:10px;cursor:pointer;transition:.2s;display:inline-flex;align-items:center;gap:8px}
    .btn:hover{transform:translateY(-1px);border-color:var(--brand);box-shadow:0 6px 18px rgba(100,73,231,.25)}
    .btn--primary{background:var(--brand);border-color:transparent}
    .btn--primary:hover{background:var(--brand-600)}

    .header{display:flex;gap:16px;align-items:center}
    .header__brand{display:flex;align-items:center;gap:12px}
    .brand__logo{height:42px;width:50px;border-radius:8px;object-fit:contain;background:#0a0f22;padding:4px;border:1px solid var(--line)}
    .brand__title{font-size:18px;font-weight:700}

    .grid{display:grid;grid-template-columns:1.2fr .8fr;gap:18px;margin-top:18px}
    .muted{color:var(--muted)}
    .chip{display:inline-block;padding:2px 8px;border-radius:999px;background:rgba(100,73,231,.15);color:var(--text);border:1px solid rgba(100,73,231,.35)}

    /* TABLE */
    .table{width:100%;border-collapse:separate;border-spacing:0 10px;margin-top:8px}
    .thead{font-size:12px;color:var(--muted)}
    .tr{background:linear-gradient(180deg,rgba(255,255,255,0.03),rgba(255,255,255,0.01));border:1px solid var(--line)}
    .tr,.tfoot-row{border-radius:12px;overflow:hidden}
    .td,.th{padding:10px 12px;vertical-align:top}
    .td.right,.th.right{text-align:right}
    .td.center{text-align:center}

    /* SUMMARY */
    .split{display:grid;grid-template-columns:1fr 1fr;gap:16px}
    .totals{margin-top:10px;border:1px dashed var(--line);border-radius:12px;overflow:hidden}
    .tfoot{background:rgba(255,255,255,0.03)}
    .tfoot-row{display:grid;grid-template-columns:1fr auto;padding:10px 12px;border-bottom:1px dashed var(--line)}
    .tfoot-row:last-child{border-bottom:none}

    .good{color:var(--good)}
    .warn{color:var(--warn)}
    .bad{color:var(--bad)}

    /* FOOTER */
    .footer{margin-top:16px;text-align:center;color:var(--muted)}

    /* PRINT */
    @media print {
      body{background:var(--paper);color:#000}
      .container{max-width:none;margin:0;padding:0}
      .card{border:none;box-shadow:none}
      .toolbar{display:none !important}
      .chip{border:1px solid #ddd}
      .tr{border-color:#ddd}
      .tfoot-row{border-color:#ddd}
      @page{margin:1.2cm}
    }

    /* RTL (Optional). Toggle by adding dir="rtl" on <html> */
    [dir="rtl"] .grid{direction:rtl}
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <!-- ACTIONS (hidden on print) -->
      <div class="toolbar">
        @php
          if(preg_match('~[0-9]~', url()->previous())){$url='../../pos';}else{$url=url()->previous();}
        @endphp
        <a href="{{ $url }}" class="btn" title="Back">&#x2190; {{ __('db.Back') }}</a>
        <button onclick="window.print()" class="btn btn--primary" title="Print">🖨️ {{ __('db.Print') }}</button>
      </div>

      <div class="card__body">
        <!-- HEADER -->
        <div class="header">
          @if($general_setting->site_logo)
            <img class="brand__logo" src="{{ url('logo', $general_setting->site_logo) }}" alt="logo" />
          @endif
          <div class="header__brand">
            <div class="brand__title">{{ $lims_biller_data->company_name }}</div>
            <span class="chip">Invoice</span>
          </div>
        </div>

        <div class="grid">
          <div>
            <div class="muted">{{ __('db.Address') }}</div>
            <div>{{ $lims_warehouse_data->address }}</div>
            <div class="muted" style="margin-top:6px">{{ __('db.Phone Number') }}</div>
            <div>{{ $lims_warehouse_data->phone }}</div>
          </div>
          <div>
            <div><strong>{{ __('db.date') }}:</strong> {{ date($general_setting->date_format, strtotime($lims_sale_data->created_at->toDateString())) }}</div>
            <div><strong>{{ __('db.reference') }}:</strong> {{ $lims_sale_data->reference_no }}</div>
            <div><strong>{{ __('db.customer') }}:</strong> {{ $lims_customer_data->name }}</div>
            @if($lims_sale_data->table_id)
              <div><strong>{{ __('db.Table') }}:</strong> {{ $lims_sale_data->table->name }}</div>
              <div><strong>{{ __('db.Queue') }}:</strong> {{ $lims_sale_data->queue }}</div>
            @endif
            @php
              foreach($sale_custom_fields as $key => $fieldName){
                $field_name=str_replace(' ','_',strtolower($fieldName));
                echo '<div><strong>'.$fieldName.':</strong> '.$lims_sale_data->$field_name.'</div>';
              }
              foreach($customer_custom_fields as $key => $fieldName){
                $field_name=str_replace(' ','_',strtolower($fieldName));
                echo '<div><strong>'.$fieldName.':</strong> '.$lims_customer_data->$field_name.'</div>';
              }
            @endphp
          </div>
        </div>

        <!-- CASHIER -->
        <div style="text-align:right;margin-top:10px">
          <h3 style="margin:0">{{ $lims_bill_by['name'] }} <span class="muted">({{ $lims_bill_by['user_name'] }})</span></h3>
        </div>

        <!-- ITEMS TABLE -->
        <table class="table" role="table" aria-label="Items">
          <thead class="thead">
            <tr class="tr" style="background:transparent;border-color:transparent">
              <th class="th">{{ __('db.Item') }}</th>
              <th class="th right">{{ __('db.Subtotal') }}</th>
            </tr>
          </thead>
          <tbody>
            @php $total_product_tax = 0; @endphp
            @foreach($lims_product_sale_data as $key => $product_sale_data)
              @php
                $lims_product_data = \App\Models\Product::find($product_sale_data->product_id);
                if($product_sale_data->variant_id){
                  $variant_data = \App\Models\Variant::find($product_sale_data->variant_id);
                  $product_name = $lims_product_data->name.' ['.$variant_data->name.']';
                } elseif($product_sale_data->product_batch_id){
                  $product_batch_data = \App\Models\ProductBatch::select('batch_no')->find($product_sale_data->product_batch_id);
                  $product_name = $lims_product_data->name.' ['.__('db.Batch No').':'.$product_batch_data->batch_no.']';
                } else { $product_name = $lims_product_data->name; }

                if($product_sale_data->imei_number && !str_contains($product_sale_data->imei_number, 'null')){
                  $product_name .= '<br>'.trans('IMEI or Serial Numbers').': '.$product_sale_data->imei_number;
                }

                if(isset($product_sale_data->warranty_duration)){
                  $product_name .= '<br><strong>Warranty</strong>: '.$product_sale_data->warranty_duration;
                  $product_name .= '<br><strong>Will Expire</strong>: '.$product_sale_data->warranty_end;
                }
                if(isset($product_sale_data->guarantee_duration)){
                  $product_name .= '<br><strong>Guarantee</strong>: '.$product_sale_data->guarantee_duration;
                  $product_name .= '<br><strong>Will Expire</strong>: '.$product_sale_data->guarantee_end;
                }

                $topping_names=[];$topping_prices=[];$topping_price_sum=0;
                if($product_sale_data->topping_id){
                  $decoded=is_string($product_sale_data->topping_id)?json_decode($product_sale_data->topping_id,true):$product_sale_data->topping_id;
                  if(is_array($decoded)){
                    foreach($decoded as $t){$topping_names[]=$t['name'];$topping_prices[]=$t['price'];$topping_price_sum+=$t['price'];}
                  }
                }
                $subtotal = ($product_sale_data->total + $topping_price_sum);
              @endphp
              <tr class="tr">
                <td class="td">
                  {!! $product_name !!}
                  @if(!empty($topping_names))
                    <div class="muted" style="margin-top:4px">{{ implode(', ', $topping_names) }}</div>
                  @endif
                  @foreach($product_custom_fields as $i => $fieldName)
                    @php $field_name=str_replace(' ','_',strtolower($fieldName)); @endphp
                    @if($lims_product_data->$field_name)
                      <div class="muted">{{ $fieldName.': '.$lims_product_data->$field_name }}</div>
                    @endif
                  @endforeach
                  <div class="muted" style="margin-top:4px">
                    {{ $product_sale_data->qty }} × {{ number_format((float)($product_sale_data->total / $product_sale_data->qty), $general_setting->decimal, '.', ',') }}
                    @if(!empty($topping_prices))
                      <small> + {{ implode(' + ', array_map(fn($p)=>number_format($p,$general_setting->decimal,'.',','), $topping_prices)) }}</small>
                    @endif
                    @if($product_sale_data->tax_rate)
                      @php $total_product_tax += $product_sale_data->tax; @endphp
                      <span class="chip" style="margin-left:6px">{{ __('db.Tax') }} {{ $product_sale_data->tax_rate }}%: {{ $product_sale_data->tax }}</span>
                    @endif
                  </div>
                </td>
                <td class="td right">{{ number_format($subtotal, $general_setting->decimal, '.', ',') }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <!-- TOTALS -->
        <div class="split">
          <div></div>
          <div class="totals">
            <div class="tfoot">
              <div class="tfoot-row"><div>{{ __('db.Total') }}</div><div>{{ number_format((float)($lims_sale_data->total_price), $general_setting->decimal, '.', ',') }}</div></div>
              @if($general_setting->invoice_format == 'gst' && $general_setting->state == 1)
                <div class="tfoot-row"><div>IGST</div><div>{{ number_format((float)($total_product_tax), $general_setting->decimal, '.', ',') }}</div></div>
              @elseif($general_setting->invoice_format == 'gst' && $general_setting->state == 2)
                <div class="tfoot-row"><div>SGST</div><div>{{ number_format((float)($total_product_tax/2), $general_setting->decimal, '.', ',') }}</div></div>
                <div class="tfoot-row"><div>CGST</div><div>{{ number_format((float)($total_product_tax/2), $general_setting->decimal, '.', ',') }}</div></div>
              @endif
              @if($lims_sale_data->order_tax)
                <div class="tfoot-row"><div>{{ __('db.Order Tax') }}</div><div>{{ number_format((float)($lims_sale_data->order_tax), $general_setting->decimal, '.', ',') }}</div></div>
              @endif
              @if($lims_sale_data->order_discount)
                <div class="tfoot-row"><div>{{ __('db.Order Discount') }}</div><div>- {{ number_format((float)($lims_sale_data->order_discount), $general_setting->decimal, '.', ',') }}</div></div>
              @endif
              @if($lims_sale_data->coupon_discount)
                <div class="tfoot-row"><div>{{ __('db.Coupon Discount') }}</div><div>- {{ number_format((float)($lims_sale_data->coupon_discount), $general_setting->decimal, '.', ',') }}</div></div>
              @endif
              @if($lims_sale_data->shipping_cost)
                <div class="tfoot-row"><div>{{ __('db.Shipping Cost') }}</div><div>{{ number_format((float)($lims_sale_data->shipping_cost), $general_setting->decimal, '.', ',') }}</div></div>
              @endif
              <div class="tfoot-row" style="background:rgba(100,73,231,.15);font-weight:700">
                <div>{{ __('db.grand total') }}</div>
                <div>{{ number_format((float)($lims_sale_data->grand_total), $general_setting->decimal, '.', ',') }}</div>
              </div>
              @if($lims_sale_data->grand_total - $lims_sale_data->paid_amount > 0)
                <div class="tfoot-row bad"><div>{{ __('db.Due') }}</div><div>{{ number_format((float)($lims_sale_data->grand_total - $lims_sale_data->paid_amount), $general_setting->decimal, '.', ',') }}</div></div>
              @endif
              @if($totalDue)
                <div class="tfoot-row bad"><div>{{ __('db.Total Due') }}</div><div>{{ number_format($totalDue, $general_setting->decimal, '.', ',') }}</div></div>
              @endif
              <div class="tfoot-row" style="grid-template-columns:1fr;gap:4px;text-align:center">
                @if($general_setting->currency_position == 'prefix')
                  <div>{{ __('db.In Words') }}: <strong>{{ $currency_code }}</strong> {{ str_replace('-', ' ', $numberInWords) }}</div>
                @else
                  <div>{{ __('db.In Words') }}: {{ str_replace('-', ' ', $numberInWords) }} <strong>{{ $currency_code }}</strong></div>
                @endif
              </div>
            </div>
          </div>
        </div>

        <!-- PAYMENTS -->
        <div style="margin-top:14px">
          @foreach($lims_payment_data as $payment_data)
            <div class="tr" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;padding:8px 12px">
              <div><strong>{{ __('db.Paid By') }}:</strong> {{ $payment_data->paying_method }}</div>
              <div><strong>{{ __('db.Amount') }}:</strong> {{ number_format((float)($payment_data->amount), $general_setting->decimal, '.', ',') }}</div>
              <div><strong>{{ __('db.Change') }}:</strong> {{ number_format((float)$payment_data->change, $general_setting->decimal, '.', ',') }}</div>
            </div>
          @endforeach
        </div>

        <!-- BARCODES -->
        <div style="text-align:center;margin-top:12px">
          <div class="muted" style="margin-bottom:6px">{{ __('db.Thank you for shopping with us Please come again') }}</div>
          @php echo '<img style="margin-top:6px" src="data:image/png;base64,' . DNS1D::getBarcodePNG($lims_sale_data->reference_no, 'C128') . '" width="300" alt="barcode" />'; @endphp
          <br />
          @php echo '<img style="margin-top:6px" src="data:image/png;base64,' . DNS2D::getBarcodePNG($qrText, 'QRCODE') . '" alt="QRcode" />'; @endphp
        </div>
      </div>
    </div>
  </div>

  <script>
    // Keep old behavior available
    // localStorage.clear();
    // auto print if desired:
    // setTimeout(()=>window.print(), 500);
  </script>
</body>
</html>
