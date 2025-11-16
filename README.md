# Z Inventory - WordPress Inventory Management System

A comprehensive inventory management plugin for WordPress with POS (Point of Sale) functionality, designed for small to medium businesses.

## Features

### 🔐 Authentication
- Custom login page using WordPress authentication
- Two user roles: Owner and Cashier
- Secure role-based access control
- Active/Inactive status for cashiers

### 👨‍💼 Owner Features
- **Product Management**
  - Add, edit, and delete products
  - Set SKU, price, and stock quantities
  - Soft delete for product history

- **Cashier Management**
  - Create cashier accounts
  - Edit cashier details
  - Activate/Deactivate cashiers
  - Password management

- **Sales Reporting**
  - View all sales transactions
  - Filter sales by cashier
  - View detailed sale information
  - Sales statistics (total sales, revenue, average sale)

- **Inventory Management**
  - Update stock quantities
  - Track inventory changes
  - View inventory logs with reasons
  - Low stock alerts

- **Dashboard**
  - Overview of products, cashiers, and sales
  - Total revenue display
  - Low stock warnings
  - Recent sales history

### 💰 Cashier Features
- **Point of Sale (POS)**
  - Product search functionality
  - Shopping cart interface
  - Automatic change calculator
  - Quick cash buttons ($5, $10, $20, $50, $100, Exact)
  - Real-time stock validation
  - Receipt generation
  - Print receipt functionality

- **Dashboard**
  - Personal sales statistics
  - Total revenue
  - Quick access to POS

### 🎨 Design
- Bootstrap 5 styling (loaded only on plugin pages)
- Responsive design for all devices
- Clean and modern interface
- Bootstrap Icons integration

## Installation

### Step 1: Upload and Activate

1. Upload the `zc-inventory` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically:
   - Create necessary database tables
   - Create custom user roles (Owner and Cashier)
   - Set up rewrite rules

### Step 2: Flush Permalinks (Important!)

After activation, you must flush permalinks to make the custom URLs work:

