# ✅ ZCA Inventory License Integration Complete!

## Overview

The ZCA Inventory WordPress plugin is now integrated with your API Key Manager at `https://api.benjomabrasado.space/`. This provides:

- **7-Day Trial Period** per site activation
- **Multi-site license support**
- **Automatic license validation**
- **WordPress admin interface** for license management
- **Admin notices** for license status

## How It Works

### 1. License Activation Flow

```
User installs ZCA Inventory
  ↓
WordPress Admin → Settings → ZCA License
  ↓
Enter License Key (ZCA-XXXX-XXXX-XXXX-XXXX)
  ↓
Click "Activate This Site"
  ↓
API Call to: https://api.benjomabrasado.space/api/activate
  ↓
7-Day Trial Starts
  ↓
License Data Cached (24 hours)
  ↓
✅ ZCA Inventory Unlocked
```

### 2. License Validation

**When Checked:**
- Every time a ZC Inventory page is accessed (dashboard, POS, products, etc.)
- Daily automatic check via WordPress cron
- Manual check via "Check License Status" button

**Cached:**
- License data cached for 24 hours (WordPress transient)
- Reduces API calls and improves performance

**If Invalid:**
- User redirected to license settings page
- Admin notices displayed
- ZC Inventory pages blocked (except login page)

### 3. Trial Period

- **Duration**: 7 days from activation
- **Per Site**: Each site gets its own 7-day trial
- **Warnings**: Alerts shown 3 days before expiration
- **After Expiry**: Plugin access blocked until upgraded to paid license

## Files Created/Modified

### 1. **New File**: `includes/class-zca-license.php`

**Class**: `ZC_License`

**Key Methods:**
- `init()` - Initialize license system
- `activate_license()` - Activate site with API
- `check_license_status()` - Validate license with API
- `is_valid()` - Check if license is valid
- `render_license_page()` - Admin UI for license management
- `show_license_notices()` - Display admin notices

**API Integration:**
- **API URL**: `https://api.benjomabrasado.space/api`
- **Endpoints Used**:
  - `POST /activate` - Activate site
  - `POST /validate` - Check license status
  - `POST /deactivate` - Deactivate site (if user needs to move)

**Security:**
- WordPress nonce validation
- Permission checks (`manage_options` capability)
- Secure API communication via `wp_remote_post()`

### 2. **Modified**: `zca-inventory.php`

**Changes:**
```php
// Line 26: Added license class include
require_once ZC_INVENTORY_PLUGIN_DIR . 'includes/class-zca-license.php';

// Line 88: Initialize license first
ZC_License::init();

// Lines 256-262: License validation on page access
if (!in_array($page, $public_pages)) {
    if (!ZC_License::is_valid()) {
        wp_redirect(admin_url('options-general.php?page=zca-license'));
        exit;
    }
}
```

## WordPress Admin Interface

### Settings → ZCA License

**Features:**

1. **License Key Input**
   - Enter ZCA API key
   - Format: ZCA-XXXX-XXXX-XXXX-XXXX
   - Saved to WordPress options table

2. **Activation Buttons**
   - "Activate This Site" - Starts 7-day trial
   - "Check License Status" - Refreshes validation

3. **License Status Display**
   - ✓ Active / ✗ Invalid status
   - License Type (Trial / Paid)
   - Trial days remaining
   - Trial expiration date
   - Current site URL

4. **Admin Notices**
   - **Warning**: No license key entered
   - **Error**: License invalid or not activated
   - **Warning**: Trial expiring in 3 days
   - **Error**: Trial expired

## API Key Manager Integration

### Endpoints Used

#### 1. Activate Site
```http
POST https://api.benjomabrasado.space/api/activate
Content-Type: application/json

{
  "key": "ZCA-XXXX-XXXX-XXXX-XXXX",
  "siteUrl": "https://customer-site.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Site activated successfully",
  "data": {
    "siteUrl": "https://customer-site.com",
    "trialEndDate": "2025-11-24T00:00:00.000Z",
    "daysRemaining": 7,
    "activeSites": 1,
    "maxSites": 1
  }
}
```

#### 2. Validate License
```http
POST https://api.benjomabrasado.space/api/validate
Content-Type: application/json

{
  "key": "ZCA-XXXX-XXXX-XXXX-XXXX",
  "siteUrl": "https://customer-site.com"
}
```

**Response:**
```json
{
  "success": true,
  "valid": true,
  "message": "API key is valid",
  "data": {
    "isPaid": false,
    "trialEndDate": "2025-11-24T00:00:00.000Z",
    "daysRemaining": 5
  }
}
```

#### 3. Deactivate Site (Optional)
```http
POST https://api.benjomabrasado.space/api/deactivate
Content-Type: application/json

{
  "key": "ZCA-XXXX-XXXX-XXXX-XXXX",
  "siteUrl": "https://customer-site.com"
}
```

**User Can Reactivate**: Unless admin deactivated it

## Testing the Integration

### 1. Create Test API Key

From your API Key Manager dashboard:
1. Login at: https://api.benjomabrasado.space
2. Click "+ Create New Key"
3. Fill in:
   - Name: "Test Customer"
   - Email: "test@example.com"
   - Max Sites: 1
4. Copy the generated key (ZCA-XXXX-XXXX-XXXX-XXXX)

### 2. Install & Test Plugin

1. Install ZCA Inventory on a WordPress site
2. Go to: **Settings → ZCA License**
3. Enter the test API key
4. Click "Save License Key"
5. Click "Activate This Site"
6. Should see: "✓ License activated successfully!"
7. License status should show:
   - Status: ✓ Active
   - License Type: Trial License
   - Trial Days Remaining: 7 days

