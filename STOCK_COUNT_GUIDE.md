# Stock Count Module - Complete Guide (Roman Urdu)

## Stock Count Kya Hai? 📊

**Stock Count** ek **physical inventory counting** process hai jahan:

1. **System ki expected quantity** (database mein jo hai)
2. **Physical warehouse mein actual quantity** (jo actually hai)
3. **Dono ko compare** karke **difference** nikalte hain
4. **Adjustment** create karte hain agar difference hai

**Simple Words Mein:**
- Warehouse mein physically jake products count karte hain
- System mein jo quantity hai usse compare karte hain
- Agar difference hai to adjustment create karte hain

---

## Stock Count Kaise Kaam Karta Hai? 🔄

### **Step 1: Stock Count Create Karein** 📝

**URL**: `http://localhost:8000/stock-count`

**Process:**
1. **"Count Stock"** button click karein
2. **Warehouse** select karein (kis warehouse ka count karna hai)
3. **Type** select karein:
   - **Full**: Puri warehouse ka count
   - **Partial**: Specific category/brand ka count
4. **Category** select karein (agar Partial hai)
5. **Brand** select karein (agar Partial hai)
6. **Submit** click karein

**Kya Hota Hai:**
- System ek **CSV file** generate karta hai
- File mein yeh columns hote hain:
  - Product Name
  - Product Code
  - IMEI/Serial Numbers
  - **Expected** (System mein jo quantity hai)
  - **Counted** (Yeh column empty hota hai - aap fill karenge)

**File Location**: `public/stock_count/` folder mein save hoti hai

---

### **Step 2: Physical Count Karein** 📦

**Process:**
1. **Initial File Download** karein (CSV format)
2. Warehouse mein jake **physically products count** karein
3. CSV file mein **"Counted"** column mein actual quantity enter karein
4. File save karein

**Example CSV:**
```csv
Product Name, Product Code, IMEI, Expected, Counted
Soft Drink, SD001, , 100, 95
Biscuits, BIS001, , 200, 205
Chocolates, CHO001, , 150, 148
```

**Note:**
- **Expected**: System mein jo quantity hai (automatically fill)
- **Counted**: Aap physically count karke enter karein

---

### **Step 3: Final File Upload Karein** 📤

**Process:**
1. Stock Count list mein **"Finalize"** button click karein
2. **Updated CSV file** upload karein (jisme "Counted" column fill ho)
3. **Note** add karein (optional)
4. **Submit** click karein

**Kya Hota Hai:**
- System final file save karta hai
- **Difference calculate** hota hai:
  - Difference = Counted - Expected
  - Positive: Zyada stock mila
  - Negative: Kam stock mila

---

### **Step 4: Final Report Dekhein** 📊

**Process:**
1. Stock Count list mein **"Final Report"** button click karein
2. Report mein dikhega:
   - **Expected**: System quantity
   - **Counted**: Physical quantity
   - **Difference**: Kitna difference hai
   - **Cost**: Difference ka cost (product cost × difference)

**Example:**
```
Product: Soft Drink [SD001]
Expected: 100
Counted: 95
Difference: -5 (5 units kam)
Cost: -500 (agar cost 100 hai to 5 × 100 = 500)
```

---

### **Step 5: Adjustment Create Karein** ➕➖

**Process:**
1. Final Report mein **"Add Adjustment"** button click karein
2. System automatically:
   - **Positive difference** ke liye: **+** adjustment (stock increase)
   - **Negative difference** ke liye: **-** adjustment (stock decrease)
3. Adjustment create ho jata hai
4. Inventory automatically update ho jati hai

**Example:**
- Expected: 100, Counted: 95
- Adjustment: -5 (5 units decrease)
- Inventory: 100 → 95

---

## Stock Count Types 📋

### 1. **Full Stock Count** 📦

**Kya Hai:**
- Puri warehouse ka count
- Sab products count kiye jaate hain