1. Go to **WordPress Admin > Settings > Permalinks**
2. Click **"Save Changes"** button (you don't need to change anything)
3. This ensures all plugin routes are registered correctly

## Getting Started

### Step 1: Creating an Owner Account

After activation, you'll need to create an owner account:

1. Go to **WordPress Admin > Users > Add New**
2. Fill in the user details:
   - Username (e.g., `owner`)
   - Email address
   - Password
3. In the **"Role"** dropdown, select **"Inventory Owner"**
4. Click **"Add New User"**

### Step 2: First Login

1. Navigate to the login page: `http://yoursite.com/zc-inventory/login`
   - Replace `yoursite.com` with your actual domain
   - Example: `http://localhost/mysite/zc-inventory/login`
2. Enter your owner username and password
3. Click **"Login"**
4. You'll be redirected to the Owner Dashboard

### Step 3: Setting Up Your System

Once logged in as Owner, follow these steps:

#### A. Add Products
1. Click **"Products"** in the navigation menu
   - URL: `/zc-inventory/products`
2. Click **"Add Product"** button
3. Fill in product details:
   - Product Name (required)
   - SKU (optional)
   - Description (optional)
   - Price (required)
   - Stock Quantity (required)
4. Click **"Add Product"**

#### B. Create Cashier Accounts
1. Click **"Cashiers"** in the navigation menu
   - URL: `/zc-inventory/cashiers`
2. Click **"Add Cashier"** button
3. Fill in cashier details:
   - Username (required) - used for login
   - Display Name (optional) - name shown in the system
   - Email (required)
   - Password (required) - minimum 8 characters
4. Click **"Add Cashier"**
5. The cashier can now log in at `/zc-inventory/login`

## Complete URL Routes Guide

### Public Routes (No Authentication Required)

| Route | URL | Description |
|-------|-----|-------------|
| **Login Page** | `/zc-inventory/login` | Main login page for both Owner and Cashier |
| **Logout** | `/zc-inventory/logout` | Logout and redirect to login page |

**Full URL Examples:**
- `http://yoursite.com/zc-inventory/login`
- `http://localhost/mysite/zc-inventory/login`
- `https://yourdomain.com/zc-inventory/login`

### Protected Routes (Authentication Required)

#### Routes for BOTH Owner and Cashier

| Route | URL | Access | Description |
|-------|-----|--------|-------------|
| **Dashboard** | `/zc-inventory/dashboard` | Owner & Cashier | Main dashboard (different for each role) |

#### Owner-Only Routes

| Route | URL | Access | Description |
|-------|-----|--------|-------------|
| **Products** | `/zc-inventory/products` | Owner Only | Add, edit, delete products |
| **Cashiers** | `/zc-inventory/cashiers` | Owner Only | Manage cashier accounts |
| **Sales Report** | `/zc-inventory/sales-report` | Owner Only | View all sales and statistics |
| **Inventory** | `/zc-inventory/inventory` | Owner Only | Update stock and view logs |

#### Cashier Routes

| Route | URL | Access | Description |
|-------|-----|--------|-------------|
| **Point of Sale** | `/zc-inventory/pos` | Cashier & Owner | Process sales transactions |

## User Workflows

### 👨‍💼 Owner Workflow

1. **Login** → `http://yoursite.com/zc-inventory/login`
2. **View Dashboard** → `/zc-inventory/dashboard`
   - See total products, active cashiers, total sales, revenue
   - View low stock alerts
   - See recent sales
3. **Manage Products** → `/zc-inventory/products`
   - Add new products
   - Edit existing products
   - Delete products
4. **Manage Cashiers** → `/zc-inventory/cashiers`
   - Add new cashiers
   - Edit cashier details
   - Activate/Deactivate cashiers
5. **View Sales** → `/zc-inventory/sales-report`
   - See all sales transactions
   - Filter by cashier
   - View sale details
6. **Manage Inventory** → `/zc-inventory/inventory`
   - Update stock quantities
   - Add reasons for changes
   - View inventory logs
7. **Process Sales** (Optional) → `/zc-inventory/pos`
   - Owners can also use POS
8. **Logout** → `/zc-inventory/logout`

### 💰 Cashier Workflow

1. **Login** → `http://yoursite.com/zc-inventory/login`
2. **View Dashboard** → `/zc-inventory/dashboard`
   - See personal sales statistics
   - View total revenue
3. **Process Sales** → `/zc-inventory/pos`
   - Search for products
   - Add items to cart
   - Enter cash received
   - Calculate change
   - Complete sale
   - Print receipt
4. **Logout** → `/zc-inventory/logout`

## How to Use the Point of Sale (POS)

### Access POS
- URL: `/zc-inventory/pos`
- Available for: Cashiers and Owners

### Step-by-Step Sale Process

1. **Select Products**
   - Click on any product card to add it to the cart
   - Use the search box to find products quickly
   - Only products with stock > 0 are shown

2. **Manage Cart**
   - Click **"+"** to increase quantity
   - Click **"-"** to decrease quantity
   - Click **trash icon** to remove item
   - Click **"Clear Cart"** to remove all items

3. **Process Payment**
   - View the **"Amount to Pay"** (auto-calculated)
   - Enter **"Cash Received"** amount
   - OR use Quick Cash buttons ($5, $10, $20, $50, $100)
   - OR click **"Exact"** for exact amount
   - View the **"Change"** amount (auto-calculated)

4. **Complete Sale**
   - Click **"Process Sale"** button
   - System validates:
     - Cart is not empty
     - Sufficient cash received
     - Stock availability
   - Receipt is displayed
   - Stock is automatically reduced

5. **Print Receipt** (Optional)
   - Click **"Print Receipt"** button in the receipt modal

6. **Start New Sale**
   - Click **"New Sale"** button

## Important Notes

### Default Credentials
There are NO default credentials. You must create an owner account manually through WordPress Admin after plugin activation.

### Access Control
- **Owner** can access: Dashboard, Products, Cashiers, Sales Report, Inventory, POS
- **Cashier** can access: Dashboard, POS
- Attempting to access restricted pages will show "Permission denied"

### Cashier Activation/Deactivation
- Owners can deactivate cashiers from the Cashiers page
- Deactivated cashiers cannot log in
- When a cashier tries to login while deactivated, they'll see: "Your account has been deactivated. Please contact the owner."

### Stock Management
- Stock is automatically reduced when a sale is processed
- Owners can manually update stock from the Inventory page
- Low stock alerts appear when stock ≤ 10 items
- System prevents selling more than available stock

### URL Examples

**Local Development:**
- `http://localhost/yoursite/zc-inventory/login`
- `http://localhost:8080/zc-inventory/dashboard`

**Production:**
- `https://yourdomain.com/zc-inventory/login`
- `https://www.yourstore.com/zc-inventory/pos`

**Subdomain:**
- `https://inventory.yourdomain.com/zc-inventory/login`

## Database Tables

The plugin creates the following tables:

- `wp_zc_products` - Product information
- `wp_zc_sales` - Sales transactions
- `wp_zc_sale_items` - Individual items in each sale
- `wp_zc_inventory_logs` - Inventory change history
- `wp_zc_cashier_settings` - Cashier active/inactive status

## Technical Details

### Requirements
- WordPress 5.0 or higher
- PHP 7.0 or higher
- MySQL 5.6 or higher

### Dependencies
- Bootstrap 5.3.0 (CDN)
- Bootstrap Icons 1.10.0 (CDN)
- jQuery (WordPress default)

### File Structure
```
zc-inventory/
├── z-inventory.php          # Main plugin file
├── includes/                # PHP classes
│   ├── class-zc-database.php
│   ├── class-zc-roles.php
│   ├── class-zc-auth.php
│   ├── class-zc-products.php
│   ├── class-zc-sales.php
│   ├── class-zc-cashiers.php
│   └── class-zc-inventory.php
├── templates/              # Page templates
│   ├── header.php
│   ├── footer.php
│   ├── login.php
│   ├── dashboard.php
│   ├── products.php
│   ├── cashiers.php
│   ├── sales-report.php
│   ├── inventory.php
│   └── pos.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
└── README.md
```

## Security Features

- Nonce verification for all AJAX requests
- SQL injection prevention using prepared statements
- XSS protection with proper escaping
- Role-based access control
- Secure password handling
- Session management

## Customization

### Changing Low Stock Threshold

Edit the threshold in the respective template files (default is 10):

```php
if ($product->stock <= 10) {
    // Low stock warning
}
```

### Styling

Custom styles can be added to `assets/css/style.css`

### Adding Custom Fields

Modify the respective class files in the `includes/` directory

## Quick Reference: All Routes

### Complete Route List

```
PUBLIC ROUTES (No Login Required)
├── /zc-inventory/login          → Login page (Owner & Cashier)
└── /zc-inventory/logout         → Logout and redirect to login

PROTECTED ROUTES (Login Required)
├── /zc-inventory/dashboard      → Dashboard (Owner & Cashier - different views)
│
├── OWNER-ONLY ROUTES
│   ├── /zc-inventory/products      → Product management
│   ├── /zc-inventory/cashiers      → Cashier management
│   ├── /zc-inventory/sales-report  → Sales reporting
│   └── /zc-inventory/inventory     → Inventory management
│
└── CASHIER & OWNER ROUTES
    └── /zc-inventory/pos           → Point of Sale (POS)
```

### Quick Start Checklist

- [ ] Activate the plugin
- [ ] Go to Settings > Permalinks and click "Save Changes"
- [ ] Create an Owner account via WordPress Admin > Users > Add New
- [ ] Login at `/zc-inventory/login`
- [ ] Add products at `/zc-inventory/products`
- [ ] Create cashier accounts at `/zc-inventory/cashiers`
- [ ] Start selling at `/zc-inventory/pos`

## Troubleshooting

### Login Issues
- Clear browser cache
- Check if user has correct role assigned
- Verify user account is active (for cashiers)
- Make sure you're using the correct login URL: `/zc-inventory/login` (NOT the WordPress admin login)

### 404 Errors
- **This is the most common issue!**
- Go to **Settings > Permalinks** and click **"Save Changes"** to flush rewrite rules
- Make sure permalinks are NOT set to "Plain"
- Recommended permalink structure: "Post name" or "Custom Structure"

### Can't Access Routes
1. Verify permalinks are flushed (Settings > Permalinks > Save Changes)
2. Check if .htaccess file is writable (for Apache servers)
3. Make sure you're logged in with the correct role
4. Check if the URL is correct (e.g., `/zc-inventory/products` not `/zc-inventory/product`)

### Database Errors
- Deactivate and reactivate the plugin to recreate tables
- Check database permissions
- Verify WordPress database prefix matches (default is `wp_`)

### Permission Denied Errors
- **Owner** trying to access routes: Make sure the user role is "Inventory Owner" or "Administrator"
- **Cashier** trying to access owner routes: Cashiers can only access Dashboard and POS
- Check if cashier account is active (not deactivated by owner)

## Changelog

### Version 1.0.0
- Initial release
- Product management
- Cashier management
- Sales tracking
- Inventory management
- POS system
- User authentication
- Role-based access

## Support

For support, please create an issue in the repository or contact the developer.

## License

GPL v2 or later

## Credits

Developed with WordPress best practices and modern web technologies.
