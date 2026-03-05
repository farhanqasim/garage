# Database connection error: "No connection could be made"

**Error:** `SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it`

This means the app cannot reach MySQL. Fix it as follows.

---

## 1. Start MySQL (XAMPP)

1. Open **XAMPP Control Panel**.
2. Click **Start** next to **MySQL**.
3. Wait until the status shows “Running” (green).

If MySQL won’t start, check the XAMPP logs (e.g. `xampp/mysql/data/*.err`) for port conflicts or permission errors.

---

## 2. Check your `.env` (copy from `.env.example` if needed)

Make sure your `.env` has:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=root
DB_PASSWORD=
```

- **XAMPP default:** `DB_USERNAME=root`, `DB_PASSWORD=` (empty).
- If you use another DB name/user (e.g. from hosting), set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` to match.

---

## 3. Try `localhost` instead of `127.0.0.1`

If it still fails, set:

```env
DB_HOST=localhost
```

Then run:

```bash
php artisan config:clear
```

---

## 4. Test the connection

```bash
php artisan migrate:status
```

If this runs without the “connection refused” error, the app can connect to the database.
