# Purchase List SQL Queries

Yeh file admin side se purchase list ke liye SQL queries contain karti hai. Jo data purchase list me display hota hai, wahi data yahan SQL queries se fetch kiya ja sakta hai.

## Table Structure

### Main Tables:
- `purchases` - Main purchase orders table
- `customers` - Customer information
- `suppliers` - Supplier information  
- `warehouses` - Warehouse information
- `return_purchases` - Returned purchases
- `product_purchases` - Purchase line items

---

## 1. Basic Purchase List Query (All Purchases)

```sql
SELECT 
    p.id,
    DATE_FORMAT(p.created_at, '%m-%d-%Y') AS date,
    p.po_no AS po_number,
    p.reference_no,
    COALESCE(c.name, s.name, u.name) AS customer_name,
    p.status AS purchase_status_code,
    CASE 
        WHEN p.status = 1 THEN 'Received'
        WHEN p.status = 2 THEN 'Partial'
        WHEN p.status = 3 THEN 'Pending'
        WHEN p.status = 4 THEN 'Ordered'
        WHEN p.status = 5 THEN 'In Process'
        WHEN p.status = 6 THEN 'Cancel'
        WHEN p.status = 7 THEN 'Complete'
        ELSE 'Unknown'
    END AS purchase_status,
    p.grand_total,
    COALESCE(SUM(rp.grand_total), 0) AS returned_amount,
    p.paid_amount,
    (p.grand_total - COALESCE(SUM(rp.grand_total), 0) - p.paid_amount) AS due_amount,
    p.payment_status AS payment_status_code,
    CASE 
        WHEN p.payment_status = 1 THEN 'Due'
        ELSE 'Paid'
    END AS payment_status,
    p.warehouse_id,
    w.name AS warehouse_name,
    p.customer_id,
    p.supplier_id,
    p.created_at,
    p.updated_at
FROM purchases p
LEFT JOIN customers c ON p.customer_id = c.id
LEFT JOIN suppliers s ON p.supplier_id = s.id
LEFT JOIN users u ON p.user_id = u.id
LEFT JOIN warehouses w ON p.warehouse_id = w.id
LEFT JOIN return_purchases rp ON p.id = rp.purchase_id
GROUP BY p.id, p.created_at, p.po_no, p.reference_no, p.status, p.grand_total, 
         p.paid_amount, p.payment_status, p.warehouse_id, w.name, p.customer_id, 
         p.supplier_id, c.name, s.name, u.name
ORDER BY p.created_at DESC;
```

---

## 2. Purchase List with Date Range Filter

```sql
SELECT 
    p.id,
    DATE_FORMAT(p.created_at, '%m-%d-%Y') AS date,
    p.po_no AS po_number,
    p.reference_no,
    COALESCE(c.name, s.name, u.name) AS customer_name,
    CASE 
        WHEN p.status = 1 THEN 'Received'
        WHEN p.status = 2 THEN 'Partial'
        WHEN p.status = 3 THEN 'Pending'
        WHEN p.status = 4 THEN 'Ordered'
        WHEN p.status = 5 THEN 'In Process'
        WHEN p.status = 6 THEN 'Cancel'
        WHEN p.status = 7 THEN 'Complete'
        ELSE 'Unknown'
    END AS purchase_status,
    FORMAT(p.grand_total, 3) AS grand_total,
    FORMAT(COALESCE(SUM(rp.grand_total), 0), 3) AS returned_amount,
    FORMAT(p.paid_amount, 3) AS paid_amount,
    FORMAT((p.grand_total - COALESCE(SUM(rp.grand_total), 0) - p.paid_amount), 3) AS due_amount,
    CASE 
        WHEN p.payment_status = 1 THEN 'Due'
        ELSE 'Paid'
    END AS payment_status
FROM purchases p
LEFT JOIN customers c ON p.customer_id = c.id
LEFT JOIN suppliers s ON p.supplier_id = s.id
LEFT JOIN users u ON p.user_id = u.id
LEFT JOIN return_purchases rp ON p.id = rp.purchase_id
WHERE DATE(p.created_at) >= '2025-01-01'  -- Starting Date
  AND DATE(p.created_at) <= '2025-12-31'  -- Ending Date
GROUP BY p.id, p.created_at, p.po_no, p.reference_no, p.status, p.grand_total, 
         p.paid_amount, p.payment_status, c.name, s.name, u.name
ORDER BY p.created_at DESC;
```

---

## 3. Purchase List with Warehouse Filter

