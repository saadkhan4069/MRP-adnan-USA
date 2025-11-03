<?php

namespace App\Exports;

use App\Models\Purchase;
use App\Models\CustomField;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Customer;
use App\Models\Currency;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class AllPurchaseExport implements FromView
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $warehouse_id = $this->request->warehouse_id;
        $purchase_status = $this->request->purchase_status;
        $payment_status = $this->request->payment_status;

        $custom_fields = CustomField::where([
            ['belongs_to', 'purchase'],
            ['is_table', true]
        ])->pluck('name');

        $field_names = [];
        foreach ($custom_fields as $fieldName) {
            $field_names[] = str_replace(" ", "_", strtolower($fieldName));
        }

        $query = Purchase::with(['warehouse', 'products']);
        $query->whereDate('created_at', '>=', $this->request->starting_date)
              ->whereDate('created_at', '<=', $this->request->ending_date);

        if ($warehouse_id)
            $query->where('warehouse_id', $warehouse_id);

        if ($purchase_status)
            $query->where('status', $purchase_status);

        if ($payment_status)
            $query->where('payment_status', $payment_status);

        if (Auth::user()->role_id > 2 && config('staff_access') == 'own') {
            $query->where('user_id', Auth::id());
        } elseif (Auth::user()->role_id > 2 && config('staff_access') == 'warehouse') {
            $query->where('warehouse_id', Auth::user()->warehouse_id);
        }

        $purchases = $query->get();
        $exportData = [];

        foreach ($purchases as $purchase) {
            $returned_amount = DB::table('return_purchases')
                ->where('purchase_id', $purchase->id)
                ->sum('grand_total');
            $due = $purchase->grand_total - $returned_amount - $purchase->paid_amount;

            $custom_field_values = [];
            foreach ($field_names as $field) {
                $custom_field_values[$field] = $purchase->$field;
            }

            $currency_code = $purchase->currency_id ? optional(Currency::find($purchase->currency_id))->code : 'N/A';

            $purchase_status_text = match ($purchase->status) {
                1 => 'Received',
                2 => 'Partial',
                3 => 'Pending',
                default => 'Ordered'
            };

            $payment_status_text = $purchase->payment_status == 1 ? 'Due' : 'Paid';

            foreach ($purchase->products as $product) {
                $supplier_id = $product->pivot->supplier_id ?? null;
                $supplier = Supplier::find($supplier_id);

                $exportData[] = [
                    'po_no' => $purchase->po_no,
                    'system_po_no' => $purchase->system_po_no,
                    'reference_no' => $purchase->reference_no,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'Qty' => $product->pivot->qty,
                    'moq' => $product->pivot->moq,

                    'supplier_name' => optional($supplier)->name,
                    'supplier_company' => optional($supplier)->company_name,
                    'supplier_email' => optional($supplier)->email,
                    'supplier_phone' => optional($supplier)->phone_number,
                    'supplier_address' => optional($supplier)->address,

                    'warehouse_name' => optional($purchase->warehouse)->name,
                    'warehouse_company' => optional($purchase->warehouse)->company,
                    'warehouse_phone' => optional($purchase->warehouse)->phone,
                    'warehouse_email' => optional($purchase->warehouse)->email,
                    'warehouse_address' => optional($purchase->warehouse)->address,

                    'total_tax' => $purchase->total_tax,
                    'total_discount' => $purchase->total_discount,
                    'shipping_cost' => $purchase->shipping_cost,
                    'total_cost' => $purchase->total_cost,
                    'grand_total' => $purchase->grand_total,
                    'paid_amount' => $purchase->paid_amount,
                    'due' => $due,

                    'purchase_status' => $purchase_status_text,
                    'payment_status' => $payment_status_text,
                    'currency' => $currency_code,
                    'custom_fields' => $custom_field_values,
                ];
            }
        }

        return view('exports.purchases', [
            'rows' => $exportData,
            'field_names' => $field_names
        ]);
    }
}
