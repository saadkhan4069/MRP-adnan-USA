# Inventory Management System - Complete Guide (Roman Urdu)

## Inventory Kahan Store Hoti Hai? 📦

Project mein **2 main tables** mein inventory store hoti hai:

### 1. **`products` Table** (Overall Stock)
- **Field**: `qty`
- **Purpose**: Product ki **total quantity** sab warehouses mein
- **Example**: Agar 3 warehouses mein total 1000 units hain, to `products.qty = 1000`

### 2. **`product_warehouse` Table** (Warehouse-wise Stock)
- **Fields**: 
  - `product_id` - Product ka ID
  - `warehouse_id` - Warehouse ka ID
  - `qty` - Us warehouse mein kitni quantity hai
  - `variant_id` - Agar product variant hai
  - `product_batch_id` - Batch number (agar batch tracking hai)
- **Purpose**: Har warehouse mein kitni quantity hai, yeh track karta hai
- **Example**: 
  - Warehouse A: 500 units
  - Warehouse B: 300 units
  - Warehouse C: 200 units

---

## Inventory Increase (Quantity Plus) ➕

Yeh modules mein inventory **increase** hoti hai:

### 1. **Purchase Order Module** 📥

**Location**: `PurchaseController.php` → `store()` method

**Kaise Kaam Karta Hai:**
```php
// Purchase order "Received" status par save hone par:
if ($purchase->status == 1) { // Status = Received
    // 1. Product table mein quantity increase
    $product->qty = $product->qty + $quantity;
    $product->save();
    
    // 2. Product_Warehouse table mein quantity increase
    $product_warehouse->qty = $product_warehouse->qty + $quantity;
    $product_warehouse->save();
}
```

**Process:**
1. Purchase order create karein
2. Status = **"Received"** set karein
3. Save karne par:
   - **Product table** mein quantity **+** ho jati hai
   - **Product_Warehouse table** mein selected warehouse ki quantity **+** ho jati hai
   - Agar batch tracking hai, to **ProductBatch** table mein bhi **+** hoti hai

**Example:**
- Pehle: Product A = 100 units (Warehouse 1)
- Purchase: 50 units add kiye (Warehouse 1)
- Baad mein: Product A = 150 units (Warehouse 1)

**URL**: `/purchases/create`

---

### 2. **Return Sale Module** 🔄

**Location**: `ReturnController.php` → `store()` method

**Kaise Kaam Karta Hai:**
```php
// Customer ne product return kiya:
$product->qty = $product->qty + $return_quantity;
$product_warehouse->qty = $product_warehouse->qty + $return_quantity;
```

**Process:**
1. Sale return create karein
2. Customer ne jo products return kiye, unki quantity:
   - **Product table** mein **+** ho jati hai
   - **Product_Warehouse table** mein **+** ho jati hai

**Example:**
- Pehle: Product B = 200 units
- Return: Customer ne 10 units return kiye
- Baad mein: Product B = 210 units

**URL**: `/returns/create`

---

### 3. **Transfer Module** (To Warehouse) 📦➡️

**Location**: `TransferController.php` → `store()` method

**Kaise Kaam Karta Hai:**
```php
// Transfer complete hone par:
// FROM Warehouse: Quantity decrease
$from_warehouse->qty = $from_warehouse->qty - $quantity;

// TO Warehouse: Quantity increase
$to_warehouse->qty = $to_warehouse->qty + $quantity;
```

**Process:**
1. Transfer create karein (From Warehouse → To Warehouse)
2. Status = **"Completed"** set karein
3. Save karne par:
   - **From Warehouse** ki quantity **-** hoti hai
   - **To Warehouse** ki quantity **+** hoti hai
   - **Product table** ki total quantity **same** rehti hai (sirf warehouse change hota hai)

**Example:**
- Pehle: 
  - Warehouse A: 100 units
  - Warehouse B: 50 units
- Transfer: 30 units (A → B)
- Baad mein:
  - Warehouse A: 70 units (-30)
  - Warehouse B: 80 units (+30)
  - Total: 150 units (same)

**URL**: `/transfers/create`

---

### 4. **Adjustment Module** (Manual Increase) ➕

**Location**: `AdjustmentController.php` → `store()` method

**Kaise Kaam Karta Hai:**
```php
// Manual adjustment:
if($action == '+') {
    $product->qty = $product->qty + $adjustment_qty;
    $product_warehouse->qty = $product_warehouse->qty + $adjustment_qty;
}
```

**Process:**
1. Adjustment create karein
2. Action = **"+"** select karein
3. Quantity enter karein
4. Save karne par:
   - **Product table** mein quantity **+** ho jati hai
   - **Product_Warehouse table** mein **+** ho jati hai