```sql
SELECT 
    p.id,
    DATE_FORMAT(p.created_at, '%m-%d-%Y') AS date,
    p.po_no AS po_number,
    p.reference_no,
    COALESCE(c.name, s.name, u.name) AS customer_name,
    CASE 
        WHEN p.status = 1 THEN 'Received'
        WHEN p.status = 2 THEN 'Partial'
        WHEN p.status = 3 THEN 'Pending'
        WHEN p.status = 4 THEN 'Ordered'
        WHEN p.status = 5 THEN 'In Process'
        WHEN p.status = 6 THEN 'Cancel'
        WHEN p.status = 7 THEN 'Complete'
        ELSE 'Unknown'
    END AS purchase_status,
    FORMAT(p.grand_total, 3) AS grand_total,
    FORMAT(COALESCE(SUM(rp.grand_total), 0), 3) AS returned_amount,
    FORMAT(p.paid_amount, 3) AS paid_amount,
    FORMAT((p.grand_total - COALESCE(SUM(rp.grand_total), 0) - p.paid_amount), 3) AS due_amount,
    CASE 
        WHEN p.payment_status = 1 THEN 'Due'
        ELSE 'Paid'
    END AS payment_status,
    w.name AS warehouse_name
FROM purchases p
LEFT JOIN customers c ON p.customer_id = c.id
LEFT JOIN suppliers s ON p.supplier_id = s.id
LEFT JOIN users u ON p.user_id = u.id
LEFT JOIN warehouses w ON p.warehouse_id = w.id
LEFT JOIN return_purchases rp ON p.id = rp.purchase_id
WHERE p.warehouse_id = 1  -- Replace with your warehouse_id
GROUP BY p.id, p.created_at, p.po_no, p.reference_no, p.status, p.grand_total, 
         p.paid_amount, p.payment_status, w.name, c.name, s.name, u.name
ORDER BY p.created_at DESC;
```

---

## 4. Purchase List with Purchase Status Filter

```sql
SELECT 
    p.id,
    DATE_FORMAT(p.created_at, '%m-%d-%Y') AS date,
    p.po_no AS po_number,
    p.reference_no,
    COALESCE(c.name, s.name, u.name) AS customer_name,
    CASE 
        WHEN p.status = 1 THEN 'Received'
        WHEN p.status = 2 THEN 'Partial'
        WHEN p.status = 3 THEN 'Pending'
        WHEN p.status = 4 THEN 'Ordered'
        WHEN p.status = 5 THEN 'In Process'
        WHEN p.status = 6 THEN 'Cancel'
        WHEN p.status = 7 THEN 'Complete'
        ELSE 'Unknown'
    END AS purchase_status,
    FORMAT(p.grand_total, 3) AS grand_total,
    FORMAT(COALESCE(SUM(rp.grand_total), 0), 3) AS returned_amount,
    FORMAT(p.paid_amount, 3) AS paid_amount,
    FORMAT((p.grand_total - COALESCE(SUM(rp.grand_total), 0) - p.paid_amount), 3) AS due_amount,
    CASE 
        WHEN p.payment_status = 1 THEN 'Due'
        ELSE 'Paid'
    END AS payment_status
FROM purchases p
LEFT JOIN customers c ON p.customer_id = c.id
LEFT JOIN suppliers s ON p.supplier_id = s.id
LEFT JOIN users u ON p.user_id = u.id
LEFT JOIN return_purchases rp ON p.id = rp.purchase_id
WHERE p.status = 3  -- 1=Received, 2=Partial, 3=Pending, 4=Ordered, 5=In Process, 6=Cancel, 7=Complete
GROUP BY p.id, p.created_at, p.po_no, p.reference_no, p.status, p.grand_total, 
         p.paid_amount, p.payment_status, c.name, s.name, u.name
ORDER BY p.created_at DESC;
```

---

## 5. Purchase List with Payment Status Filter

