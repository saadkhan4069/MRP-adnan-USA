# CPG (Consumer Packaged Goods) Management System - Complete Guide

## CPG Kya Hai?

**CPG (Consumer Packaged Goods)** wo products hain jo consumers daily use karte hain jaise:
- Food & Beverages (Khana aur Peena)
- Personal Care Products (Soap, Shampoo, Toothpaste)
- Household Items (Detergent, Cleaning Products)
- Snacks & Confectionery (Biscuits, Chocolates)

Yeh products fast-moving hote hain aur regular basis par restock kiye jaate hain.

---

## Project Overview - CPG Context Mein

Yeh project **CPG Supply Chain Management** ke liye banaya gaya hai jahan:

1. **Suppliers** se products **Purchase** kiye jaate hain
2. **Warehouses** mein **Inventory** manage hoti hai
3. **Customers** ko products **Sale** kiye jaate hain
4. **Warehouses** ke beech **Transfer** hota hai

---

## Main Modules - CPG Workflow

### 1. **Purchase Order (PO) Module** 📦

**Kya Hai:**
- Suppliers se products order karna
- CPG mein: Raw materials, packaging materials, finished goods suppliers se order karna

**Kaise Use Karein:**

#### Step 1: Purchase Order Create Karein
```
URL: http://localhost:8000/purchases/create
```

**Process:**
1. **Date** select karein
2. **Reference No** enter karein (optional)
3. **Customer** select karein (jo supplier ko represent karta hai)
4. **Warehouse/Production** select karein (kahan products aayenge)
5. **Currency** select karein

#### Step 2: Products Add Karein
1. **Select Product** field mein product code ya name type karein
2. Product select karne par automatically:
   - **Quantity** set hoti hai
   - **Supplier** dropdown show hota hai
   - **Net Unit Price** calculate hota hai
   - **Tax** apply hota hai
   - **Subtotal** calculate hota hai

#### Step 3: Additional Information
- **Batch No**: Product batch number (agar required ho)
- **Lot No**: Production lot number
- **MOQ**: Minimum Order Quantity
- **Shipping Term**: Pre-Paid ya Post-Paid
- **Shipping Cost**: Transport cost

#### Step 4: Save Purchase Order
- **Submit** button click karein
- Purchase order save ho jayega
- Inventory automatically update ho jayegi

**CPG Context:**
- Suppliers se bulk orders
- Quality control ke liye batch tracking
- Shipping terms important (CPG mein fast delivery chahiye)

---

### 2. **Sale Order Module** 💰

**Kya Hai:**
- Customers ko products bechna
- CPG mein: Retailers, distributors, ya end consumers ko products sell karna

**Kaise Use Karein:**

#### Step 1: Sale Order Create Karein
```
URL: http://localhost:8000/sales/create
```

**Process:**
1. **Date** select karein
2. **Reference No** enter karein
3. **Ship To (Customer)** select karein
4. **Ship From (Warehouse)** select karein
5. **Bill To (Biller)** select karein
6. **Currency** select karein

#### Step 2: Products Add Karein
1. **Select Product** field mein product code ya name type karein
2. Product select karne par:
   - **Quantity** set hoti hai
   - **Supplier** select karein (product ka supplier)
   - **Net Unit Price** automatically calculate hota hai
   - **Discount** apply kar sakte hain
   - **Tax** calculate hota hai
   - **Subtotal** show hota hai

#### Step 3: Order Details
- **Order Tax**: Overall tax (agar chahiye)
- **Order Discount**: Overall discount
- **Shipping Cost**: Delivery charges
- **Grand Total**: Final amount

#### Step 4: Payment
- **Payment Method**: Cash, Credit Card, Cheque
- **Paid Amount**: Kitna pay kiya
- **Due Amount**: Kitna baaki hai

**CPG Context:**
- Fast checkout (CPG products quick sale hote hain)
- Multiple payment options
- Customer credit management

---

### 3. **Transfer Module** 🔄

**Kya Hai:**
- Ek warehouse se doosre warehouse mein products transfer karna
- CPG mein: Distribution centers, regional warehouses ke beech stock movement

**Kaise Use Karein:**

#### Step 1: Transfer Create Karein
```
URL: http://localhost:8000/transfers/create
```

**Process:**
1. **Date** select karein
2. **From Warehouse** select karein (kahan se transfer karna hai)
3. **To Warehouse** select karein (kahan transfer karna hai)
4. **Status** select karein:
   - **Completed**: Transfer complete ho gaya
   - **Pending**: Abhi pending hai
   - **Sent**: Bhej diya hai

#### Step 2: Products Add Karein
1. **From Warehouse** select karne ke baad products available honge
2. **Select Product** field mein product code ya name type karein
3. Product select karne par:
   - **Quantity** set hoti hai
   - **Batch No** (agar required ho)
   - **Net Unit Cost** calculate hota hai
   - **Tax** apply hota hai
   - **Subtotal** calculate hota hai

#### Step 3: Transfer Details
- **Shipping Cost**: Transport charges
- **Note**: Additional information
- **Attach Document**: Transfer documents (optional)

**CPG Context:**
- Multi-warehouse management
- Stock balancing between locations
- Fast transfer for high-demand products

---

### 4. **Inventory Management** 📊

**Kya Hai:**
- Products ki stock tracking
- CPG mein: Real-time inventory updates, low stock alerts