**Use Case:**
- Stock found (jo system mein nahi tha)
- Manual correction
- Damaged goods replacement

**URL**: `/adjustments/create`

---

### 5. **Production Module** (Finished Goods) 🏭

**Location**: `ProductionController.php` → `store()` method

**Kaise Kaam Karta Hai:**
```php
// Production complete hone par:
// Finished product ki quantity increase
$finished_product->qty = $finished_product->qty + $produced_qty;
$finished_product_warehouse->qty = $finished_product_warehouse->qty + $produced_qty;

// Raw materials ki quantity decrease (separate process)
```

**Process:**
1. Production order create karein
2. Raw materials select karein (jo use honge)
3. Finished product select karein (jo banaega)
4. Production complete karne par:
   - **Raw materials** ki quantity **-** hoti hai
   - **Finished product** ki quantity **+** hoti hai

**Example:**
- Raw Material: Sugar 10kg (-10kg)
- Finished Product: Soft Drink 100 bottles (+100)

**URL**: `/productions/create` (agar Manufacturing module enabled hai)

---

## Inventory Decrease (Quantity Minus) ➖

Yeh modules mein inventory **decrease** hoti hai:

### 1. **Sale Order Module** 💰

**Location**: `SaleController.php` → `store()` method

**Kaise Kaam Karta Hai:**
```php
// Sale order "Completed" status par save hone par:
if ($sale->sale_status == 1) { // Status = Completed
    // 1. Product table mein quantity decrease
    $product->qty = $product->qty - $quantity;
    $product->save();
    
    // 2. Product_Warehouse table mein quantity decrease
    $product_warehouse->qty = $product_warehouse->qty - $quantity;
    $product_warehouse->save();
}
```

**Process:**
1. Sale order create karein
2. Status = **"Completed"** set karein
3. Save karne par:
   - **Product table** mein quantity **-** ho jati hai
   - **Product_Warehouse table** mein selected warehouse ki quantity **-** ho jati hai
   - Agar variant hai, to **ProductVariant** table mein bhi **-** hoti hai

**Example:**
- Pehle: Product C = 500 units (Warehouse 2)
- Sale: 25 units beche (Warehouse 2)
- Baad mein: Product C = 475 units (Warehouse 2)

**URL**: `/sales/create`

---

### 2. **Return Purchase Module** 🔄

**Location**: `ReturnPurchaseController.php` → `store()` method

**Kaise Kaam Karta Hai:**
```php
// Supplier ko product return kiya:
$product->qty = $product->qty - $return_quantity;
$product_warehouse->qty = $product_warehouse->qty - $return_quantity;
```

**Process:**
1. Purchase return create karein
2. Supplier ko jo products return kiye, unki quantity:
   - **Product table** mein **-** ho jati hai
   - **Product_Warehouse table** mein **-** ho jati hai

**Example:**
- Pehle: Product D = 300 units
- Return: Supplier ko 20 units return kiye
- Baad mein: Product D = 280 units

**URL**: `/return-purchases/create`

---

### 3. **Transfer Module** (From Warehouse) 📦⬅️

**Location**: `TransferController.php` → `store()` method

**Kaise Kaam Karta Hai:**
```php
// Transfer complete hone par:
// FROM Warehouse: Quantity decrease
$from_warehouse->qty = $from_warehouse->qty - $quantity;
```

**Process:**
1. Transfer create karein (From Warehouse → To Warehouse)
2. Status = **"Completed"** set karein
3. Save karne par:
   - **From Warehouse** ki quantity **-** hoti hai
   - **To Warehouse** ki quantity **+** hoti hai

**Note**: Transfer mein **total product quantity same** rehti hai, sirf warehouse change hota hai.

**URL**: `/transfers/create`

---

### 4. **Adjustment Module** (Manual Decrease) ➖

**Location**: `AdjustmentController.php` → `store()` method

**Kaise Kaam Karta Hai:**
```php
// Manual adjustment:
if($action == '-') {
    $product->qty = $product->qty - $adjustment_qty;
    $product_warehouse->qty = $product_warehouse->qty - $adjustment_qty;
}
```

**Process:**
1. Adjustment create karein
2. Action = **"-"** select karein
3. Quantity enter karein
4. Save karne par:
   - **Product table** mein quantity **-** ho jati hai
   - **Product_Warehouse table** mein **-** ho jati hai

**Use Case:**
- Stock lost/damaged
- Theft
- Expired products removal
- Manual correction

**URL**: `/adjustments/create`

---

### 5. **Production Module** (Raw Materials) 🏭

**Location**: `ProductionController.php` → `store()` method