```sql
SELECT 
    p.id,
    DATE_FORMAT(p.created_at, '%m-%d-%Y') AS date,
    p.po_no AS po_number,
    p.reference_no,
    COALESCE(c.name, s.name, u.name) AS customer_name,
    CASE 
        WHEN p.status = 1 THEN 'Received'
        WHEN p.status = 2 THEN 'Partial'
        WHEN p.status = 3 THEN 'Pending'
        WHEN p.status = 4 THEN 'Ordered'
        WHEN p.status = 5 THEN 'In Process'
        WHEN p.status = 6 THEN 'Cancel'
        WHEN p.status = 7 THEN 'Complete'
        ELSE 'Unknown'
    END AS purchase_status,
    FORMAT(p.grand_total, 3) AS grand_total,
    FORMAT(COALESCE(SUM(rp.grand_total), 0), 3) AS returned_amount,
    FORMAT(p.paid_amount, 3) AS paid_amount,
    FORMAT((p.grand_total - COALESCE(SUM(rp.grand_total), 0) - p.paid_amount), 3) AS due_amount,
    CASE 
        WHEN p.payment_status = 1 THEN 'Due'
        ELSE 'Paid'
    END AS payment_status
FROM purchases p
LEFT JOIN customers c ON p.customer_id = c.id
LEFT JOIN suppliers s ON p.supplier_id = s.id
LEFT JOIN users u ON p.user_id = u.id
LEFT JOIN return_purchases rp ON p.id = rp.purchase_id
WHERE p.payment_status = 1  -- 1=Due, 2=Paid
GROUP BY p.id, p.created_at, p.po_no, p.reference_no, p.status, p.grand_total, 
         p.paid_amount, p.payment_status, c.name, s.name, u.name
ORDER BY p.created_at DESC;
```

---

## 6. Complete Purchase List with All Filters Combined

```sql
SELECT 
    p.id,
    DATE_FORMAT(p.created_at, '%m-%d-%Y') AS date,
    p.po_no AS po_number,
    p.reference_no,
    COALESCE(c.name, s.name, u.name) AS customer_name,
    CASE 
        WHEN p.status = 1 THEN 'Received'
        WHEN p.status = 2 THEN 'Partial'
        WHEN p.status = 3 THEN 'Pending'
        WHEN p.status = 4 THEN 'Ordered'
        WHEN p.status = 5 THEN 'In Process'
        WHEN p.status = 6 THEN 'Cancel'
        WHEN p.status = 7 THEN 'Complete'
        ELSE 'Unknown'
    END AS purchase_status,
    FORMAT(p.grand_total, 3) AS grand_total,
    FORMAT(COALESCE(SUM(rp.grand_total), 0), 3) AS returned_amount,
    FORMAT(p.paid_amount, 3) AS paid_amount,
    FORMAT((p.grand_total - COALESCE(SUM(rp.grand_total), 0) - p.paid_amount), 3) AS due_amount,
    CASE 
        WHEN p.payment_status = 1 THEN 'Due'
        ELSE 'Paid'
    END AS payment_status,
    w.name AS warehouse_name,
    c.email AS customer_email,
    c.phone_number AS customer_phone,
    s.email AS supplier_email,
    s.phone_number AS supplier_phone
FROM purchases p
LEFT JOIN customers c ON p.customer_id = c.id
LEFT JOIN suppliers s ON p.supplier_id = s.id
LEFT JOIN users u ON p.user_id = u.id
LEFT JOIN warehouses w ON p.warehouse_id = w.id
LEFT JOIN return_purchases rp ON p.id = rp.purchase_id
WHERE DATE(p.created_at) >= '2025-01-01'  -- Starting Date (Change as needed)
  AND DATE(p.created_at) <= '2025-12-31'  -- Ending Date (Change as needed)
  AND (p.warehouse_id = 1 OR 1 = 0)  -- Warehouse Filter (Set to 0 for all, or specific warehouse_id)
  AND (p.status = 3 OR 0 = 0)  -- Purchase Status Filter (Set to 0 for all, or specific status)
  AND (p.payment_status = 1 OR 0 = 0)  -- Payment Status Filter (Set to 0 for all, 1=Due, 2=Paid)
GROUP BY p.id, p.created_at, p.po_no, p.reference_no, p.status, p.grand_total, 
         p.paid_amount, p.payment_status, w.name, c.name, s.name, u.name, 
         c.email, c.phone_number, s.email, s.phone_number
ORDER BY p.created_at DESC
LIMIT 10 OFFSET 0;  -- Pagination: LIMIT = records per page, OFFSET = start position
```

---

## 7. Purchase List with Search Functionality

