# JAPS POS: Delivery Driver App API (v1)

The REST API behind the native **Delivery (driver)** app. It replaces the `/delivery` PWA (webview) and uses the same delivery service as Admin → Delivery Management, so route status, stop status and invoice delivery status stay in sync.

- Postman collection: `JAPS-POS-Delivery-App.postman_collection.json` (same folder)
- All endpoints: `{BASE_URL}/api/v1/delivery/...`

---

## 1. Base URL (one app, many client servers)

1. On first launch, ask for the **Server URL** (e.g. `https://pos.clientname.com`). Trim it, add `https://` if missing, and remove any trailing `/` and `/api…`.
2. Validate it with `GET {BASE_URL}/api/v1/ping` (public):
```json
{ "success": true, "message": "Server reachable.", "data": { "app_name": "Japs POS", "api": "japspos-mobile", "api_version": "1.0.0", "base_url": "https://pos.clientname.com", "server_time": "2026-10-09T15:21:22-04:00", "apps": ["sale","delivery","customer"] } }
```
Accept it only when `success === true` and `data.api === "japspos-mobile"`.
3. Save it persistently. It is entered **once**.
4. Provide **Change server** on the login screen and in Settings. Changing it logs out, clears the token, saves the new URL and shows login again (a token is only valid on its own server).

---

## 2. Authentication

### POST `/api/v1/delivery/auth/login` (public)
| Field | Type | Required |
|---|---|---|
| `username` | string | yes |
| `password` | string | yes |
| `device_name` | string | no |

Access rules (same as the PWA): the user must be `active`, `allow_login = 1`, `user_type = user`, the business must be active, **and** the user must have `delivery.driver.view`, `delivery.driver.edit` or `delivery.manage`, or a role named `Delivery#…`. 10 failed attempts per minute returns **429**.

```json
{
  "success": true,
  "message": "Logged in.",
  "data": {
    "token": "15|OMSQxJ0FFe3UW5221n9ccBgJJ32ZFb6YTapTBH3n",
    "token_type": "Bearer",
    "expires_at": "2027-10-09T15:21:25-04:00",
    "user": {
      "id": 4, "username": "delivery", "surname": null, "first_name": "Delivery", "last_name": "Man",
      "full_name": "Delivery Man", "email": "delivery@gmail.com", "contact_number": null, "language": "en",
      "role": "Delivery",
      "permissions": { "driver_view": true, "driver_edit": true, "delivery_manage": false }
    },
    "business": {
      "id": 1, "name": "Japs POS", "logo": null, "time_zone": "America/New_York", "date_format": "m/d/Y", "time_format": "24",
      "currency_precision": 2, "quantity_precision": 2,
      "currency": { "id": 124, "code": "USD", "symbol": "$", "thousand_separator": ",", "decimal_separator": ".", "symbol_placement": "before" }
    }
  }
}
```
Errors: `401` wrong credentials · `403 This account is not allowed to use the Delivery app.` · `422` missing fields · `429`.

### Token usage
```
Authorization: Bearer {token}
Accept: application/json
```
Tokens last 365 days (`MOBILE_API_TOKEN_TTL_DAYS`) or until logout. The account is re-checked on every request. If it is blocked, the token is deleted and the request returns **403**. On **401/403** from any endpoint (except login), clear the token and go to login.

| Method | Path | Purpose |
|---|---|---|
| GET | `/delivery/auth/me` | User, permissions, business |
| POST | `/delivery/auth/logout` | Revoke this device |
| POST | `/delivery/auth/logout-all` | Revoke all devices of this driver (`{ "revoked": n }`) |

---

## 3. Conventions
- Success: `{ "success": true, "message": "...|null", "data": ... }`
- Error: `{ "success": false, "message": "...", "errors": { "field": ["..."] } | null }`

| HTTP | Meaning |
|---|---|
| 200 | OK |
| 401 | Token missing, invalid or expired |
| 403 | Not allowed / account blocked |
| 404 | Stop not found **or not assigned to this driver** |
| 422 | Validation or business rule (e.g. "This stop is already finished.") |
| 429 | Too many login attempts |

- "Today" is the **business time zone** date (`business.time_zone`).
- Timestamps in stop/route objects are ISO-8601 with an offset. Dates are `YYYY-MM-DD`.
- A driver only ever sees **their own** routes and stops in their own business.

---

## 4. Objects

