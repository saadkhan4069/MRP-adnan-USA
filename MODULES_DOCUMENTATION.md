# MRP System - Complete Modules Documentation
# MRP System - Modules Ki Complete Documentation

---

## Table of Contents / Contents Ki List
1. [WooCommerce Orders Module](#woocommerce-orders-module)
2. [Shipment/Logistics Module](#shipmentlogistics-module)
3. [Purchase Module](#purchase-module)
4. [Permissions & Role Management](#permissions--role-management)

---

## WooCommerce Orders Module
## WooCommerce Orders Module

### English Documentation

#### Overview
The WooCommerce Orders module allows you to integrate with WooCommerce stores, fetch orders automatically, and manage them in your portal. This module is separate from the CPG (Customer) module and handles external platform orders.

#### Features

1. **API Integration**
   - Connect multiple WooCommerce stores
   - Store API credentials (Consumer Key & Secret)
   - Test connection before syncing
   - Support for multiple platforms

2. **Order Synchronization**
   - Automatic order fetching from WooCommerce API
   - Date-based filtering (fetch orders from specific dates)
   - Duplicate prevention (only new orders are synced)
   - Complete order details including:
     - Buyer information (name, email, phone)
     - Billing address (complete address details)
     - Shipping address (complete address details)
     - Payment information
     - Order items with product images
     - Order dates (created, modified, completed, paid)
     - Order notes and metadata

3. **Order Management**
   - View all orders in a data table
   - Add new orders manually
   - Edit existing orders
   - Delete orders
   - View order details
   - Product images preview

4. **Permissions**
   - `woocommerce-index` - View orders list
   - `woocommerce-add` - Add new orders
   - `woocommerce-edit` - Edit orders
   - `woocommerce-delete` - Delete orders
   - `woocommerce-api-settings` - Manage API settings

#### How to Use

**Setting Up API Connection:**
1. Go to WooCommerce Orders → API Settings
2. Click "Add API Connection"
3. Enter:
   - Platform Name (e.g., "WooCommerce")
   - Website URL (your WooCommerce store URL)
   - Consumer Key
   - Consumer Secret
   - Sync Interval (optional)
4. Click "Save"
5. Test connection using "Test Connection" button

**Syncing Orders:**
1. Go to API Settings
2. Click "Sync Orders" button
3. Select date range (optional):
   - From Date: Fetch orders from this date
   - To Date: Fetch orders up to this date
   - Leave empty: Fetch all new orders
4. Click "Sync Orders"
5. System will fetch only new orders (duplicates are skipped)

**Viewing Orders:**
1. Go to WooCommerce Orders → Orders List
2. View all orders in table format
3. See product images (click to preview)
4. Filter and search orders
5. Edit or delete orders as needed

**Adding Manual Orders:**
1. Go to WooCommerce Orders → New Order
2. Fill in order details:
   - Order Number
   - Status
   - Currency
   - Total Amount
   - Buyer Information (First Name, Last Name, Email, Phone)
   - Billing Address
   - Shipping Address
   - Payment Information
3. Click "Save Order"

#### Technical Details

**Database Tables:**
- `woocommerce_api_settings` - Stores API credentials
- `woocommerce_orders` - Stores order data

**Key Fields:**
- `platform_order_id` - Original order ID from WooCommerce
- `order_number` - Order number
- `product_images` - JSON array of product images
- `line_items` - JSON array of order items
- `raw_data` - Complete API response

**Routes:**
- `/woocommerce/orders` - Orders list
- `/woocommerce/orders/create` - Create order
- `/woocommerce/orders/{id}/edit` - Edit order
- `/woocommerce/api-settings` - API settings

---

### Roman Urdu Documentation

#### Overview
WooCommerce Orders module aapko WooCommerce stores ke saath connect karne, orders automatically fetch karne, aur unhein portal mein manage karne ki facility deta hai. Yeh module CPG (Customer) module se alag hai aur external platform orders ko handle karta hai.

#### Features

1. **API Integration**
   - Multiple WooCommerce stores se connect karein
   - API credentials store karein (Consumer Key & Secret)
   - Sync se pehle connection test karein
   - Multiple platforms ka support

2. **Order Synchronization**
   - WooCommerce API se automatic order fetching
   - Date-based filtering (specific dates se orders fetch karein)
   - Duplicate prevention (sirf naye orders sync honge)
   - Complete order details including:
     - Buyer information (name, email, phone)
     - Billing address (complete address details)
     - Shipping address (complete address details)
     - Payment information
     - Order items with product images
     - Order dates (created, modified, completed, paid)
     - Order notes aur metadata

3. **Order Management**
   - Data table mein sabhi orders dekhein
   - Manually naye orders add karein
   - Existing orders edit karein
   - Orders delete karein
   - Order details dekhein
   - Product images ka preview

4. **Permissions**
   - `woocommerce-index` - Orders list dekhein
   - `woocommerce-add` - Naye orders add karein
   - `woocommerce-edit` - Orders edit karein
   - `woocommerce-delete` - Orders delete karein
   - `woocommerce-api-settings` - API settings manage karein

#### Kaise Use Karein

**API Connection Setup:**
1. WooCommerce Orders → API Settings par jayein
2. "Add API Connection" click karein
3. Enter karein:
   - Platform Name (e.g., "WooCommerce")
   - Website URL (apka WooCommerce store URL)
   - Consumer Key
   - Consumer Secret
   - Sync Interval (optional)
4. "Save" click karein
5. "Test Connection" button se connection test karein

**Orders Sync Karna:**
1. API Settings par jayein
2. "Sync Orders" button click karein
3. Date range select karein (optional):
   - From Date: Is date se orders fetch karein
   - To Date: Is date tak orders fetch karein
   - Empty chhor dein: Sab naye orders fetch honge
4. "Sync Orders" click karein
5. System sirf naye orders fetch karega (duplicates skip honge)

**Orders Dekhna:**
1. WooCommerce Orders → Orders List par jayein
2. Table format mein sabhi orders dekhein
3. Product images dekhein (preview ke liye click karein)
4. Orders filter aur search karein
5. Zarurat ke mutabiq orders edit ya delete karein

**Manual Orders Add Karna:**
1. WooCommerce Orders → New Order par jayein
2. Order details fill karein:
   - Order Number
   - Status
   - Currency
   - Total Amount
   - Buyer Information (First Name, Last Name, Email, Phone)
   - Billing Address
   - Shipping Address
   - Payment Information
3. "Save Order" click karein

---

## Shipment/Logistics Module
## Shipment/Logistics Module

### English Documentation

#### Overview
The Shipment/Logistics module allows you to manage shipping and logistics operations, create shipments, track packages, and generate shipping labels and invoices.

#### Features

1. **Shipment Management**
   - Create new shipments
   - Edit existing shipments
   - View shipment details
   - Delete shipments
   - Track shipments

2. **Package Management**
   - Add multiple packages per shipment
   - Package details:
     - Packaging type
     - Quantity (Qty)
     - Package Class
     - Package NMFC
     - Commodity Name
     - Weight and weight unit
     - Dimensions (Length, Width, Height)
     - Declared Value
     - Dimensions Note

3. **Address Management**
   - Shipper (From) Address:
     - Company, Name, Address
     - City, State, Zipcode, Country
     - Contact, Email
     - Dock Hours
   - Recipient (To) Address:
     - Company, Name, Address
     - City, State, Zipcode, Country
     - Contact, Email
     - Dock Hours

4. **Shipping Label**
   - Create portal shipping label
   - Update label
   - View label in PDF
   - Label includes:
     - Tracking number
     - Pickup Date & Time
     - Drop off Date & Time
     - Shipper and Recipient details
     - Package details
     - EZ-Solutions branding

5. **Proforma Invoice**
   - Generate proforma invoice PDF
   - Complete order details
   - Package totals (quantity and weight)
   - EZ-Solutions branding
   - Marked as "PORTAL GENERATED"

6. **Attachments**
   - Upload multiple files (PDF, Images, Excel, Word)
   - View attachments
   - Download attachments
   - Files stored in `public/shipment/`

7. **Date Management**
   - Created date can be edited inline
   - Pickup Date & Time
   - Drop off Date & Time

#### Permissions
- `shipments-index` - View shipments list
- `shipments-add` - Create shipments
- `shipments-edit` - Edit shipments
- `shipments-delete` - Delete shipments
- `shipments-view` - View shipment details

#### How to Use

**Creating a Shipment:**
1. Go to Shipping / Logistic → Add Shipment
2. Step 1: Enter Shipper (From) details including Dock Hours
3. Step 2: Enter Recipient (To) details including Dock Hours
4. Step 3: Enter shipment details (currency, service, etc.)
5. Step 4: Add products/items
6. Step 5: Add packages with complete details
7. Step 6: Review and submit

**Viewing Shipments:**
1. Go to Shipping / Logistic → Shipment List
2. View all shipments in table
3. Click on row to expand and see details
4. Dock Hours shown in expanded view

**Creating Shipping Label:**
1. Open shipment details
2. Click "Create Portal Shipping Label"
3. Fill in label details:
   - Pickup Date & Time
   - Drop off Date & Time
   - Other required fields
4. Click "Save Portal Label"
5. Label is saved via AJAX (no page reload)
6. View label PDF using "Portal Label PDF" button

**Generating Proforma Invoice:**
1. Open shipment details
2. Click "Proforma Invoice" button
3. PDF opens in browser
4. Invoice is marked as "PORTAL GENERATED"

**Editing Created Date:**
1. Go to Shipment List
2. Click on edit icon next to Created date
3. Select new date/time
4. Click save
5. Date updates without page reload

---

### Roman Urdu Documentation

#### Overview
Shipment/Logistics module aapko shipping aur logistics operations manage karne, shipments create karne, packages track karne, aur shipping labels aur invoices generate karne ki facility deta hai.

#### Features

1. **Shipment Management**
   - Naye shipments create karein
   - Existing shipments edit karein
   - Shipment details dekhein
   - Shipments delete karein
   - Shipments track karein

2. **Package Management**
   - Har shipment ke liye multiple packages add karein
   - Package details:
     - Packaging type
     - Quantity (Qty)
     - Package Class
     - Package NMFC
     - Commodity Name
     - Weight aur weight unit
     - Dimensions (Length, Width, Height)
     - Declared Value
     - Dimensions Note

3. **Address Management**
   - Shipper (From) Address:
     - Company, Name, Address
     - City, State, Zipcode, Country
     - Contact, Email
     - Dock Hours
   - Recipient (To) Address:
     - Company, Name, Address
     - City, State, Zipcode, Country
     - Contact, Email
     - Dock Hours

4. **Shipping Label**
   - Portal shipping label create karein
   - Label update karein
   - PDF mein label dekhein
   - Label includes:
     - Tracking number
     - Pickup Date & Time
     - Drop off Date & Time
     - Shipper aur Recipient details
     - Package details
     - EZ-Solutions branding

5. **Proforma Invoice**
   - Proforma invoice PDF generate karein
   - Complete order details
     - Package totals (quantity aur weight)
   - EZ-Solutions branding
   - "PORTAL GENERATED" ke taur par marked

6. **Attachments**
   - Multiple files upload karein (PDF, Images, Excel, Word)
   - Attachments dekhein
   - Attachments download karein
   - Files `public/shipment/` mein store hote hain

7. **Date Management**
   - Created date inline edit ki ja sakti hai
   - Pickup Date & Time
   - Drop off Date & Time

#### Permissions
- `shipments-index` - Shipments list dekhein
- `shipments-add` - Shipments create karein
- `shipments-edit` - Shipments edit karein
- `shipments-delete` - Shipments delete karein
- `shipments-view` - Shipment details dekhein

#### Kaise Use Karein

**Shipment Create Karna:**
1. Shipping / Logistic → Add Shipment par jayein
2. Step 1: Shipper (From) details enter karein including Dock Hours
3. Step 2: Recipient (To) details enter karein including Dock Hours
4. Step 3: Shipment details enter karein (currency, service, etc.)
5. Step 4: Products/items add karein
6. Step 5: Complete details ke saath packages add karein
7. Step 6: Review karke submit karein

**Shipments Dekhna:**
1. Shipping / Logistic → Shipment List par jayein
2. Table mein sabhi shipments dekhein
3. Details ke liye row par click karein
4. Expanded view mein Dock Hours dikhaye jate hain

**Shipping Label Create Karna:**
1. Shipment details open karein
2. "Create Portal Shipping Label" click karein
3. Label details fill karein:
   - Pickup Date & Time
   - Drop off Date & Time
   - Other required fields
4. "Save Portal Label" click karein
5. Label AJAX se save hota hai (page reload nahi hota)
6. "Portal Label PDF" button se label PDF dekhein

**Proforma Invoice Generate Karna:**
1. Shipment details open karein
2. "Proforma Invoice" button click karein
3. PDF browser mein khulta hai
4. Invoice "PORTAL GENERATED" ke taur par marked hota hai

**Created Date Edit Karna:**
1. Shipment List par jayein
2. Created date ke saath edit icon par click karein
3. Naya date/time select karein
4. Save click karein
5. Date bina page reload ke update hoti hai

---

## Purchase Module
## Purchase Module

### English Documentation

#### Overview
The Purchase module manages purchase orders, suppliers, and purchase-related operations. It includes features for creating purchases, managing shipped items, and viewing purchase history.

#### Features

1. **Purchase Management**
   - Create purchase orders
   - Edit purchases
   - View purchase list
   - Delete purchases
   - Import purchases via CSV

2. **Shipped Items**
   - View shipped purchase items
   - Track shipments
   - View supplier and customer company names
   - Courier information
   - Tracking numbers

3. **Calculations**
   - Automatic total calculations
   - Tax calculations
   - Discount calculations
   - Shipping cost calculations
   - Grand total calculations

#### Permissions
- `purchases-index` - View purchase list
- `purchases-add` - Create purchases
- `purchases-edit` - Edit purchases
- `purchases-delete` - Delete purchases
- `purchases-import` - Import purchases by CSV
- `purchase-shipment-list` - View shipment list

---

### Roman Urdu Documentation

#### Overview
Purchase module purchase orders, suppliers, aur purchase-related operations manage karta hai. Is mein purchases create karne, shipped items manage karne, aur purchase history dekhein ki features hain.

#### Features

1. **Purchase Management**
   - Purchase orders create karein
   - Purchases edit karein
   - Purchase list dekhein
   - Purchases delete karein
   - CSV se purchases import karein

2. **Shipped Items**
   - Shipped purchase items dekhein
   - Shipments track karein
   - Supplier aur customer company names dekhein
   - Courier information
   - Tracking numbers

3. **Calculations**
   - Automatic total calculations
   - Tax calculations
   - Discount calculations
   - Shipping cost calculations
   - Grand total calculations

#### Permissions
- `purchases-index` - Purchase list dekhein
- `purchases-add` - Purchases create karein
- `purchases-edit` - Purchases edit karein
- `purchases-delete` - Purchases delete karein
- `purchases-import` - CSV se purchases import karein
- `purchase-shipment-list` - Shipment list dekhein

---

## Permissions & Role Management
## Permissions & Role Management

### English Documentation

#### Overview
The system uses Spatie Laravel Permission package for role-based access control. Each module has specific permissions that can be assigned to different roles.

#### Roles
1. **Admin** (Role ID: 1) - Full access to all modules
2. **Owner** (Role ID: 2) - Full access to all modules
3. **Staff** (Role ID: 4) - Limited access based on permissions
4. **Customer** (Role ID: 5) - Limited access based on permissions

#### Permission Structure
Permissions follow the pattern: `{module}-{action}`
- `index` - View list
- `add` - Create new
- `edit` - Edit existing
- `delete` - Delete
- `view` - View details

#### WooCommerce Permissions
- `woocommerce-index` - View orders
- `woocommerce-add` - Add orders
- `woocommerce-edit` - Edit orders
- `woocommerce-delete` - Delete orders
- `woocommerce-api-settings` - Manage API settings

#### Shipment Permissions
- `shipments-index` - View shipments
- `shipments-add` - Create shipments
- `shipments-edit` - Edit shipments
- `shipments-delete` - Delete shipments
- `shipments-view` - View shipment details

#### Managing Permissions
1. Go to Role → Permission
2. Select role
3. Check/uncheck permissions
4. Click Submit
5. Permissions are saved and cache is cleared

#### Cache Management
- Role permissions are cached for performance
- Cache is automatically cleared when permissions are updated
- Use `php artisan cache:clear-role-permissions` to manually clear cache

---

### Roman Urdu Documentation

#### Overview
System Spatie Laravel Permission package use karta hai role-based access control ke liye. Har module ki specific permissions hain jo different roles ko assign ki ja sakti hain.

#### Roles
1. **Admin** (Role ID: 1) - Sabhi modules ki full access
2. **Owner** (Role ID: 2) - Sabhi modules ki full access
3. **Staff** (Role ID: 4) - Permissions ke basis par limited access
4. **Customer** (Role ID: 5) - Permissions ke basis par limited access

#### Permission Structure
Permissions pattern follow karte hain: `{module}-{action}`
- `index` - List dekhein
- `add` - Naya create karein
- `edit` - Existing edit karein
- `delete` - Delete karein
- `view` - Details dekhein

#### WooCommerce Permissions
- `woocommerce-index` - Orders dekhein
- `woocommerce-add` - Orders add karein
- `woocommerce-edit` - Orders edit karein
- `woocommerce-delete` - Orders delete karein
- `woocommerce-api-settings` - API settings manage karein

#### Shipment Permissions
- `shipments-index` - Shipments dekhein
- `shipments-add` - Shipments create karein
- `shipments-edit` - Shipments edit karein
- `shipments-delete` - Shipments delete karein
- `shipments-view` - Shipment details dekhein

#### Permissions Manage Karna
1. Role → Permission par jayein
2. Role select karein
3. Permissions check/uncheck karein
4. Submit click karein
5. Permissions save hote hain aur cache clear hota hai

#### Cache Management
- Performance ke liye role permissions cache kiye jate hain
- Permissions update hone par cache automatically clear hota hai
- Manually cache clear karne ke liye `php artisan cache:clear-role-permissions` use karein

---

## Technical Commands
## Technical Commands

### English

**Create WooCommerce Permissions:**
```bash
php artisan permissions:create-woocommerce
```

**Add API Credentials:**
```bash
php artisan woocommerce:add-api-credentials
```

**Clear Role Permissions Cache:**
```bash
php artisan cache:clear-role-permissions
```

**Clear Application Cache:**
```bash
php artisan cache:clear
```

---

### Roman Urdu

**WooCommerce Permissions Create Karna:**
```bash
php artisan permissions:create-woocommerce
```

**API Credentials Add Karna:**
```bash
php artisan woocommerce:add-api-credentials
```

**Role Permissions Cache Clear Karna:**
```bash
php artisan cache:clear-role-permissions
```

**Application Cache Clear Karna:**
```bash
php artisan cache:clear
```

---

## Database Tables
## Database Tables

### English

**WooCommerce:**
- `woocommerce_api_settings` - API credentials
- `woocommerce_orders` - Order data

**Shipments:**
- `shipments` - Shipment data
- `shipment_items` - Order items
- `shipment_packages` - Package details
- `shipment_attachments` - File attachments
- `shipment_labels` - Shipping labels

**Purchases:**
- `purchases` - Purchase orders
- `purchase_shippeds` - Shipped items

---

### Roman Urdu

**WooCommerce:**
- `woocommerce_api_settings` - API credentials
- `woocommerce_orders` - Order data

**Shipments:**
- `shipments` - Shipment data
- `shipment_items` - Order items
- `shipment_packages` - Package details
- `shipment_attachments` - File attachments
- `shipment_labels` - Shipping labels

**Purchases:**
- `purchases` - Purchase orders
- `purchase_shippeds` - Shipped items

---

## Support & Contact
## Support & Contact

For technical support or questions, please contact the development team.

Technical support ya sawal ke liye, development team se contact karein.

---

**Document Version:** 1.0  
**Last Updated:** January 2026  
**Document Version:** 1.0  
**Last Updated:** January 2026

