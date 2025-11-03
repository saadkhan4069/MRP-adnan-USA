<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shipments', function (Blueprint $t) {
            $t->id();

            // meta
            $t->string('po_no')->nullable();
            $t->string('reference_no')->nullable();
            $t->unsignedBigInteger('customer_id')->nullable(); // buyer
            $t->unsignedTinyInteger('status')->default(1);     // 1..5

            // ship-from
            $t->string('ship_from_company')->nullable();
            $t->string('ship_from_first_name');
            $t->text('ship_from_address_1');
            $t->string('ship_from_country');
            $t->string('ship_from_state');
            $t->string('ship_from_city');
            $t->string('ship_from_zipcode');
            $t->string('ship_from_contact');
            $t->string('ship_from_email');

            // ship-to
            $t->string('ship_to_company')->nullable();
            $t->string('ship_to_first_name');
            $t->text('ship_to_address_1');
            $t->string('ship_to_country');
            $t->string('ship_to_state');
            $t->string('ship_to_city');
            $t->string('ship_to_zipcode');
            $t->string('ship_to_contact');
            $t->string('ship_to_email');

            // currency
            $t->unsignedBigInteger('currency_id')->nullable();
            $t->decimal('exchange_rate', 18, 6)->default(1);

            // service & payment
            $t->string('service_code')->nullable();
            $t->boolean('saturday_delivery')->default(false);
            $t->string('signature_option')->nullable(); // none/adult/direct
            $t->string('bill_to')->nullable();          // shipper/receiver/third_party
            $t->string('ups_account')->nullable();
            $t->string('incoterms')->nullable();        // DAP/DDP/CIF/EXW
            $t->string('contents_type')->nullable();    // merchandise/documents/sample/gift
            $t->decimal('declared_value_total', 18, 2)->nullable();

            // order level totals
            $t->decimal('order_tax_rate', 8, 3)->default(0);
            $t->decimal('order_discount', 18, 2)->default(0);
            $t->decimal('shipping_cost', 18, 2)->default(0);

            $t->unsignedInteger('item')->default(0);      // number of line items
            $t->decimal('total_qty', 18, 3)->default(0);
            $t->decimal('total_cost', 18, 2)->default(0);
            $t->decimal('total_tax', 18, 2)->default(0);
            $t->decimal('order_tax', 18, 2)->default(0);
            $t->decimal('grand_total', 18, 2)->default(0);

            // payment mirror
            $t->decimal('paid_amount', 18, 2)->default(0);
            $t->unsignedTinyInteger('payment_status')->default(1);

            $t->text('comments')->nullable();

            $t->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('shipments');
    }
};