### 4.1 Route summary
```json
{
  "id": 1,
  "route_date": "2026-10-09",
  "status": "started",
  "status_label": "In progress",
  "total_orders": 12,
  "delivered": 4,
  "failed": 1,
  "remaining": 7,
  "total_distance": 23954,
  "estimated_duration": 2876,
  "started_at": "2026-10-09T08:11:49-04:00",
  "completed_at": null,
  "can_start": false,
  "start": { "name": "Japs POS", "address": "New York, NY", "lat": 43.069, "lng": -76.172 }
}
```
- `status`: `planned` → `started` → `completed` (or `cancelled`). `can_start` = status is `planned`.
- `total_distance` is in meters and `estimated_duration` in seconds (from route optimisation).
- `remaining` = stops that are not delivered, failed or skipped.

### 4.2 Stop
```json
{
  "id": 4,
  "route_id": 1,
  "route_date": "2026-10-09",
  "stop_no": 1,
  "status": "pending",
  "status_label": "Pending",
  "transaction_id": 180,
  "invoice_no": "0107",
  "invoice_total": 269.5,
  "payment_status": "paid",
  "ship_to_name": "Tony & Prem Service Station",
  "ship_to_phone": "13474972046",
  "ship_to_address": "206-06 Jamaica Ave",
  "ship_to_city": "Queens Village",
  "ship_to_state": "NY",
  "ship_to_zip": "11428",
  "formatted_address": "206-06 Jamaica Ave\nQueens Village, NY, 11428",
  "latitude": 40.7171,
  "longitude": -73.7363,
  "navigate_url": "https://www.google.com/maps/dir/?api=1&destination=40.7171%2C-73.7363",
  "distance_from_previous": 1188,
  "estimated_duration_from_previous": 140,
  "arrived_at": null,
  "delivered_at": null,
  "fail_reason": null,
  "fail_reason_label": null,
  "delivery_notes": null,
  "can_act": true,
  "can_mark_arrived": true,
  "can_mark_delivered": true,
  "can_mark_failed": true
}
```
- `status`: `pending` · `en_route` · `arrived` · `delivered` · `failed` · `skipped`.
- `navigate_url`: open it in Google Maps (or use `latitude`/`longitude` with the platform maps app).
- `delivered_at` is also set when a stop **fails** (it is the time the stop was finished).
- Buttons: show **Arrived** when `can_mark_arrived`, **Delivered** when `can_mark_delivered` (a failed stop can be retried and marked delivered), and **Could not deliver** when `can_mark_failed`.
- `invoice_no`, `invoice_total` and `payment_status` are `null` in `/history` rows when the invoice was removed.

---

## 5. Endpoints

### GET `/delivery/dashboard`
```json
{
  "success": true,
  "data": {
    "date": "2026-10-09",
    "driver": { "id": 4, "name": "Delivery Man", "first_name": "Delivery" },
    "stats": { "today_stops": 12, "pending": 7, "delivered_today": 4, "failed_today": 1, "all_time_delivered": 312, "progress_percent": 33 },
    "route": { "...route summary..." },
    "next_stop": { "...stop..." }
  }
}
```
`route` is `null` when nothing is assigned today ("Dispatch will assign invoices to your route"). `next_stop` is the first unfinished stop by stop number. When it is `null` and `route` exists, the route is complete.

### GET `/delivery/route[?stop={stopId}]`
Today's route (planned or started) with every stop and map data. With `?stop=`, it opens that stop and its route (it can be another day's route of this driver).
```json
{
  "success": true,
  "message": null,
  "data": {
    "date": "2026-10-09",
    "route": { "...route summary..." },
    "active_stop": { "...stop..." },
    "suggested_stop_id": 4,
    "stops": [ { "...stop..." } ],
    "map": {
      "origin": { "name": "Japs POS", "address": "...", "lat": 43.069, "lng": -76.172 },
      "points": [ { "id": 4, "n": 1, "lat": 40.7171, "lng": -73.7363, "label": "#0107", "name": "Tony & Prem", "status": "pending" } ]
    }
  }
}
```
With no route: `message: "No route assigned for 2026-10-09."` and `route`, `active_stop` and `map` are null, `stops` is `[]`.
`active_stop` = the requested stop, otherwise the suggested (next) stop.
Map colours used by the PWA: start `S`, pending `#ffd000`, en_route/arrived `#f97316`, delivered `#22c55e`, failed `#ef4444`. Draw the polyline in `n` order starting from `origin`.

