Drugstore POS System — Full Feature Breakdown

--------------------------------------------------------------------------------

Ground Rules

You will NOT edit any code until I approve the implementation plan for each task.
Present the implementation plan and workflow for each task first, then wait for my approval before writing a single line of code.
Address each task one at a time in the order I approve them.

--------------------------------------------------------------------------------

Task 1 — Dashboard

Goal
Build a functional dashboard that gives a real-time overview of the business at a glance.

Implementation Plan
- [ ] Total Sales Today — total revenue generated for the current day
- [ ] Total Sales This Month — monthly revenue summary
- [ ] Total Transactions Today — number of completed transactions
- [ ] Low Stock Alerts — products that are running low and need restocking
- [ ] Top Selling Products — most sold items within a selected period
- [ ] Recent Transactions — a short list of the latest sales
- [ ] Total Products — how many products are currently in the system
- [ ] Expiring Products — items nearing their expiry date

Workflow
1. On load, the dashboard fetches data from the database
2. Each metric is displayed in its own card or widget
3. Low stock and expiring product alerts are highlighted visually
4. Data updates in real time or on page refresh

Awaiting your approval before touching any code.

--------------------------------------------------------------------------------

Task 2 — Product Management

Goal
Allow admins to add, edit, and manage products in the drugstore inventory, including category-based senior discount settings.

Implementation Plan
- [ ] Product Name
- [ ] Barcode
- [ ] Category (e.g., Medicines, Vitamins, Personal Care, etc.)
- [ ] Unit of Measurement (e.g., piece, box, bottle)
- [ ] Price
- [ ] Stock Quantity
- [ ] Reorder Level
- [ ] Expiry Date
- [ ] Supplier
- [ ] Discount Eligibility — toggle per category whether the senior citizen 20% discount applies
- [ ] Discount Rate — adjustable percentage (default 20%, but can be changed per category)

Workflow
1. Admin opens Product Management
2. Admin can Add, Edit, or Archive a product
3. Admin can manage Categories and set whether a category is eligible for senior discount and at what rate
4. Changes are saved to the database immediately
5. Product list is searchable and filterable by category, stock level, and expiry

Awaiting your approval before touching any code.

--------------------------------------------------------------------------------

Task 3 — Inventory Management

Goal
Give admins a clear view of current stock levels and manage inventory efficiently.

Implementation Plan
- [ ] Full Product List with current stock levels
- [ ] Low Stock Indicator — visually flags products below their reorder level
- [ ] Expiry Date Tracker — highlights products expiring soon (e.g., within 30 days)
- [ ] Stock Adjustment — manually increase or decrease stock with a reason (e.g., damaged, expired, returned)
- [ ] Audit Log — record of all stock changes with timestamp and who made the change
- [ ] Filter and Search — by category, stock status, or expiry status

Workflow
1. Admin opens Inventory Management
2. They see all products with real-time stock levels
3. Low stock and expiring products are highlighted at the top or flagged with a color indicator
4. Admin can manually adjust stock and the change is logged automatically

Awaiting your approval before touching any code.

--------------------------------------------------------------------------------

Task 4 — Sales (POS)

Goal
Build the core Point of Sale interface where staff processes transactions, applies discounts, and completes sales.

Implementation Plan
- [ ] Product Search — search by name or barcode
- [ ] Cart — add products, adjust quantity, remove items
- [ ] Senior Citizen Discount — toggle per transaction, automatically applies the discount rate set per category
- [ ] Subtotal, Discount, and Total Display — updates in real time as items are added
- [ ] Payment Methods — Cash (with change computation), and optionally others later
- [ ] Receipt Generation — printable or on-screen receipt after transaction
- [ ] Barcode Scanner Ready — input field accepts barcode scanner input (scanner integration to be added later)

Workflow
1. Staff opens the Sales/POS screen
2. Staff searches for a product or scans a barcode
3. Product is added to the cart
4. If the customer is a senior citizen, staff toggles the senior discount
5. Discount applies only to eligible categories automatically
6. Staff processes payment, inputs amount tendered, system computes change
7. Transaction is saved and receipt is generated

Awaiting your approval before touching any code.

--------------------------------------------------------------------------------

Task 5 — Purchase and Restock

Goal
Track incoming stock from suppliers and manage purchase orders.

Implementation Plan
- [ ] Purchase Order Creation — create a new order with supplier name, products, quantities, and expected delivery date
- [ ] Receive Stock — mark a purchase order as received, automatically updating inventory levels
- [ ] Purchase History — list of all past purchase orders with status (Pending, Received, Cancelled)
- [ ] Supplier Management — add and manage supplier information

Workflow
1. Admin creates a purchase order for a supplier
2. Order is saved with status Pending
3. When stock arrives, admin marks it as Received
4. Inventory levels are automatically updated
5. Full purchase history is viewable and searchable

Awaiting your approval before touching any code.

--------------------------------------------------------------------------------

Task 6 — Reports

Goal
Generate useful reports to help the business make informed decisions.

Implementation Plan
- [ ] Daily/Weekly/Monthly Sales Report
- [ ] Top Selling Products Report
- [ ] Inventory Report
- [ ] Purchase/Restock Report
- [ ] Senior Discount Report
- [ ] Exportable — reports can be exported as PDF or CSV

Workflow
1. Admin selects the report type and date range
2. System fetches and displays the relevant data
3. Admin can export the report if needed

Awaiting your approval before touching any code.

--------------------------------------------------------------------------------

Task 7 — User Authentication & Role Management

Goal
Implement a 3-level role system (Staff, Admin, Superadmin) with proper access control and approval workflow for user management.

Role Permissions

Feature | Staff | Admin | Superadmin
-------------------------------------------
POS / Sales | Yes | Yes | Yes
Dashboard | Yes | Yes | Yes
Product Management | Yes | Yes | Yes
Inventory Management | Yes | Yes | Yes
Reports | Yes | Yes | Yes
Purchase & Restock | No | Yes | Yes
User Management | No | Yes (with approval) | Yes
System Settings | No | Yes | Yes
Security Settings | No | No | Yes

Workflow
1. Superadmin creates the initial accounts
2. Admin can add or remove staff accounts but the action is sent to Superadmin as a pending request
3. Superadmin receives a notification and either approves or rejects the request
4. If approved, the account is created or removed
5. Staff can only access Sales, Dashboard, Products, and Inventory
6. Unauthorized routes redirect to an Access Denied page

Awaiting your approval before touching any code.
