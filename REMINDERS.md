# Reminders – Implementation Guide (App Script)

Yeh document batata hai ke **Reminders** app mein kahan se lagaye gaye hain aur kaise use karein.

---

## 1. Reminders Kya Cover Karte Hain?

| Type | Kya Dikhta Hai | Link |
|------|----------------|------|
| **Sales Invoices Pending** | Jo sales/invoices abhi fully paid nahi (remaining amount > 0) | Sales → View invoice |
| **Purchases / Payment Recovery** | Jo purchases abhi fully paid nahi (supplier ko dena baaki) | Purchases → View |
| **Elite Car Wash – Jobs / Payment Pending** | New job create hui lekin payment pending, ya completed job jiska payment abhi record nahi | Car Wash page |
| **Task Reminders** | Manual task reminders (title, priority, status, assignee) | Task Reminder page |

Sab **branch-wise** filter hote hain (selected branch / user branch).

---

## 2. Kahan Se Reminders Open Karein?

- **Sidebar:** **Reminders** link (bell icon) – yahan total pending count badge bhi dikhta hai.
- **Direct URL:** `/reminders` (route name: `reminders.index`).

Sidebar mein badge automatically **/reminders/counts** API se load hota hai.

---

## 3. Files / Components (Implementation Reference)

### Backend
- **Controller:** `app/Http/Controllers/RemindersController.php`
  - `index()` – reminders dashboard (sales, purchases, car wash, task reminders)
  - `counts()` – JSON API for badge (sales_pending, purchases_pending, car_wash_pending, task_reminders, total)

### Routes (`routes/web.php`)
```php
Route::get('/reminders', [RemindersController::class, 'index'])->name('reminders.index')->middleware('auth');
Route::get('/reminders/counts', [RemindersController::class, 'counts'])->name('reminders.counts')->middleware('auth');
```

### View
- **Blade:** `resources/views/reminders/index.blade.php`
  - 4 summary cards (counts)
  - 4 tables: Sales Pending, Purchases Pending, Car Wash Pending, Task Reminders
  - Har row mein "View" / "Open" button se related page open hota hai

### Sidebar Link + Badge
- **Sidebar:** `resources/views/include/sidebar.blade.php` – "Reminders" link + `#sidebar-reminders-badge`
- **Badge load:** `resources/views/layouts/app.blade.php` – DOMContentLoaded par `/reminders/counts` fetch karke badge update

### Models (Data Source)
- Sales pending: `App\Models\Sale` (remaining = grand_total - total_paid)
- Purchases pending: `App\Models\Purchase` (remaining = grand_total - total_paid)
- Car wash: `App\Models\CarWashJob` (active jobs + completed jobs with no completed CarWashPayment)
- Tasks: `App\Models\TaskReminder` (status != 'Completed')

---

## 4. Logic Summary (Script / Pseudo-code)

```
REMINDERS INDEX:
  branchId = session selected_branch OR user branch

  1) Sales Pending
     - Sale where (grand_total - total_paid) > 0, branch = branchId
     - Show: reference, customer, remaining, date, link to sales.show

  2) Purchases Pending (Payment Recovery)
     - Purchase where (grand_total - total_paid) > 0, branch = branchId
     - Show: reference, supplier, remaining, date, link to purchases.show

  3) Car Wash Pending
     - CarWashJob where status = 'active' OR (status = 'completed' AND no CarWashPayment completed for job), branch = branchId
     - Show: service_name, customer/vehicle, price, status, time, link to car wash page

  4) Task Reminders
     - TaskReminder where status != 'Completed', branch = branchId
     - Show: title, priority, status, assignee, link to task reminder page

  totalCount = sum of above counts
```

---

## 5. Naya Reminder Type Add Karna (Future)

Agar baad mein aur cheezen (e.g. services invoices, custom reminders) add karni hon:

1. **RemindersController@index** – nayi collection banao (e.g. `$servicesPending`) aur same pattern se fill karo.
2. **reminders/index.blade.php** – nayi card + table section add karo.
3. **RemindersController@counts** – nayi key (e.g. `services_pending`) aur `total` mein add karo.
4. **Sidebar/layout** – agar koi alag badge chahiye to optional; warna `total` wala hi sab dikhata hai.

---

## 6. Quick Checklist (App Ko Wapis Dene Ke Liye)

- [x] Reminders page: `/reminders` (sales, purchases, car wash, task reminders)
- [x] Sidebar: "Reminders" link + badge (count)
- [x] Badge: `/reminders/counts` se auto-update
- [x] Sales pending: invoice link → `route('sales.show', $id)`
- [x] Purchases pending: link → `route('purchases.show', $id)`
- [x] Car wash: link → Car Wash page with job id
- [x] Task reminders: link → Task Reminder page

Yeh script/implementation app mein already lag chuka hai; sirf is document ko app ke sath rakh kar reference use karein.
