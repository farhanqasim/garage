# Reminders – Implementation Summary (Elite Car Wash / Trader)

Yeh document batata hai ke **Reminders** app mein kahan aur kaise implement hain, taake aap inhe use kar saken ya kisi ko script de saken.

---

## 1. Reminders kya dikhate hain?

| Section | Kya count hota hai | Link / Action |
|--------|---------------------|----------------|
| **Sales Invoices Pending** | Jo sales/invoices jahan payment baaki (grand_total - total_paid > 0) | View → Sale detail |
| **Purchases / Payment Recovery** | Jo purchases jahan amount baaki (payable) | View → Purchase detail |
| **Elite Car Wash – Jobs / Payment Pending** | (1) Active jobs, (2) Completed jobs jahan abhi tak koi completed payment record nahi | View → Car Wash job |
| **Task Reminders** | Task reminders jinki status "Completed" nahi | Open → Task Reminder page |

---

## 2. Kahan se kholen?

- **Sidebar:** **Reminders** link (bell icon) – click karke `/reminders` open hota hai.
- **Badge:** Sidebar mein Reminders ke saath **red badge** total pending count dikhata hai (sales + purchases + car wash + tasks).
- **Direct URL:** `https://your-domain.com/reminders` (auth required).

---

## 3. Files / Code locations (script for app)

### Routes (`routes/web.php`)

```php
Route::get('/reminders', [\App\Http\Controllers\RemindersController::class, 'index'])->name('reminders.index')->middleware('auth');
Route::get('/reminders/counts', [\App\Http\Controllers\RemindersController::class, 'counts'])->name('reminders.counts')->middleware('auth');
```

### Controller

- **File:** `app/Http/Controllers/RemindersController.php`
- **index():** Sales pending, Purchases pending, Car Wash pending, Task reminders collect karke `reminders.index` view ko pass karta hai.
- **counts():** JSON mein `sales_pending`, `purchases_pending`, `car_wash_pending`, `task_reminders`, `total` return karta hai (header/sidebar badge ke liye).

### View

- **File:** `resources/views/reminders/index.blade.php`
- 4 summary cards (counts) + 4 tables (Sales, Purchases, Car Wash, Task Reminders) with View/Open buttons.

### Sidebar (Reminders link + badge)

- **File:** `resources/views/include/sidebar.blade.php`
- Reminders link: `route('reminders.index')`
- Badge element: `id="sidebar-reminders-badge"` (start mein `d-none`, count load hone par show hota hai).

### Badge count load (layout)

- **File:** `resources/views/layouts/app.blade.php` (end ke paas script)
- `DOMContentLoaded` par `GET /reminders/counts` call hota hai, response se `total` le kar badge text set hota hai aur agar total > 0 to badge visible.

---

## 4. Logic short (reference)

- **Sales pending:** `Sale` model, `grand_total - total_paid > 0` (branch filter optional).
- **Purchases pending:** `Purchase` model, same remaining amount logic (branch filter optional).
- **Car wash pending:**  
  - `CarWashJob` status = `active` **ya**  
  - status = `completed` lekin `car_wash_payments` mein us job_id ke liye koi `status = 'completed'` payment nahi.
- **Task reminders:** `TaskReminder` jahan `status != 'Completed'`.

Sab jagah `branch_id` (session `selected_branch_id` ya user's branch) se filter ho sakta hai.

---

## 5. Naye reminder types add karna (future)

Agar baad mein aur cheezen add karni hon (e.g. services, other invoices):

1. **RemindersController@index:** nayi collection banao (e.g. `$servicesPending`), query chala kar view ko pass karo.
2. **RemindersController@counts:** nayi key add karo (e.g. `services_pending`) aur `total` mein add karo.
3. **reminders/index.blade.php:** nayi card + table section add karo.
4. Sidebar/layout same rehta hai – `total` already sab count ka sum hai.

---

## 6. Quick test

1. Login karke sidebar se **Reminders** open karo.
2. Check karo: Sales / Purchases / Car Wash / Task Reminders sections aur counts.
3. Sidebar badge: koi pending ho to number dikhna chahiye, warna 0 ya hide.

---

**Summary:** Invoices pending (sales), payments recovery (purchases), Elite Car Wash (new job / payment pending), aur task reminders – ye sab **Reminders** page par already implement hain. Is document ko app ke sath script/reference ki tarah use kiya ja sakta hai.