```sql
SELECT 
    p.id,
    DATE_FORMAT(p.created_at, '%m-%d-%Y') AS date,
    p.po_no AS po_number,
    p.reference_no,
    COALESCE(c.name, s.name, u.name) AS customer_name,
    CASE 
        WHEN p.status = 1 THEN 'Received'
        WHEN p.status = 2 THEN 'Partial'
        WHEN p.status = 3 THEN 'Pending'
        WHEN p.status = 4 THEN 'Ordered'
        WHEN p.status = 5 THEN 'In Process'
        WHEN p.status = 6 THEN 'Cancel'
        WHEN p.status = 7 THEN 'Complete'
        ELSE 'Unknown'
    END AS purchase_status,
    FORMAT(p.grand_total, 3) AS grand_total,
    FORMAT(COALESCE(SUM(rp.grand_total), 0), 3) AS returned_amount,
    FORMAT(p.paid_amount, 3) AS paid_amount,
    FORMAT((p.grand_total - COALESCE(SUM(rp.grand_total), 0) - p.paid_amount), 3) AS due_amount,
    CASE 
        WHEN p.payment_status = 1 THEN 'Due'
        ELSE 'Paid'
    END AS payment_status
FROM purchases p
LEFT JOIN customers c ON p.customer_id = c.id
LEFT JOIN suppliers s ON p.supplier_id = s.id
LEFT JOIN users u ON p.user_id = u.id
LEFT JOIN return_purchases rp ON p.id = rp.purchase_id
LEFT JOIN product_purchases pp ON p.id = pp.purchase_id
WHERE (
    p.reference_no LIKE '%SEARCH_TERM%'  -- Replace SEARCH_TERM with actual search value
    OR p.po_no LIKE '%SEARCH_TERM%'
    OR c.name LIKE '%SEARCH_TERM%'
    OR s.name LIKE '%SEARCH_TERM%'
    OR pp.imei_number LIKE '%SEARCH_TERM%'
)
GROUP BY p.id, p.created_at, p.po_no, p.reference_no, p.status, p.grand_total, 
         p.paid_amount, p.payment_status, c.name, s.name, u.name
ORDER BY p.created_at DESC;
```

---

## 8. Purchase Statistics Summary

```sql
SELECT 
    COUNT(*) AS total_purchases,
    SUM(p.grand_total) AS total_grand_total,
    SUM(p.paid_amount) AS total_paid_amount,
    SUM(COALESCE(SUM(rp.grand_total), 0)) AS total_returned_amount,
    SUM((p.grand_total - COALESCE(SUM(rp.grand_total), 0) - p.paid_amount)) AS total_due_amount,
    COUNT(CASE WHEN p.status = 1 THEN 1 END) AS received_count,
    COUNT(CASE WHEN p.status = 2 THEN 1 END) AS partial_count,
    COUNT(CASE WHEN p.status = 3 THEN 1 END) AS pending_count,
    COUNT(CASE WHEN p.status = 4 THEN 1 END) AS ordered_count,
    COUNT(CASE WHEN p.status = 5 THEN 1 END) AS in_process_count,
    COUNT(CASE WHEN p.status = 6 THEN 1 END) AS cancelled_count,
    COUNT(CASE WHEN p.status = 7 THEN 1 END) AS complete_count,
    COUNT(CASE WHEN p.payment_status = 1 THEN 1 END) AS due_count,
    COUNT(CASE WHEN p.payment_status = 2 THEN 1 END) AS paid_count
FROM purchases p
LEFT JOIN return_purchases rp ON p.id = rp.purchase_id
WHERE DATE(p.created_at) >= '2025-01-01'  -- Starting Date
  AND DATE(p.created_at) <= '2025-12-31'  -- Ending Date
GROUP BY p.id;
```

---

## 9. Purchase List for Specific Customer

```sql
SELECT 
    p.id,
    DATE_FORMAT(p.created_at, '%m-%d-%Y') AS date,
    p.po_no AS po_number,
    p.reference_no,
    c.name AS customer_name,
    CASE 
        WHEN p.status = 1 THEN 'Received'
        WHEN p.status = 2 THEN 'Partial'
        WHEN p.status = 3 THEN 'Pending'
        WHEN p.status = 4 THEN 'Ordered'
        WHEN p.status = 5 THEN 'In Process'
        WHEN p.status = 6 THEN 'Cancel'
        WHEN p.status = 7 THEN 'Complete'
        ELSE 'Unknown'
    END AS purchase_status,
    FORMAT(p.grand_total, 3) AS grand_total,
    FORMAT(COALESCE(SUM(rp.grand_total), 0), 3) AS returned_amount,
    FORMAT(p.paid_amount, 3) AS paid_amount,
    FORMAT((p.grand_total - COALESCE(SUM(rp.grand_total), 0) - p.paid_amount), 3) AS due_amount,
    CASE 
        WHEN p.payment_status = 1 THEN 'Due'
        ELSE 'Paid'
    END AS payment_status
FROM purchases p
INNER JOIN customers c ON p.customer_id = c.id
LEFT JOIN return_purchases rp ON p.id = rp.purchase_id
WHERE p.customer_id = 1  -- Replace with your customer_id
GROUP BY p.id, p.created_at, p.po_no, p.reference_no, p.status, p.grand_total, 
         p.paid_amount, p.payment_status, c.name
ORDER BY p.created_at DESC;
```

