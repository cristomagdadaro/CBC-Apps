# CRCMDatatable Write-Flow Analysis

## Scope Reviewed

- `resources/js/Components/CRCMDatatable/CRCMDatatable.vue`
- `resources/js/Components/CRCMDatatable/core/CRCMDatatable.js`
- `resources/js/Modules/infrastructure/ApiService.ts`

---

## High-level write architecture

`CRCMDatatable.vue` is the presentation/orchestration layer. It opens form and confirmation dialogs, then delegates all data-write actions to the `dt` instance (`new CRCMDatatable(...)`).

`core/CRCMDatatable.js` is the write executor. It translates UI actions into API calls (`post`, `put`, `delete`), handles success/error checks, and refreshes table data after successful writes.

`ApiService.ts` is the transport layer. It performs actual HTTP requests, handles CSRF bootstrap, multipart detection for file uploads, and emits global browser notifications.

---

## How each write action is handled

## 1) Create

UI trigger in `CRCMDatatable.vue`:

- Add dialog emits `submitForm`.
- Handler calls `dt.create($event)`.

Core handling in `CRCMDatatable.js`:

- `create(data)` calls `this.api.post(this.model.toObject(data))`.
- On success (`checkForErrors` returns true), it calls `refresh()`.
- `refresh()` calls `init()` to re-fetch table data.

Transport in `ApiService.ts`:

- `post()` ensures CSRF cookie if needed.
- Detects binary payload and auto-switches to `multipart/form-data`.
- Emits success/error notification events.

---

## 2) Update

UI trigger in `CRCMDatatable.vue`:

- Edit button opens edit dialog (`showEditDialogFunc`).
- Form emits `submitForm`.
- Handler calls `dt.update($event)`.

Core handling in `CRCMDatatable.js`:

- `update(data)` calls `this.api.put(this.model.toObject(data))`.
- On success, refreshes table and returns `true`; otherwise returns `false`.

Transport in `ApiService.ts`:

- `put()` supports JSON updates and multipart fallback.
- If multipart is needed, it sends `_method=PUT` via POST.
- Emits success/error notification events.

---

## 3) Delete single row

UI trigger in `CRCMDatatable.vue`:

- Delete button opens confirmation modal.
- Confirm action calls `dt.delete(toDeleteId)`.

Core handling in `CRCMDatatable.js`:

- `delete(id)` calls `this.api.delete(id)`.
- On success, refreshes.
- Removes deleted id from local `selected` state.

Transport in `ApiService.ts`:

- `delete(url, id)` sends DELETE request to route(url, id).
- Emits warning notification for successful delete.

---

## 4) Delete selected rows (batch)

UI trigger in `CRCMDatatable.vue`:

- “Delete selected” modal confirm calls `dt.deleteSelected()`.

Core handling in `CRCMDatatable.js`:

- Calls `this.api.delete(this.selected)`.
- On success, refreshes table.
- Clears all selected ids.

Note: batch delete behavior depends on backend route/controller accepting an array of ids as route parameter payload contract.

---

## 5) Import CSV (multi-create loop)

UI trigger in `CRCMDatatable.vue`:

- Import modal emits `uploadForm`.
- Handler calls `dt.importCSV($event)`.

Core handling in `CRCMDatatable.js`:

- Iterates each CSV row.
- For each row, runs `this.api.post(this.model.toObject(row))`.
- Tracks `success`, `failed`, `total` counters.
- Shows summary notification (success/partial/failed).
- Refreshes table only when no failures.

Write characteristic:

- Sequential row-by-row writes (not batched, not parallel).

---

## Error handling model

In core class:

- `checkForErrors(response)` checks if response is `instanceof BaseResponse`.
- Success => `closeAllModal = true`; failure => creates Notification.

In transport layer:

- HTTP methods throw on failure.
- Notifications are emitted with `window.dispatchEvent('cbc:notify', ...)`.

Implication:

- There are two notification pathways (core Notification object + global event from ApiService).

---

## Data refresh behavior after write

After successful create/update/delete/deleteSelected:

- `refresh()` is executed.
- `init()` re-fetches data and recalculates columns.

For import:

- Refresh is conditional (`failed <= 0`).

---

## Observed implementation gaps impacting writes

1. **Import uses unresolved `ErrorResponse` dependency**
   - `CRCMDatatable.js` imports `@/Pages/constants` for `ErrorResponse`, but matching file/export was not found in scanned workspace paths.
   - This can break runtime checks in `importCSV()`.

2. **Response type check mismatch risk**
   - `CRCMDatatable.js` expects `BaseResponse` from `@/Modules/core/domain/base/BaseResponse`.
   - Current module structure appears to use `resources/js/Modules/domain/Response.ts` and `resources/js/Modules/infrastructure/ApiService.ts`.
   - If aliases or classes diverge, `instanceof BaseResponse` may fail and misclassify success/failure.

3. **Datatable initialization does not call `init()`**
   - In `initializeDatatable()`, the line `await this.dt.init()` is commented out.
   - This can delay/skip first data load, including post-write consistency expectations.

4. **Dual feedback systems**
   - Core class emits Notification directly; API layer emits global `cbc:notify` events.
   - Can cause duplicated or inconsistent user feedback after writes.

---

## Recommended write-path hardening

1. Replace `instanceof`-based success detection with status/shape checks (`response.status`, payload contract).
2. Resolve/replace `ErrorResponse` source used by CSV import.
3. Ensure initial `dt.init()` is executed when datatable mounts.
4. Standardize on a single notification mechanism for write operations.
5. For CSV import, consider backend batch endpoint (single request) or controlled concurrency to improve throughput and consistency.

---

## Write-flow summary

`CRCMDatatable.vue` collects user intent -> `CRCMDatatable.js` performs CRUD/import orchestration -> `ApiService.ts` executes HTTP writes with CSRF + payload adaptation -> on success the table is refreshed to reconcile UI state with server state.