### POST `/delivery/route/start`
Starts today's route (`planned` → `started`, sets `started_at`). Returns the route summary with `message: "Route started."`.
`404 No route assigned for today.` · `422 This route was cancelled.` A completed route is left unchanged.
Arriving at, delivering or failing any stop also starts the route automatically.

### GET `/delivery/assigned?from=YYYY-MM-DD&to=YYYY-MM-DD`
Open stops (not delivered and not skipped; failed stops are included so they can be retried). Default range: today only.
`{ "from": "2026-10-09", "to": "2026-10-09", "count": 3, "stops": [ ...stop... ] }`

### GET `/delivery/all?from=&to=`
Every stop in the range grouped by route date. Default: the last 7 days.
```json
{ "data": { "from": "2026-10-02", "to": "2026-10-09", "total": 25, "open_count": 4, "delivered_count": 20,
  "days": [ { "date": "2026-10-09", "stops": [ { "...stop..." } ] } ] } }
```
If `from` is after `to`, they are swapped. An invalid date falls back to the default.

### GET `/delivery/history?date=YYYY-MM-DD` | `?all=1`
Delivered and failed stops, newest first, max 100. Default: today.
`{ "date": "2026-10-09", "all": false, "delivered_count": 10, "failed_count": 1, "stops": [ ...stop... ] }`

### GET `/delivery/fail-reasons`
```json
{ "data": [
  { "key": "customer_unavailable", "label": "Customer unavailable" },
  { "key": "wrong_address", "label": "Wrong address" },
  { "key": "customer_refused", "label": "Customer refused" },
  { "key": "unable_to_access", "label": "Unable to access location" },
  { "key": "other", "label": "Other" }
] }
```

### GET `/delivery/stops/{id}`
One stop, plus `order`:
```json
"order": {
  "id": 180, "invoice_no": "0107", "transaction_date": "2026-08-31 09:30:00", "final_total": 269.5,
  "payment_status": "paid", "shipping_status": "ordered", "shipping_details": "Delivery",
  "shipping_address": "...", "additional_notes": "...",
  "customer": { "id": 1, "name": "Customer", "business_name": null, "mobile": "012548963145" }
}
```

### POST `/delivery/stops/{id}/arrived`
No body. Status becomes `arrived`, `arrived_at` is set (first time only) and the invoice's `delivery_status` becomes `arrived`.
Returns the stop, `message: "Marked as arrived."` · `422 This stop is already finished.` for delivered/failed/skipped stops.

### POST `/delivery/stops/{id}/delivered`
Body (optional): `{ "notes": "Left with cashier" }` (max 5000).
Status becomes `delivered`, `delivered_at` = now, `fail_reason` is cleared, and the invoice gets `delivery_status = delivered` and `shipping_status = delivered`. When the last stop is finished the route completes automatically. Marking a stop on a completed route re-opens the route.
Allowed for `pending`, `en_route`, `arrived` and `failed` (retry). Calling it on an already delivered stop does nothing.
Returns the stop, `message: "Marked as delivered."` · `422 This stop cannot be marked delivered.` (skipped).

### POST `/delivery/stops/{id}/failed`
```json
{ "fail_reason": "customer_unavailable", "notes": "Store closed" }
```
`fail_reason` is required and must be one of the fail-reason keys. `notes` is optional and **replaces** the stop's notes.
Status becomes `failed` and the invoice's `delivery_status` becomes `failed`. If no open stops remain, the route completes.
Returns the stop, `message: "Marked as failed."` · `422` invalid reason / "This stop is already finished."

### POST `/delivery/stops/{id}/notes`
`{ "notes": "Gate code 1234" }`. Saves the delivery note (empty/null clears it). Returns the stop, `message: "Note saved."`

---

## 6. Suggested app flow
1. **Server URL** → `GET /ping`
2. **Login** → `POST /delivery/auth/login`
3. **Home** → `GET /delivery/dashboard` (Start route → `POST /delivery/route/start`)
4. **Route map** → `GET /delivery/route`. Tap a stop → `GET /delivery/route?stop={id}` or `GET /delivery/stops/{id}`
5. **Stop card**: Navigate (`navigate_url`) · Arrived · Delivered (+note) · Could not deliver (reason + note) · Save note
6. **Assigned**, **All** and **Delivered** tabs → `/assigned`, `/all`, `/history`
7. Refresh the dashboard/route after every action. Each action returns the updated stop.

## 7. Change log
- **1.0.0** (2026-10-10): first release. It matches the `/delivery` PWA (adds a stop-details endpoint and fail-reason list for native forms).