### 3. Test Access Control

1. Go to ZC Inventory page: `/zca-inventory/dashboard`
2. Should load successfully (license valid)
3. Deactivate license from API Manager
4. Refresh dashboard
5. Should redirect to license settings with error notice

### 4. Test Trial Expiry

**Manually Expire Trial:**
1. In API Manager, select the API key
2. Find the activated site
3. Click "Manage" → Edit trial end date
4. Set to yesterday's date
5. In WordPress, click "Check License Status"
6. Should show: "Your trial has expired"
7. Dashboard access should be blocked

## Admin User Experience

### First Install (No License)
```
Admin Notice:
⚠ ZCA Inventory: Please enter your license key in License Settings

Access to ZC Inventory: BLOCKED
→ Redirected to license settings
```

### License Entered, Not Activated
```
Admin Notice:
✗ ZCA Inventory: Your license is invalid or not activated. Activate your license

Access to ZC Inventory: BLOCKED
→ Redirected to license settings
```

### License Active (Trial)
```
Access to ZC Inventory: ✅ ALLOWED
- Dashboard
- Products
- POS
- Cashiers
- Sales Reports
- Inventory Management
- Settings
```

### Trial Expiring (3 Days Left)
```
Admin Notice:
⚠ ZCA Inventory: Your trial expires in 3 days. Please contact support to upgrade to a paid license.

Access to ZC Inventory: ✅ ALLOWED (for now)
```

### Trial Expired
```
Admin Notice:
✗ ZCA Inventory: Your trial has expired. Please contact support to upgrade to a paid license.

Access to ZC Inventory: BLOCKED
→ Redirected to license settings
```

### Paid License
```
No trial warnings
Access to ZC Inventory: ✅ ALLOWED (indefinitely)
```

## Customer Support Scenarios

### Scenario 1: Customer wants to move site

**What happens:**
1. Customer deactivates from old site: Settings → ZCA License → (contact you)
2. You deactivate from API Manager dashboard
3. Customer activates on new site
4. **Trial continues** from where it left off (not reset)

**Note**: Trial doesn't restart; same trial period continues.

### Scenario 2: Trial expires, customer wants to upgrade

**Process:**
1. Customer contacts you
2. You edit API key in dashboard:
   - Check "Paid License" (isPaid: true)
   - Set expiration date (or leave blank for lifetime)
3. Customer clicks "Check License Status" in WordPress
4. License updated to "Paid License"
5. No more trial warnings

### Scenario 3: Customer exceeds max sites

**What happens:**
1. Customer tries to activate on 2nd site (maxSites: 1)
2. API returns: "Maximum number of sites reached for this API key"
3. Activation fails
4. You can increase maxSites in API Manager dashboard

### Scenario 4: Admin suspends customer

**What happens:**
1. You click "Suspend" on API key in dashboard
2. API key status changes to "suspended"
3. Customer's site validation fails
4. Customer redirected to license settings
5. Admin notice: "Your license is invalid or not activated"

## WordPress Options Storage

**Options Table:**
- `zc_license_key` - Stored API key
- Transient: `zc_license_data` - Cached license validation (24 hours)

**Database Queries:**
- `get_option('zc_license_key')` - Retrieve key
- `get_transient('zc_license_data')` - Get cached status
- `set_transient('zc_license_data', $data, DAY_IN_SECONDS)` - Cache

## Performance Optimization

**Caching Strategy:**
- License data cached for 24 hours
- Reduces API calls from every page load to once daily
- Manual refresh available via "Check License Status" button
- Automatic daily check via WordPress cron

**API Timeout:**
- 15 second timeout for API requests
- Graceful failure handling
- Error messages shown to user

## Security Features

1. **WordPress Nonces**: All AJAX requests validated
2. **Capability Checks**: Only `manage_options` users
3. **HTTPS**: All API calls over secure connection
4. **No API Secrets Stored**: Only license key stored locally
5. **Transient Expiration**: Cached data auto-expires

## Deployment Checklist

- [x] License class created
- [x] Main plugin file updated
- [x] License validation on page access
- [x] Admin menu added
- [x] Admin notices implemented
- [x] AJAX handlers registered
- [x] Daily cron job scheduled
- [x] API integration tested
- [x] Error handling implemented

## Next Steps

1. **Test in Production**
   - Install plugin on test WordPress site
   - Use real API key from https://api.benjomabrasado.space
   - Test activation, validation, and expiry

2. **Customer Documentation**
   - Create guide for customers
   - Include screenshots of license activation
   - Provide troubleshooting steps

3. **Support Workflow**
   - Define process for trial-to-paid conversion
   - Set up customer communication templates
   - Create FAQ for common issues

## Support & Troubleshooting

### API Connection Errors

**Symptom**: "Connection error. Please try again."

**Causes:**
- API Manager is down
- Network connectivity issues
- Firewall blocking requests

**Solution:**
1. Check if https://api.benjomabrasado.space is accessible
2. Check WordPress server's outbound connections
3. Verify firewall allows wp_remote_post()

### License Not Validating

**Symptom**: "Your license is invalid or not activated"

**Causes:**
- API key doesn't exist in database
- Site URL doesn't match activated site
- API key suspended in dashboard
- Trial expired

**Solution:**
1. Check API key format
2. Verify site URL matches exactly
3. Check API Manager dashboard status
4. Click "Check License Status" to refresh

### WordPress Options

Clear cached license data:
```php
delete_transient('zc_license_data');
```

---

**Integration Complete! 🎉**

The ZCA Inventory plugin now has full license management with 7-day trials and seamless API integration.