---

## 10. Purchase List with Product Details

```sql
SELECT 
    p.id AS purchase_id,
    DATE_FORMAT(p.created_at, '%m-%d-%Y') AS date,
    p.po_no AS po_number,
    p.reference_no,
    COALESCE(c.name, s.name, u.name) AS customer_name,
    CASE 
        WHEN p.status = 1 THEN 'Received'
        WHEN p.status = 2 THEN 'Partial'
        WHEN p.status = 3 THEN 'Pending'
        WHEN p.status = 4 THEN 'Ordered'
        WHEN p.status = 5 THEN 'In Process'
        WHEN p.status = 6 THEN 'Cancel'
        WHEN p.status = 7 THEN 'Complete'
        ELSE 'Unknown'
    END AS purchase_status,
    FORMAT(p.grand_total, 3) AS grand_total,
    FORMAT(COALESCE(SUM(rp.grand_total), 0), 3) AS returned_amount,
    FORMAT(p.paid_amount, 3) AS paid_amount,
    FORMAT((p.grand_total - COALESCE(SUM(rp.grand_total), 0) - p.paid_amount), 3) AS due_amount,
    pr.name AS product_name,
    pp.qty AS product_quantity,
    pp.net_unit_cost AS product_unit_cost,
    pp.total AS product_total,
    pp.imei_number AS product_imei
FROM purchases p
LEFT JOIN customers c ON p.customer_id = c.id
LEFT JOIN suppliers s ON p.supplier_id = s.id
LEFT JOIN users u ON p.user_id = u.id
LEFT JOIN return_purchases rp ON p.id = rp.purchase_id
LEFT JOIN product_purchases pp ON p.id = pp.purchase_id
LEFT JOIN products pr ON pp.product_id = pr.id
WHERE DATE(p.created_at) >= '2025-01-01'
  AND DATE(p.created_at) <= '2025-12-31'
GROUP BY p.id, p.created_at, p.po_no, p.reference_no, p.status, p.grand_total, 
         p.paid_amount, p.payment_status, c.name, s.name, u.name, 
         pr.name, pp.qty, pp.net_unit_cost, pp.total, pp.imei_number
ORDER BY p.created_at DESC, pr.name;
```

---

## Status Codes Reference

### Purchase Status Codes:
- `1` = Received
- `2` = Partial
- `3` = Pending
- `4` = Ordered
- `5` = In Process
- `6` = Cancel
- `7` = Complete

### Payment Status Codes:
- `1` = Due
- `2` = Paid

---

## Notes

1. **Date Format**: Queries me date format `%m-%d-%Y` use kiya gaya hai (MM-DD-YYYY). Apne requirement ke hisab se change kar sakte hain.

2. **Number Formatting**: `FORMAT()` function use kiya gaya hai for decimal places. Default 3 decimal places hain. `config('decimal')` se match karne ke liye adjust kar sakte hain.

3. **Returned Amount**: `return_purchases` table se sum calculate hota hai for each purchase.

4. **Due Amount Calculation**: `grand_total - returned_amount - paid_amount`

5. **Customer/Supplier Logic**: Agar `customer_id` hai to customer name, warna supplier name, warna user name use hota hai.

6. **Pagination**: Query #6 me LIMIT aur OFFSET add kiya gaya hai. DataTables ke liye use karein.

7. **Search**: Query #7 me multiple fields me search functionality hai (reference_no, po_no, customer name, supplier name, IMEI number).

---

## Usage Examples

### Example 1: Get all pending purchases for last month
```sql
-- Use Query #4 and modify WHERE clause:
WHERE p.status = 3  -- Pending
  AND DATE(p.created_at) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
  AND DATE(p.created_at) <= CURDATE()
```

### Example 2: Get all due payments
```sql
-- Use Query #5:
WHERE p.payment_status = 1  -- Due
```

### Example 3: Get purchases for specific date range
```sql
-- Use Query #2:
WHERE DATE(p.created_at) >= '2025-01-01'
  AND DATE(p.created_at) <= '2025-01-31'
```

---

**Last Updated**: January 2026
**Author**: EZ-SOLUTIONS Development Team

