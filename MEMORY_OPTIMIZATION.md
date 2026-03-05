# Memory Optimization Guide

This document describes measures taken to reduce Out of Memory (OOM) errors in both the **Laravel application** and **Cursor IDE**.

---

## 1. Laravel application (PHP)

### Configured limits

- **PHP memory limit**: Set in `public/index.php` from `MEMORY_LIMIT` in `.env` (default `256M`). Increase for heavy reports if needed, e.g. `MEMORY_LIMIT=512M`.
- **Max items per request**: `config('app.max_items_per_request')` (default `1000`). Used when loading “all” items or the recycle bin. Override with `MAX_ITEMS_PER_REQUEST` in `.env`.
- **Stock report warehouse rows**: `config('app.max_warehouse_items_report')` (default `10000`). Override with `MAX_WAREHOUSE_ITEMS_REPORT` in `.env`.

### Code changes

- **Unbounded queries capped**: Endpoints that previously loaded all matching rows (e.g. “all items”, recycle bin, warehouse stock report) now use the configurable limits above.
- **Car wash job filters**: User filter options are built from distinct `user_id`s via a single query instead of loading all jobs in memory.
- **OOM handling**: When PHP hits the memory limit, the exception handler returns a clear 509 response (and JSON for API requests) instead of a generic fatal error.

### Optional .env tuning

```env
MEMORY_LIMIT=256M
MAX_ITEMS_PER_REQUEST=1000
MAX_WAREHOUSE_ITEMS_REPORT=10000
```

---

## 2. Cursor IDE (Electron)

The “window terminated unexpectedly (reason: 'oom')” message comes from **Cursor/Electron**, not from PHP. To reduce the chance of Cursor running out of memory:

1. **Reopen with fewer editors**: When Cursor offers to reopen, check **“Don’t restore editors”** so it doesn’t reload many tabs at once.
2. **Close unused tabs**: Keep only the files you need open.
3. **Reduce workspace load**: This repo includes a `.cursorignore` so Cursor skips indexing `vendor/`, `node_modules/`, `storage/`, and large asset directories. That lowers memory and CPU use.
4. **System RAM**: If you have many extensions or very large projects, 8GB RAM can be tight; 16GB is more comfortable.

---

## 3. Summary

| Area              | Change |
|-------------------|--------|
| PHP memory        | `MEMORY_LIMIT` in `.env`, default 256M in `public/index.php` |
| Item/recycle lists| Capped via `max_items_per_request` (default 1000) |
| Stock report      | Warehouse rows capped via `max_warehouse_items_report` (default 10000) |
| Car wash filters  | User list built from distinct IDs, no full job load |
| OOM response      | Handled in `App\Exceptions\Handler` with 509 body |
| Cursor            | `.cursorignore` added; use “Don’t restore editors” and fewer tabs if OOM recurs |