**Use Case:**
- Monthly/Quarterly inventory check
- Year-end stock verification
- Complete warehouse audit

**Process:**
1. Type = **"Full"** select karein
2. Category/Brand select nahi karna
3. Sab products include honge

---

### 2. **Partial Stock Count** 🎯

**Kya Hai:**
- Specific category ya brand ka count
- Sirf selected products count kiye jaate hain

**Use Case:**
- Fast-moving products ka regular check
- Specific category ka audit
- Brand-wise verification

**Process:**
1. Type = **"Partial"** select karein
2. **Category** select karein (optional)
3. **Brand** select karein (optional)
4. Sirf selected products include honge

---

## Stock Count Workflow Diagram 🔄

```
1. CREATE STOCK COUNT
   ↓
2. DOWNLOAD INITIAL FILE (CSV)
   ↓
3. PHYSICAL COUNT (Warehouse mein jake count karein)
   ↓
4. UPDATE CSV FILE (Counted column fill karein)
   ↓
5. UPLOAD FINAL FILE
   ↓
6. SYSTEM CALCULATES DIFFERENCE
   ↓
7. VIEW FINAL REPORT
   ↓
8. CREATE ADJUSTMENT (Agar difference hai)
   ↓
9. INVENTORY UPDATED ✅
```

---

## Important Points ⚠️

### 1. **CSV File Format** 📄

**Columns:**
- Product Name
- Product Code
- IMEI/Serial Numbers
- Expected (System quantity - automatically fill)
- Counted (Physical quantity - aap fill karein)

**Important:**
- **Expected column** mat change karein
- Sirf **Counted column** update karein
- File format same rakhein

---

### 2. **Difference Calculation** 🧮

**Formula:**
```
Difference = Counted - Expected
```

**Examples:**
- Expected: 100, Counted: 95 → Difference: -5 (5 kam)
- Expected: 100, Counted: 105 → Difference: +5 (5 zyada)
- Expected: 100, Counted: 100 → Difference: 0 (same)

---

### 3. **Adjustment Creation** ➕➖

**Automatic:**
- Positive difference → **+** adjustment (stock increase)
- Negative difference → **-** adjustment (stock decrease)

**Process:**
- Adjustment module automatically create hota hai
- Inventory automatically update hoti hai
- Adjustment record save hota hai

---

### 4. **File Management** 📁

**Files:**
- **Initial File**: System generate karta hai (expected quantities)
- **Final File**: Aap upload karte hain (counted quantities)

**Location:**
- `public/stock_count/` folder
- Files download kar sakte hain
- Files CSV format mein hote hain

---

## Use Cases - CPG Context 🏭

### 1. **Monthly Stock Verification** 📅

**Process:**
- Har month end par full stock count
- Physical count vs system count compare
- Adjustments create karein

**Benefit:**
- Inventory accuracy maintain
- Theft/damage detect
- Stock discrepancies fix

---

### 2. **Fast-Moving Products Check** ⚡

**Process:**
- Fast-moving products ka partial count
- Regular basis par check
- Quick adjustments

**Benefit:**
- High-demand products accurate
- Stockouts prevent
- Customer satisfaction

---

### 3. **Category-Wise Audit** 📊

**Process:**
- Specific category ka count
- Brand-wise verification
- Targeted adjustments

**Benefit:**
- Category performance track
- Brand-wise accuracy
- Focused inventory management

---

### 4. **Year-End Inventory** 📆

**Process:**
- Complete warehouse count
- All products verify
- Final adjustments

**Benefit:**
- Annual inventory accuracy
- Financial reporting
- Compliance

---

## Step-by-Step Example 📝

### **Scenario: Soft Drink Warehouse**

**Step 1: Create Stock Count**
```
Warehouse: Main Warehouse
Type: Full
Submit
```

**Step 2: Download Initial File**
```
File: 20250119-143022.csv
Columns: Product Name, Code, IMEI, Expected, Counted
```

