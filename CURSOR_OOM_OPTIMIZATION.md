# Cursor OOM (Out of Memory) Optimization Guide

This guide helps you reduce Cursor/VS Code memory usage and prevent "window terminated unexpectedly (reason: 'oom')" crashes on Windows.

---

## 1. Possible Causes of High Memory Usage in This Project

| Cause | Why it matters |
|-------|----------------|
| **Laravel `vendor/`** | Thousands of PHP dependency files (~9k+). Indexing and watching them uses a lot of RAM. |
| **`node_modules/`** | If present, can be 100k+ files. Same issue as vendor. |
| **`storage/framework/views/`** | Compiled Blade templates; constantly changing; watchers and indexers hit these. |
| **`storage/logs/`** | Log files grow; can be large and change often. |
| **`public/items/`** | User-uploaded or generated assets (e.g. barcodes, images). |
| **`public/assets/`** | Front-end assets (JS/CSS/images). |
| **Large Blade/JS files** | `create.blade.php` and similar large files increase parsing/indexing load. |
| **Many open editors** | Each tab keeps file content and language services in memory. |
| **Extensions** | PHP Intelephense, ESLint, Prettier, etc. load language servers and caches. |
| **Git** | Large `.git` history and status checks add overhead. |

---

## 2. Folders to Exclude from Indexing / Search / Watch

Exclude these so Cursor does **not** index, search, or watch them (saves RAM and CPU):

- **Dependencies:** `vendor/`, `node_modules/`
- **Build / cache:** `build/`, `dist/`, `.next/`, `bootstrap/cache/`
- **Storage / cache:** `storage/framework/`, `storage/logs/`
- **Generated / uploads:** `public/items/`, `public/assets/`, `public/build/`, `public/storage/`
- **Version control:** `.git/`
- **IDE:** `.idea/`, `*.log`

Ensure these are listed in:

- **`.cursorignore`** (at project root and/or inside `trader/`) so Cursor’s AI/indexing skips them.
- **VS Code/Cursor `files.exclude`** and **`search.exclude`** so the editor UI and search don’t touch them.
- **`files.watcherExclude`** so the file watcher doesn’t monitor them.

---

## 3. Safe Settings to Reduce Memory in Cursor/VS Code

Use either **User** or **Workspace** settings. Workspace: `.vscode/settings.json` in the project.

### Recommended `.vscode/settings.json` (workspace)

Create or merge into `trader/.vscode/settings.json` (or project root):

```json
{
  "files.exclude": {
    "**/vendor": true,
    "**/node_modules": true,
    "**/storage/framework": true,
    "**/storage/logs": true,
    "**/bootstrap/cache": true,
    "**/public/items": true,
    "**/public/build": true,
    "**/.git": true
  },
  "search.exclude": {
    "**/vendor": true,
    "**/node_modules": true,
    "**/storage": true,
    "**/bootstrap/cache": true,
    "**/public/items": true,
    "**/public/assets": true,
    "**/public/build": true,
    "**/*.min.js": true,
    "**/*.min.css": true
  },
  "files.watcherExclude": {
    "**/vendor/**": true,
    "**/node_modules/**": true,
    "**/storage/**": true,
    "**/bootstrap/cache/**": true,
    "**/public/items/**": true,
    "**/public/assets/**": true,
    "**/.git/**": true
  }
}
```

### User settings (Cursor global)

Path: **`%APPDATA%\Cursor\User\settings.json`** (Windows).

Add or merge (do not remove existing settings you need):

```json
{
  "files.watcherExclude": {
    "**/vendor/**": true,
    "**/node_modules/**": true,
    "**/storage/**": true,
    "**/.git/**": true
  },
  "search.useIgnoreFiles": true,
  "search.followSymlinks": false,
  "editor.largeFileOptimizations": true,
  "files.autoSave": "off"
}
```

- **`editor.largeFileOptimizations`**: Reduces cost for large files.
- **`search.useIgnoreFiles`**: Respects `.gitignore` (and similar) so search skips ignored paths.
- **`files.autoSave": "off"`**: Optional; avoids extra writes/watcher activity if you don’t need auto-save.

---

## 4. Reduce Open Editors, Background Processes, and Extensions

### Open editors

- Prefer **single-folder workspace** (e.g. `trader` only) if you don’t need the whole `MAIN` tree.
- **Close unused tabs**; use “Close Others” / “Close Saved” to keep only what you need.
- After a crash, when Cursor asks to restore, choose **“Don’t restore editors”** so it doesn’t reopen many files at once.
- Limit **pinned tabs**; unpin when not needed.

### Background processes

- **Disable or limit** extensions that run heavy background tasks (e.g. full-project PHP analysis). Configure them to exclude `vendor/` and `storage/`.
- In **Task Manager**, check for multiple `Cursor` or `Code` processes; close extra windows.

### Extensions

- **Disable** extensions you don’t use (Extensions view → Disable).
- For **PHP**: use a single PHP extension (e.g. Intelephense) and set `intelephense.files.exclude` to include `**/vendor/**`, `**/storage/**`.
- For **JavaScript/Blade**: avoid duplicate formatters/linters; disable for folders that don’t need them.

---

## 5. RAM and Virtual Memory (Windows)