**Kaise Kaam Karta Hai:**
```php
// Production mein raw materials use hone par:
$raw_material->qty = $raw_material->qty - $used_qty;
$raw_material_warehouse->qty = $raw_material_warehouse->qty - $used_qty;
```

**Process:**
1. Production order create karein
2. Raw materials select karein
3. Production complete karne par:
   - **Raw materials** ki quantity **-** hoti hai
   - **Finished product** ki quantity **+** hoti hai

**Example:**
- Raw Material: Sugar 10kg (-10kg)
- Finished Product: Soft Drink 100 bottles (+100)

**URL**: `/productions/create` (agar Manufacturing module enabled hai)

---

## Inventory Kahan Dikhti Hai? 👀

### 1. **Products List Page** 📋

**URL**: `/products`

**Kya Dikhta Hai:**
- Har product ki **total quantity** (sab warehouses ka sum)
- Product details (name, code, price, etc.)

**Access:**
- Menu: **Products** → **Product List**

---

### 2. **Product Details Page** 📄

**URL**: `/products/{id}`

**Kya Dikhta Hai:**
- Product ki **total quantity**
- **Warehouse-wise quantity** breakdown
- Batch-wise quantity (agar batch tracking hai)
- Variant-wise quantity (agar variants hain)

**Access:**
- Products list se product par click karein

---

### 3. **Warehouse Stock Report** 📊

**URL**: `/reports/warehouse-stock` (ya similar)

**Kya Dikhta Hai:**
- Har warehouse mein kitni quantity hai
- Product-wise breakdown
- Low stock alerts

**Access:**
- Menu: **Reports** → **Warehouse Stock**

---

### 4. **Sale Create Page** 💰

**URL**: `/sales/create`

**Kya Dikhta Hai:**
- Product select karte waqt **available stock** dikhta hai
- Warehouse select karne ke baad us warehouse ki quantity dikhti hai
- Low stock warning (agar quantity kam hai)

**Process:**
1. Warehouse select karein
2. Product search karein
3. Available quantity automatically show hoti hai

---

### 5. **Purchase Create Page** 📥

**URL**: `/purchases/create`

**Kya Dikhta Hai:**
- Current stock level
- Purchase karne ke baad expected stock level

**Process:**
1. Warehouse select karein
2. Product select karein
3. Current stock dikhta hai

---

### 6. **Transfer Create Page** 📦

**URL**: `/transfers/create`

**Kya Dikhta Hai:**
- **From Warehouse** ki available quantity
- Product select karte waqt stock check hota hai
- Transfer ke baad expected stock levels

**Process:**
1. From Warehouse select karein
2. Product search karein
3. Available quantity dikhti hai

---

### 7. **Dashboard** 📈

**URL**: `/dashboard` (ya home page)

**Kya Dikhta Hai:**
- Total products count
- Low stock alerts
- Recent inventory movements

**Access:**
- Login ke baad home page par

---

## Complete Inventory Flow Example 🔄

### Scenario: Soft Drink Company

**Step 1: Purchase (Inventory Increase)**
```
Supplier: Sugar Supplier
Product: Sugar (100kg)
Warehouse: Main Warehouse
Status: Received

Result:
- products.qty: 0 → 100
- product_warehouse.qty (Main Warehouse): 0 → 100
```

**Step 2: Production (Raw Materials Decrease, Finished Goods Increase)**
```
Raw Material: Sugar (10kg)
Finished Product: Soft Drink (100 bottles)
Warehouse: Main Warehouse

Result:
- Sugar: 100kg → 90kg (-10kg)
- Soft Drink: 0 → 100 bottles (+100)
```

**Step 3: Transfer (Warehouse Change)**
```
From: Main Warehouse
To: Regional Warehouse
Product: Soft Drink (50 bottles)
Status: Completed

Result:
- Main Warehouse: 100 → 50 (-50)
- Regional Warehouse: 0 → 50 (+50)
- Total Soft Drink: 100 (same)
```

**Step 4: Sale (Inventory Decrease)**
```
Customer: Retail Store
Product: Soft Drink (20 bottles)
Warehouse: Regional Warehouse
Status: Completed

Result:
- Regional Warehouse: 50 → 30 (-20)
- Total Soft Drink: 100 → 80 (-20)
```

**Step 5: Return Sale (Inventory Increase)**
```
Customer: Retail Store (return)
Product: Soft Drink (2 bottles - damaged)
Warehouse: Regional Warehouse

Result:
- Regional Warehouse: 30 → 32 (+2)
- Total Soft Drink: 80 → 82 (+2)
```

---

## Important Points ⚠️