**Step 3: Physical Count**
```
Warehouse mein jake count:
- Soft Drink: 95 units (Expected: 100)
- Biscuits: 205 units (Expected: 200)
- Chocolates: 148 units (Expected: 150)
```

**Step 4: Update CSV**
```csv
Product Name, Code, IMEI, Expected, Counted
Soft Drink, SD001, , 100, 95
Biscuits, BIS001, , 200, 205
Chocolates, CHO001, , 150, 148
```

**Step 5: Upload Final File**
```
Finalize button → Upload file → Submit
```

**Step 6: View Report**
```
Soft Drink: Expected 100, Counted 95, Difference -5
Biscuits: Expected 200, Counted 205, Difference +5
Chocolates: Expected 150, Counted 148, Difference -2
```

**Step 7: Create Adjustment**
```
Soft Drink: -5 adjustment
Biscuits: +5 adjustment
Chocolates: -2 adjustment
```

**Step 8: Inventory Updated**
```
Soft Drink: 100 → 95
Biscuits: 200 → 205
Chocolates: 150 → 148
```

---

## Common Issues & Solutions 🔧

### Issue 1: CSV File Format Wrong
**Solution:**
- Expected column mat change karein
- Sirf Counted column update karein
- File format same rakhein

### Issue 2: Difference Calculate Nahin Ho Raha
**Solution:**
- Final file properly upload karein
- CSV format check karein
- Counted column properly fill karein

### Issue 3: Adjustment Create Nahin Ho Raha
**Solution:**
- Final report check karein
- Difference hai ya nahi verify karein
- "Add Adjustment" button click karein

### Issue 4: File Download Nahin Ho Raha
**Solution:**
- File permissions check karein
- `public/stock_count/` folder exist kare
- Browser settings check karein

---

## Best Practices ✅

### 1. **Regular Stock Counts** 📅
- Monthly basis par count
- Fast-moving products ka frequent check
- Year-end complete audit

### 2. **Accurate Counting** 📊
- Physical count carefully karein
- Double-check karein
- Team members verify karein

### 3. **File Management** 📁
- Initial file backup rakhein
- Final file properly save karein
- Files organize rakhein

### 4. **Timely Adjustments** ⏰
- Count ke baad jaldi adjustment create karein
- Inventory accuracy maintain karein
- Reports review karein

### 5. **Documentation** 📝
- Notes add karein (kya issue tha)
- Reasons document karein
- History maintain karein

---

## Summary Table 📋

| Step | Action | Result |
|------|--------|--------|
| 1 | Create Stock Count | Initial CSV file generated |
| 2 | Download File | Expected quantities file |
| 3 | Physical Count | Warehouse mein count |
| 4 | Update CSV | Counted column fill |
| 5 | Upload Final File | Final file uploaded |
| 6 | View Report | Difference calculated |
| 7 | Create Adjustment | Inventory updated |

---

## Database Tables 📊

### `stock_counts` Table
```sql
- id
- reference_no (Unique reference)
- warehouse_id (Kis warehouse ka count)
- category_id (Categories - comma separated)
- brand_id (Brands - comma separated)
- user_id (Kisne create kiya)
- type (full/partial)
- initial_file (Initial CSV file)
- final_file (Final CSV file)
- note (Additional notes)
- is_adjusted (Adjustment create hua ya nahi)
```

---

## Conclusion 🎯

Stock Count module **physical inventory verification** ke liye complete solution hai:

- ✅ **Initial file generation** (Expected quantities)
- ✅ **Physical counting** (Actual quantities)
- ✅ **Difference calculation** (Expected vs Counted)
- ✅ **Automatic adjustments** (Inventory update)
- ✅ **Reports & tracking** (History maintain)

**Yeh module CPG businesses ke liye bahut important hai** kyunki:
- Inventory accuracy maintain hoti hai
- Theft/damage detect hota hai
- Stock discrepancies fix hote hain
- Financial reporting accurate hoti hai

**Happy Stock Counting! 📊🚀**


