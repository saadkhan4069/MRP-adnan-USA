<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $t) {
            $t->id();

            // General meta
            $t->string('po_no')->nullable();
            $t->string('reference_no')->nullable();
            $t->unsignedBigInteger('customer_id')->nullable();
            $t->unsignedTinyInteger('status')->default(1); // 1 Pending, 2 In Transit, 3 Delivered, 4 Returned, 5 Cancelled

            // Shipper (From)
            $t->string('ship_from_company')->nullable();
            $t->string('ship_from_first_name');
            $t->text('ship_from_address_1'); // we store full display_name if needed
            $t->string('ship_from_country');
            $t->string('ship_from_state');
            $t->string('ship_from_city');
            $t->string('ship_from_zipcode');
            $t->string('ship_from_contact');
            $t->string('ship_from_email');

            // Recipient (To)
            $t->string('ship_to_company')->nullable();
            $t->string('ship_to_first_name');
            $t->text('ship_to_address_1');
            $t->string('ship_to_country');
            $t->string('ship_to_state');
            $t->string('ship_to_city');
            $t->string('ship_to_zipcode');
            $t->string('ship_to_contact');
            $t->string('ship_to_email');

            // Currency
            $t->unsignedBigInteger('currency_id')->nullable();
            $t->decimal('exchange_rate', 18, 6)->default(1);

            // Service / Payment (Carrier-agnostic)
            $t->string('provider')->nullable();             // ex: ups, dhl, fedex, aramex...
            $t->string('service_code')->nullable();         // ex: ups_saver, FEDEX_EXPRESS_SAVER
            $t->string('service_name')->nullable();         // human friendly
            $t->boolean('saturday_delivery')->default(false);
            $t->string('signature_option')->nullable();     // none / adult / direct etc.

            $t->string('payer')->nullable();                // shipper / receiver / third_party
            $t->string('account_number')->nullable();       // carrier account used
            $t->string('incoterms')->nullable();            // DAP/DDP/CIF/EXW
            $t->string('contents_type')->nullable();        // merchandise/documents/sample/gift
            $t->decimal('declared_value_total', 18, 2)->nullable();

            // Rates / amounts (order-level)
            $t->decimal('order_tax_rate', 8, 3)->default(0);
            $t->decimal('order_discount', 18, 2)->default(0);
            $t->decimal('shipping_cost', 18, 2)->default(0);
            $t->decimal('total_qty', 18, 3)->default(0);
            $t->unsignedInteger('item')->default(0);
            $t->decimal('total_cost', 18, 2)->default(0);
            $t->decimal('total_tax', 18, 2)->default(0);
            $t->decimal('order_tax', 18, 2)->default(0);
            $t->decimal('grand_total', 18, 2)->default(0);

            // Payment mirror
            $t->decimal('paid_amount', 18, 2)->default(0);
            $t->unsignedTinyInteger('payment_status')->default(1);

            // Tracking / Labels (generic)
            $t->string('tracking_number')->nullable();
            $t->string('master_tracking_number')->nullable();
            $t->string('label_format')->nullable();         // PDF/ZPL/PNG
            $t->string('label_url')->nullable();            // if you host files
            $t->string('invoice_url')->nullable();          // commercial invoice url if generated
            $t->string('customs_docs_url')->nullable();
            $t->string('pickup_id')->nullable();

            // Flexible JSON buckets for any carrier
            $t->json('rate_breakdown')->nullable();         // base, fuel, surcharge etc.
            $t->json('carrier_request')->nullable();        // what we sent
            $t->json('carrier_response')->nullable();       // full response payload
            $t->json('meta')->nullable();                   // anything else

            $t->text('comments')->nullable();

            $t->timestamps();

            // Useful indexes
            $t->index(['provider', 'service_code']);
            $t->index('tracking_number');
            $t->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