### 1. **Status Matters** ✅
- **Purchase**: Sirf **"Received"** status par inventory increase hoti hai
- **Sale**: Sirf **"Completed"** status par inventory decrease hoti hai
- **Transfer**: Sirf **"Completed"** status par warehouse change hota hai

### 2. **Warehouse Selection** 🏢
- Har transaction mein **warehouse select karna zaroori** hai
- Inventory **warehouse-wise** track hoti hai
- Product ki total quantity = sab warehouses ka sum

### 3. **Batch Tracking** 🏷️
- Agar batch tracking enabled hai, to:
  - **ProductBatch** table mein bhi quantity track hoti hai
  - Batch-wise inventory manage hoti hai

### 4. **Variant Products** 🎨
- Agar product variants hain, to:
  - **ProductVariant** table mein bhi quantity track hoti hai
  - Har variant ki alag quantity hoti hai

### 5. **Unit Conversion** 📏
- Units convert hote hain (kg → gram, etc.)
- System automatically unit conversion karta hai
- Final quantity **base unit** mein store hoti hai

---

## Database Tables Structure 📊

### `products` Table
```sql
- id
- name
- code
- qty (TOTAL QUANTITY - sab warehouses ka sum)
- type
- unit_id
- is_active
```

### `product_warehouse` Table
```sql
- id
- product_id
- warehouse_id
- qty (WAREHOUSE-SPECIFIC QUANTITY)
- variant_id (agar variant hai)
- product_batch_id (agar batch tracking hai)
- price
- imei_number (agar IMEI tracking hai)
```

### `product_batches` Table (Batch Tracking)
```sql
- id
- product_id
- batch_no
- qty (BATCH-SPECIFIC QUANTITY)
- expired_date
```

### `product_variants` Table (Variant Products)
```sql
- id
- product_id
- variant_id
- item_code
- qty (VARIANT-SPECIFIC QUANTITY)
```

---

## Summary Table 📋

| Module | Inventory Change | When | Status Required |
|--------|----------------|------|-----------------|
| **Purchase** | ➕ Increase | Order Received | Status = Received |
| **Sale** | ➖ Decrease | Order Completed | Sale Status = Completed |
| **Transfer** | ➖➖ From Warehouse | Transfer Completed | Status = Completed |
| **Transfer** | ➕➕ To Warehouse | Transfer Completed | Status = Completed |
| **Return Sale** | ➕ Increase | Return Processed | - |
| **Return Purchase** | ➖ Decrease | Return Processed | - |
| **Adjustment (+)** | ➕ Increase | Adjustment Saved | - |
| **Adjustment (-)** | ➖ Decrease | Adjustment Saved | - |
| **Production (Raw)** | ➖ Decrease | Production Completed | - |
| **Production (Finished)** | ➕ Increase | Production Completed | - |

---

## Common Issues & Solutions 🔧

### Issue 1: Inventory Update Nahin Ho Rahi
**Solution:**
- Check karein ke **status** sahi hai ya nahin
- Purchase: Status = "Received"
- Sale: Sale Status = "Completed"
- Transfer: Status = "Completed"

### Issue 2: Warehouse Select Nahin Kiya
**Solution:**
- Har transaction mein **warehouse select karna zaroori** hai
- Agar warehouse select nahin kiya, to inventory update nahin hogi

### Issue 3: Quantity Negative Ho Rahi Hai
**Solution:**
- Check karein ke **sufficient stock** hai ya nahin
- Sale create se pehle stock verify karein
- Low stock alerts setup karein

### Issue 4: Batch/Variant Quantity Wrong
**Solution:**
- Batch tracking enabled hai ya nahin check karein
- Variant products ke liye variant select karein
- Product settings verify karein

---

## Best Practices ✅

1. **Regular Stock Checks**
   - Daily inventory verification
   - Weekly stock reports review
   - Monthly physical stock count

2. **Status Management**
   - Purchase orders ko "Received" status par set karein
   - Sale orders ko "Completed" status par set karein
   - Transfer ko "Completed" status par set karein

3. **Warehouse Organization**
   - Har warehouse ko properly organize karein
   - Warehouse-wise stock reports regular check karein

4. **Batch Tracking**
   - Expiry dates track karein
   - FIFO (First In First Out) follow karein

5. **Low Stock Alerts**
   - Low stock alerts setup karein
   - Reorder points set karein

---

## Conclusion 🎯

Yeh system **complete inventory management** provide karta hai:
- ✅ Real-time stock tracking
- ✅ Warehouse-wise inventory
- ✅ Batch/variant tracking
- ✅ Automatic updates
- ✅ Comprehensive reports

**Happy Inventory Managing! 📦🚀**