**Features:**
- **Product-Warehouse** tracking
- **Batch/Lot** tracking
- **Stock Reports**
- **Low Stock Alerts**

**Access:**
- Products page se inventory dekh sakte hain
- Reports section mein detailed inventory reports

---

## CPG Workflow - Complete Process

### Typical CPG Supply Chain Flow:

```
1. SUPPLIER → Purchase Order → WAREHOUSE (Raw Materials)
                    ↓
2. WAREHOUSE → Manufacturing/Processing → Finished Goods
                    ↓
3. WAREHOUSE → Transfer → Distribution Centers
                    ↓
4. DISTRIBUTION CENTER → Sale Order → CUSTOMER (Retailer/Distributor)
                    ↓
5. CUSTOMER → End Consumer
```

### Step-by-Step Example:

**Scenario: Soft Drink Company**

1. **Purchase Order**:
   - Supplier: "Sugar Supplier"
   - Products: Sugar, Flavoring, Packaging
   - Warehouse: "Main Production Warehouse"
   - Status: Order placed

2. **Production** (Manufacturing Module):
   - Raw materials use karke finished products banaaye
   - Batch numbers assign kiye
   - Quality check complete

3. **Transfer**:
   - From: "Main Production Warehouse"
   - To: "Regional Distribution Center - North"
   - Products: Finished soft drinks
   - Quantity: 10,000 units

4. **Sale Order**:
   - Customer: "Retail Chain Store"
   - Warehouse: "Regional Distribution Center - North"
   - Products: Soft drinks
   - Payment: Credit (30 days)

---

## Important Features - CPG Ke Liye

### 1. **Batch/Lot Tracking** 🏷️
- Har product ka batch number track karein
- Expiry dates manage karein
- Quality control ke liye important

### 2. **Multi-Warehouse Management** 🏢
- Multiple warehouses manage karein
- Stock levels real-time update
- Transfer between warehouses easy

### 3. **Supplier Management** 👥
- Multiple suppliers track karein
- Supplier performance monitor karein
- Payment terms manage karein

### 4. **Customer Management** 👤
- Customer groups (Retailers, Distributors, etc.)
- Credit limits set karein
- Payment history track karein

### 5. **Reports & Analytics** 📈
- Sales reports
- Purchase reports
- Inventory reports
- Profit/Loss analysis

---

## Common Issues & Solutions

### Issue 1: Product Select Karne Par Price Nahin Aa Raha
**Solution:**
- Warehouse select karein pehle
- Customer select karein (sale ke liye)
- Product code correctly type karein
- Browser refresh karein agar issue ho

### Issue 2: Subtotal 0.0000 Show Ho Raha Hai
**Solution:**
- Product ka price properly set hai ya nahin check karein
- Tax rate check karein
- Quantity valid hai ya nahin verify karein
- Calculation function properly call ho raha hai (fixed in latest update)

### Issue 3: Transfer Mein Products Nahin Dikhte
**Solution:**
- "From Warehouse" select karein pehle
- Check karein ke warehouse mein stock hai ya nahin
- Product active hai ya nahin verify karein

---

## Best Practices - CPG Management

### 1. **Regular Stock Updates** ✅
- Daily inventory checks
- Low stock alerts setup karein
- Fast-moving products ko priority dein

### 2. **Batch Management** ✅
- Har batch ka proper tracking
- FIFO (First In First Out) follow karein
- Expiry dates monitor karein

### 3. **Supplier Relations** ✅
- Regular communication
- Payment terms clear rakhein
- Quality standards maintain karein

### 4. **Customer Service** ✅
- Fast order processing
- Accurate delivery
- Credit management

### 5. **Reports Review** ✅
- Weekly sales reports
- Monthly inventory reports
- Quarterly financial analysis

---

## Module Access URLs

| Module | URL | Description |
|--------|-----|-------------|
| Purchase List | `/purchases` | All purchase orders |
| Create Purchase | `/purchases/create` | New purchase order |
| Sale List | `/sales` | All sale orders |
| Create Sale | `/sales/create` | New sale order |
| Transfer List | `/transfers` | All transfers |
| Create Transfer | `/transfers/create` | New transfer |
| Products | `/products` | Product management |
| Inventory | `/products` | Stock levels |
| Reports | `/reports` | Various reports |

---

## Technical Details

### Database Structure:
- **purchases**: Purchase orders
- **sales**: Sale orders
- **transfers**: Warehouse transfers
- **products**: Product information
- **product_warehouse**: Stock levels
- **suppliers**: Supplier details
- **customers**: Customer details

### Key Calculations:
- **Subtotal** = (Unit Price × Quantity) + Tax - Discount
- **Grand Total** = Subtotal + Shipping Cost - Order Discount + Order Tax
- **Inventory Update**: Purchase/Transfer = +Stock, Sale = -Stock

---

## Support & Updates

Agar koi issue ho ya feature chahiye:
1. Error messages check karein
2. Browser console check karein (F12)
3. Database connection verify karein
4. Logs check karein: `storage/logs/laravel.log`

---

## Conclusion

Yeh system CPG businesses ke liye complete solution hai:
- ✅ Purchase management
- ✅ Inventory tracking
- ✅ Sales processing
- ✅ Warehouse transfers
- ✅ Financial reporting

Sab kuch CPG supply chain ke requirements ke hisab se customize kiya gaya hai.

**Happy Managing! 🚀**

