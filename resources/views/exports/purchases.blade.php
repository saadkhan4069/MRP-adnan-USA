<table>
    <thead>
        <tr>
            <th>PO #</th>
            <th>System PO #</th>
            <th>Reference No</th>
            <th>Product Name</th>
            <th>Product Code</th>
            <th>Qty</th>
            <th>Minimum Order Qty</th>

            <th>Supplier Name</th>
            <th>Supplier Company</th>
            <th>Supplier Email</th>
            <th>Supplier Phone</th>
            <th>Supplier Address</th>

            <th>Warehouse Name</th>
            <th>Warehouse Company</th>
            <th>Warehouse Phone</th>
            <th>Warehouse Email</th>
            <th>Warehouse Address</th>

                    <!-- In <thead> -->
            <th>Purchase Status</th>
            <th>Payment Status</th>
            <th>Currency</th>
            <th>Total Tax</th>
            <th>Total Discount</th>
            <th>Shipping Cost</th>
            <th>Total Cost</th>
            <th>Grand Total</th>
            <th>Paid</th>
            <th>Due</th>


            @foreach($field_names as $field)
                <th>{{ ucfirst(str_replace('_',' ', $field)) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
         @foreach($rows as $row)
            <tr>
                <td>{{ $row['po_no'] }}</td>
                <td>{{ $row['system_po_no'] }}</td>
                <td>{{ $row['reference_no'] }}</td>
                <td>{{ $row['product_name'] }}</td>
                <td>{{ $row['product_code'] }}</td>
                <td>{{ $row['Qty'] }}</td>
                <td>{{ $row['moq'] }}</td>

                <td>{{ $row['supplier_name'] }}</td>
                <td>{{ $row['supplier_company'] }}</td>
                <td>{{ $row['supplier_email'] }}</td>
                <td>{{ $row['supplier_phone'] }}</td>
                <td>{{ $row['supplier_address'] }}</td>

                <td>{{ $row['warehouse_name'] }}</td>
                <td>{{ $row['warehouse_company'] }}</td>
                <td>{{ $row['warehouse_phone'] }}</td>
                <td>{{ $row['warehouse_email'] }}</td>
                <td>{{ $row['warehouse_address'] }}</td>
                
                            <!-- In <tbody> -->
                <td>{{ $row['purchase_status'] }}</td>
                <td>{{ $row['payment_status'] }}</td>
                <td>{{ $row['currency'] }}</td>
                <td>{{ $row['total_tax'] }}</td>
                <td>{{ $row['total_discount'] }}</td>
                <td>{{ $row['shipping_cost'] }}</td>
                <td>{{ $row['total_cost'] }}</td>
                <td>{{ $row['grand_total'] }}</td>
                <td>{{ $row['paid_amount'] }}</td>
                <td>{{ $row['due'] }}</td>

                <!-- <td>{{ $row['due'] }}</td> -->

                @foreach($field_names as $field)
                    <td>{{ $row['custom_fields'][$field] ?? '' }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
