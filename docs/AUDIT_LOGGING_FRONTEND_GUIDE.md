# Audit Logging Frontend Integration Guide

## Overview
The audit logging system has been integrated into all edit/update forms. Each form now displays who created the record and who last modified it using the new `AuditInfoCard` component.

## What Changed

### New Component: AuditInfoCard.vue
**Location:** `resources/js/Components/AuditInfoCard.vue`

This component displays:
- **Created**: Date/time and user who created the record
- **Last Modified**: Date/time and user who last modified the record (only shown if modified)
- **Not Modified Notice**: Displays if the record has never been updated

### Updated Forms
All five forms now use the `AuditInfoCard` component:
1. **EditItemForm.vue** - Inventory Items
2. **EditPersonnelForm.vue** - Personnel Management
3. **EditSupplierForm.vue** - Supplier Management
4. **IncomingUpdateForm.vue** - Incoming Transactions
5. **OutgoingUpdateForm.vue** - Outgoing Transactions

### New Controller Helper Method
**Location:** `app/Http/Controllers/BaseController.php`

Added `loadAuditLogs(Model $model)` method to simplify loading audit logs for any model.

## Implementation Steps for Controllers

To enable audit information display in your edit/show pages, follow these steps:

### 1. ItemController (Items Edit Page)

```php
use Inertia\Inertia;

public function edit($id)
{
    $item = Item::findOrFail($id);
    
    // Load audit logs
    $auditLogs = $this->loadAuditLogs($item);
    
    return Inertia::render('Inventory/Items/Edit', [
        'data' => $item,
        'auditLogs' => $auditLogs,
        'categories' => Category::all(),
        'suppliers' => Supplier::all(),
    ]);
}
```

### 2. PersonnelController (Personnel Edit Page)

```php
use Inertia\Inertia;

public function edit($id)
{
    $personnel = Personnel::findOrFail($id);
    
    // Load audit logs
    $auditLogs = $this->loadAuditLogs($personnel);
    
    return Inertia::render('Inventory/Personnel/Edit', [
        'data' => $personnel,
        'auditLogs' => $auditLogs,
    ]);
}
```

### 3. SupplierController (Supplier Edit Page)

```php
use Inertia\Inertia;

public function edit($id)
{
    $supplier = Supplier::findOrFail($id);
    
    // Load audit logs
    $auditLogs = $this->loadAuditLogs($supplier);
    
    return Inertia::render('Inventory/Supplier/Edit', [
        'data' => $supplier,
        'auditLogs' => $auditLogs,
    ]);
}
```

### 4. TransactionController (Transactions Edit Page)

```php
use Inertia\Inertia;

public function editIncoming($id)
{
    $transaction = Transaction::findOrFail($id);
    
    // Load audit logs
    $auditLogs = $this->loadAuditLogs($transaction);
    
    return Inertia::render('Inventory/Transactions/EditIncoming', [
        'data' => $transaction,
        'auditLogs' => $auditLogs,
        // ... other data
    ]);
}

public function editOutgoing($id)
{
    $transaction = Transaction::findOrFail($id);
    
    // Load audit logs
    $auditLogs = $this->loadAuditLogs($transaction);
    
    return Inertia::render('Inventory/Transactions/EditOutgoing', [
        'data' => $transaction,
        'auditLogs' => $auditLogs,
        // ... other data
    ]);
}
```

## Component Props

### AuditInfoCard Component

```vue
<audit-info-card
    :audit-logs="$page.props.auditLogs"
    :created-at="$page.props.data.created_at"
    :updated-at="$page.props.data.updated_at"
/>
```

| Prop | Type | Description |
|------|------|-------------|
| `auditLogs` | Array | Array of audit log objects with user relationships |
| `created-at` | String | ISO datetime string of creation date |
| `updated-at` | String | ISO datetime string of last update date |

## Audit Log Object Structure

Each audit log object contains:
```php
{
    "id": "uuid",
    "user_id": "uuid",
    "model_type": "App\Models\Item",
    "model_id": "uuid",
    "action": "created|updated|deleted|force_deleted",
    "old_values": {...},
    "new_values": {...},
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "created_at": "2026-02-02T10:30:00",
    "updated_at": "2026-02-02T10:30:00",
    "user": {
        "id": "uuid",
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

## Display Format

### Created Section
```
Created
Feb 02, 2026 10:30 AM
by John Doe
```

### Last Modified Section (if applicable)
```
Last Modified
Feb 02, 2026 2:45 PM
by Jane Smith
```

### If No Modifications
```
No modifications since creation
```

## Usage Examples

### Query Audit History in Controller
```php
// Get all changes for a model
$changes = $model->auditLogs()->get();

// Get only updates
$updates = $model->auditLogs()->where('action', 'updated')->get();

// Get who created it
$creator = $model->getCreatedByUser();

// Get who last modified it
$lastModifier = $model->getLastModifiedByUser();

// Check if modified
if ($model->hasBeenModified()) {
    // Do something
}
```

### Query Audit History Globally
```php
// Get all changes for a specific user
$changes = AuditLog::where('user_id', $userId)->latest()->get();

// Get all changes for a specific model type
$changes = AuditLog::forModel('App\Models\Transaction')->get();

// Get all updates across all models
$updates = AuditLog::forAction('updated')->latest()->get();
```

## Notes

1. **Automatic Logging**: All insert, update, delete, and force-delete operations are automatically logged for audited models.

2. **User Attribution**: The authenticated user making the change is automatically captured via `Auth::id()`.

3. **IP Tracking**: The IP address of the request is captured for security auditing.

4. **Sensitive Data**: Passwords, tokens, and other sensitive fields are automatically redacted in audit logs (see `filterSensitiveData()` in AuditObserver).

5. **Timezone**: Audit timestamps use the configured Laravel timezone. Ensure your app timezone is properly set in `config/app.php`.

6. **Eager Loading**: When you need to display audit logs in multiple records, eager load them to avoid N+1 queries:
   ```php
   $items = Item::with('auditLogs.user')->get();
   ```

## Troubleshooting

### Audit logs not showing in edit pages
- Ensure the model has the `Auditable` trait applied
- Check that audit logs are being passed in the Inertia props
- Verify the audit_logs table is populated with data

### Seeing "[REDACTED]" for all sensitive fields
- This is expected behavior for security
- Sensitive fields like passwords are automatically masked

### Timestamps showing wrong time
- Check your `.env` file for `APP_TIMEZONE`
- Ensure database timestamp is using UTC (recommended)