- **8 GB RAM**: Often tight with Cursor + browser + Laravel; OOM is more likely.
- **16 GB RAM**: Recommended for comfortable use with this project.
- **Virtual memory (page file)**:
  - **Settings → System → About → Advanced system settings → Performance Settings → Advanced → Virtual memory → Change.**
  - Uncheck “Automatically manage paging file” for the drive where Windows is installed.
  - Set **Custom size**: Initial = 2048 MB, Maximum = 8192 MB (or higher if you have free disk space).
  - Click **Set**, then **OK**, and **restart** if prompted.

This gives Windows more room when physical RAM is full and can reduce hard OOM crashes (at the cost of some speed when swapping).

---

## 6. Step-by-Step Instructions to Prevent OOM (Keep Project Usable)

### One-time setup

1. **Exclude heavy folders**
   - Ensure **`.cursorignore`** exists in the project (e.g. `trader/.cursorignore`) and lists:
     - `vendor/`, `node_modules/`, `storage/`, `bootstrap/cache/`, `public/items/`, `public/assets/`, `public/build/`, `.git/`.
   - Add **`.vscode/settings.json`** under the project root (or `trader/`) with the `files.exclude`, `search.exclude`, and `files.watcherExclude` entries from section 3.

2. **Cursor/VS Code settings**
   - Open **File → Preferences → Settings** (or `Ctrl+,`).
   - Search for **files.watcherExclude** and add the same patterns as above (or paste the JSON from section 3 into User `settings.json`).
   - Set **search.useIgnoreFiles** to `true`, **search.followSymlinks** to `false`, **editor.largeFileOptimizations** to `true` if available.

3. **Restart Cursor**
   - Fully quit Cursor and reopen the project so new excludes and settings take effect.

### Daily habits

4. **Open only the folder you need**
   - Use **File → Open Folder** and open `trader` (or the smallest root that contains your code) instead of a parent with many sibling folders.

5. **Limit open tabs**
   - Keep under ~10–15 editors; close files you’re not editing.
   - Use **“Don’t restore editors”** after a crash.

6. **Disable unneeded extensions**
   - Disable heavy or duplicate extensions; enable only when needed.

7. **After heavy work**
   - Close unused tabs and, if needed, **Reload Window** (Command Palette → “Developer: Reload Window”) to free memory.

### If OOM still happens

8. **Increase virtual memory** (see section 5).
9. **Upgrade RAM** to 16 GB if possible.
10. **Use a smaller workspace** (only `trader`) and keep `vendor` and `storage` excluded everywhere (`.cursorignore`, `files.exclude`, `search.exclude`, `files.watcherExclude`).

---

## Windows-Specific Steps

- **Virtual memory**: As in section 5, set a fixed page file (e.g. 2048–8192 MB) and restart.
- **Antivirus**: Exclude the project folder (e.g. `c:\xampp\htdocs\MAIN`) from real-time scanning to reduce I/O and CPU.
- **Power plan**: Use **High performance** or **Balanced** so the system doesn’t aggressively throttle.
- **Cursor path**: If Cursor is on a slow or network drive, moving it to a fast local SSD can help.

---

## Cursor-Specific Tips

- **Don’t restore editors** after a crash to avoid reopening many files.
- Rely on **`.cursorignore`** so Cursor’s AI and indexing skip `vendor`, `node_modules`, and storage.
- Prefer **single-folder workspace** (e.g. `trader`) to reduce scope of indexing and watchers.
- In **Cursor Settings**, review **Features** and **Extensions**; turn off or limit heavy AI/indexing on very large folders.
- If the project is under **Git**, ensure `.gitignore` is correct so Cursor and extensions don’t index build/cache artifacts.

---

## Best Practices for Large Projects

1. **One codebase folder per workspace** when possible (e.g. open `trader`, not the whole `MAIN` tree).
2. **Exclude by default**: `vendor`, `node_modules`, `storage`, `build`, `dist`, `.git`, and large asset folders in both `.cursorignore` and editor settings.
3. **Watcher exclusions** are critical; without them, file watchers can use a lot of memory on big trees.
4. **Fewer open editors** and fewer extensions; enable language servers only for languages you use.
5. **Restart Cursor** after changing exclude/watcher settings so they apply fully.
6. **Monitor memory**: Use Task Manager (Ctrl+Shift+Esc) to see Cursor’s memory; if it grows toward 2–3 GB, close tabs and reload the window.

---

## Summary Checklist

- [ ] **Root `.cursorignore`** (if workspace is `MAIN`): create `c:\xampp\htdocs\MAIN\.cursorignore` with:
  ```
  trader/vendor/
  trader/node_modules/
  trader/storage/
  trader/bootstrap/cache/
  trader/public/items/
  trader/public/assets/
  .git/
  ```
- [ ] **`trader/.cursorignore`** already exists; ensure it lists `vendor/`, `node_modules/`, `storage/`, `public/items`, `.git/` (paths relative to trader).
- [ ] `.vscode/settings.json` has `files.exclude`, `search.exclude`, `files.watcherExclude` for the same
- [ ] `.vscode/settings.json` has `files.exclude`, `search.exclude`, `files.watcherExclude` for the same
- [ ] User or workspace: `files.watcherExclude` and `search.useIgnoreFiles` set
- [ ] After crash: “Don’t restore editors”
- [ ] Prefer opening only `trader` (or the app root) as the workspace
- [ ] Virtual memory (page file) set on Windows if RAM is 8 GB
- [ ] Unused extensions disabled; PHP/JS config excludes `vendor` and `storage`

If you want, the next step can be to add a ready-to-use `trader/.vscode/settings.json` with these excludes so you only need to drop it in and reload the window.
