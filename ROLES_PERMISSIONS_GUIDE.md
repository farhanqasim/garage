# Roles & Permissions System Guide

## Overview
Complete roles and permissions system using Spatie Permission package. This system allows you to control access to different modules and features based on user roles.

## Setup Instructions

### 1. Run Migration (if not already done)
```bash
php artisan migrate
```

### 2. Create Permissions
You can create all permissions in two ways:

**Option A: Using Seeder**
```bash
php artisan db:seed --class=PermissionSeeder
```

**Option B: Using UI**
- Go to Roles & Permissions page
- Click "Create All Permissions" button

### 3. Assign Roles to Users
```php
// In tinker or seeder
$user = User::find(1);
$user->assignRole('Admin');
```

## Available Permissions

### Dashboard
- `view-dashboard` - View dashboard
- `view-reports` - View reports
- `view-analytics` - View analytics

### Items Management
- `view-items` - View items list
- `create-items` - Create new items
- `edit-items` - Edit existing items
- `delete-items` - Delete items
- `duplicate-items` - Duplicate items
- `view-item-details` - View item details
- `export-items` - Export items
- `import-items` - Import items

### Branches
- `view-branches` - View branches
- `create-branches` - Create branches
- `edit-branches` - Edit branches
- `delete-branches` - Delete branches
- `manage-branch-status` - Manage branch status
- `view-all-branches` - View all branches (admin only)

### Sales
- `view-sales` - View sales
- `create-sales` - Create sales
- `edit-sales` - Edit sales
- `delete-sales` - Delete sales
- `view-sales-reports` - View sales reports
- `process-sales-return` - Process sales returns

### Purchases
- `view-purchases` - View purchases
- `create-purchases` - Create purchases
- `edit-purchases` - Edit purchases
- `delete-purchases` - Delete purchases
- `view-purchase-reports` - View purchase reports

### Customers
- `view-customers` - View customers
- `create-customers` - Create customers
- `edit-customers` - Edit customers
- `delete-customers` - Delete customers
- `view-customer-details` - View customer details

### Suppliers
- `view-suppliers` - View suppliers
- `create-suppliers` - Create suppliers
- `edit-suppliers` - Edit suppliers
- `delete-suppliers` - Delete suppliers
- `view-supplier-details` - View supplier details

### Users
- `view-users` - View users
- `create-users` - Create users
- `edit-users` - Edit users
- `delete-users` - Delete users
- `manage-user-roles` - Manage user roles

### Roles & Permissions
- `view-roles` - View roles
- `create-roles` - Create roles
- `edit-roles` - Edit roles
- `delete-roles` - Delete roles
- `assign-permissions` - Assign permissions

### POS
- `access-pos` - Access POS system
- `process-pos-sales` - Process POS sales
- `view-pos-reports` - View POS reports

### Categories
- `view-categories` - View categories
- `create-categories` - Create categories
- `edit-categories` - Edit categories
- `delete-categories` - Delete categories

### Settings
- `view-settings` - View settings
- `edit-settings` - Edit settings
- `manage-system-settings` - Manage system settings

## Default Roles

### Super Admin
- Has all permissions
- Cannot be edited or deleted
- Full system access

### Admin
- Has most permissions except role management
- Can manage all modules except roles

### User
- Limited permissions
- Can view items, create sales, access POS

### Branch Manager
- Branch-specific permissions
- Can manage items, sales, purchases for their branch

## Usage in Controllers

### Method 1: Middleware in Constructor
```php
public function __construct()
{
    $this->middleware('permission:view-items')->only('index');
    $this->middleware('permission:create-items')->only('create', 'store');
    $this->middleware('permission:edit-items')->only('edit', 'update');
    $this->middleware('permission:delete-items')->only('destroy');
}
```

### Method 2: In Methods
```php
public function index()
{
    if (!auth()->user()->can('view-items')) {
        abort(403, 'Unauthorized');
    }
    // Your code
}
```

## Usage in Routes

```php
Route::get('/items', [ItemController::class, 'index'])
    ->middleware('permission:view-items')
    ->name('items.index');
```

## Usage in Blade Views

```blade
@can('create-items')
    <a href="{{ route('items.create') }}" class="btn btn-primary">Create Item</a>
@endcan

@can('edit-items')
    <a href="{{ route('items.edit', $item->id) }}">Edit</a>
@endcan

@can('delete-items')
    <form action="{{ route('items.destroy', $item->id) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit">Delete</button>
    </form>
@endcan
```

## Branch-Specific Permissions

For branch-specific access, check both permission and branch:

```php
public function index()
{
    $user = auth()->user();
    
    if ($user->hasRole('Branch Manager')) {
        // Show only their branch data
        $branchId = session('selected_branch_id');
        $items = Item::where('branch_id', $branchId)->get();
    } else {
        // Show all data
        $items = Item::all();
    }
}
```

## Assigning Roles to Users

### Via Code
```php
$user = User::find(1);
$user->assignRole('Admin');
$user->assignRole(['Admin', 'Branch Manager']); // Multiple roles
```

### Via UI
- Go to Users page
- Edit user
- Select role(s)

## Creating Custom Permissions

Add to `User::getpermissionGroups()` method:

```php
'Custom Module' => [
    'view-custom',
    'create-custom',
    'edit-custom',
    'delete-custom',
],
```

Then run permission seeder or use "Create All Permissions" button.

## Testing Permissions

```php
// Check if user has permission
$user->can('view-items');

// Check if user has role
$user->hasRole('Admin');

// Check if user has any of these roles
$user->hasAnyRole(['Admin', 'Branch Manager']);

// Check if user has all of these roles
$user->hasAllRoles(['Admin', 'Branch Manager']);
```

## Important Notes

1. **Super Admin** role cannot be edited or deleted
2. Roles assigned to users cannot be deleted (must remove users first)
3. Permissions are cached - clear cache after changes:
   ```bash
   php artisan permission:cache-reset
   ```
4. Always use `@can` directive in views for UI elements
5. Always check permissions in controllers for security

