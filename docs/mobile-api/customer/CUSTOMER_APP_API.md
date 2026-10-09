# JAPS POS: Customer App API (v1)

The REST API behind the native **Customer** app. It replaces the `/customer` PWA (webview) and uses the same server code: orders are created as Sales Orders (`source = customer_app`) that staff process in Admin, and invoices are the same documents Admin prints.

- Postman collection: `JAPS-POS-Customer-App.postman_collection.json` (same folder)
- All endpoints: `{BASE_URL}/api/v1/customer/...`

---

## 1. Base URL (one app, many client servers)

1. On first launch, ask for the **Server URL** (the store's address, e.g. `https://pos.storename.com`). Trim it, add `https://` if missing, and remove any trailing `/` and `/api…`. You can also ship a white-label build with the URL pre-filled.
2. Validate it with `GET {BASE_URL}/api/v1/ping` (public):
```json
{ "success": true, "message": "Server reachable.", "data": { "app_name": "Japs POS", "api": "japspos-mobile", "api_version": "1.0.0", "base_url": "https://pos.storename.com", "server_time": "2026-10-09T15:21:22-04:00", "apps": ["sale","delivery","customer"] } }
```
Accept it only when `success === true` and `data.api === "japspos-mobile"`.
3. Save it persistently. It is entered **once**.
4. Provide **Change store/server** on the login screen and in Settings. Changing it logs out, clears the token, saves the new URL and shows login again.

---

## 2. Authentication

### POST `/api/v1/customer/auth/login` (public)
| Field | Type | Required | Notes |
|---|---|---|---|
| `login` | string | yes | **Email or mobile**. For mobile, formatting is ignored (`(012) 548-963 145`, `+012548963145` and `012548963145` all match) |
| `password` | string | yes | |
| `device_name` | string | no | |

Access rules (same as the PWA): the contact must be a **customer** (type `customer` or `both`), have **Customer App access** enabled (`allow_customer_app = 1`, set in Admin → Contacts or when a sales rep creates the customer) and have a password. 10 failed attempts per minute returns **429**.

```json
{
  "success": true,
  "message": "Logged in.",
  "data": {
    "token": "21|nMZMlVu5H3EMpMrMUQRBWs9LCSjJsTYsHkfwgftt",
    "token_type": "Bearer",
    "customer": {
      "id": 1, "contact_id": "CO0001", "name": "Customer", "first_name": "Customer", "last_name": null,
      "business_name": null, "display_name": "Customer", "initials": "C",
      "email": "customer@example.com", "mobile": "012548963145", "type": "customer",
      "customer_group_id": null, "credit_limit": null,
      "address_line_1": null, "address_line_2": null, "city": null, "state": null, "zip_code": null, "country": null,
      "shipping_address": null
    },
    "business": {
      "id": 1, "name": "Japs POS", "logo": null, "time_zone": "America/New_York", "date_format": "m/d/Y", "time_format": "24",
      "currency_precision": 2, "quantity_precision": 2,
      "currency": { "id": 124, "code": "USD", "symbol": "$", "thousand_separator": ",", "decimal_separator": ".", "symbol_placement": "before" }
    }
  }
}
```
Errors: `401 Invalid email/mobile or password.` · `422` missing fields · `429` too many attempts.

### Token usage
```
Authorization: Bearer {token}
Accept: application/json
```
Tokens last 365 days (`MOBILE_API_TOKEN_TTL_DAYS`) or until logout. Access is re-checked on every request. If the store disables Customer App access, the token is deleted and the request returns **403 Customer app access is not enabled for this account.** On **401/403**, clear the token and show login.

| Method | Path | Purpose |
|---|---|---|
| GET | `/customer/auth/me` | `{ customer, business }` |
| POST | `/customer/auth/logout` | Revoke this device |
| POST | `/customer/auth/logout-all` | Revoke all of this customer's devices |

---

## 3. Conventions
- Success: `{ "success": true, "message": "...|null", "data": ... }`
- Error: `{ "success": false, "message": "...", "errors": { "field": ["..."] } | null }`

| HTTP | Meaning |
|---|---|
| 200 / 201 | OK / created |
| 401 | Token missing, invalid or expired |
| 403 | App access disabled |
| 404 | Not found or not this customer's record |
| 422 | Validation / business rule |
| 429 | Too many login attempts |

- Dates are `YYYY-MM-DD HH:mm:ss` in the store's time zone (`business.time_zone`).
- Money values are numbers. Format them with `business.currency`.
- The customer only ever sees **their own** orders and invoices.
- **Store location**: the customer's selected store (location) is saved **per device (token)**. Product prices and stock and new orders use it unless you pass `location_id`. The default is the first active location.

---

## 4. Endpoints

### GET `/customer/home`
```json
{
  "success": true,
  "data": {
    "customer": { "id": 1, "contact_id": "CO0001", "name": "Customer", "business_name": null, "initials": "C" },
    "business": { "...business object..." },
    "balance_due": 426.46,
    "current_location": { "id": 1, "name": "Japs POS", "state": "New York", "city": "New York", "landmark": "New York" },
    "locations": [ { "id": 1, "name": "Japs POS", "state": "New York", "city": "New York", "landmark": "New York" } ],
    "new_arrivals": [ { "product_id": 5600, "variation_id": 5600, "name": "New Item", "sku": "SKU1", "price": 9.99, "image": "https://.../img/default.png" } ],
    "top_products": [ { "product_id": 5541, "variation_id": 5541, "name": "Item", "sku": "SKU4625", "price": 20.99, "image": "..." } ]
  }
}
```
- `balance_due` = the customer's total outstanding balance (same figure as Admin → Contact).
- `new_arrivals`: the 8 newest sellable products. `top_products`: 8 products alphabetically (same as the PWA).

### GET `/customer/locations`
`{ "current_location_id": 1, "locations": [ { "id": 1, "label": "New York" } ] }`. The label is the location's **state** (or its name if the state is empty), sorted by state, as in the PWA.

### POST `/customer/locations/current`
`{ "location_id": 1 }` → `{ "current_location_id": 1 }`, `message: "Location updated: New York"`. `422 Invalid location.`

### GET `/customer/orders`
The last 50 documents: **sales orders** (any status) and **final invoices**.
```json
{ "data": { "orders": [
  { "id": 452, "invoice_no": "2026/0008", "type": "sales_order", "kind": "order", "status": "ordered", "transaction_date": "2026-10-09 15:25:34", "final_total": 20.99, "payment_status": null, "shipping_status": "ordered", "shipping_method": "Pickup", "location_id": 1, "source": "customer_app" },
  { "id": 447, "invoice_no": "0116", "type": "sell", "kind": "invoice", "status": "final", "transaction_date": "2026-09-29 14:51:50", "final_total": 13.99, "payment_status": "paid", "shipping_status": null, "shipping_method": null, "location_id": 1, "source": null }
] } }
```
`kind`: `order` (sales order) or `invoice` (final sale). `status` for orders: `ordered`, `partial`, `completed`.

### GET `/customer/orders/{id}`
```json
{
  "data": {
    "order": {
      "...order row...": "",
      "fulfillment": "pickup",
      "shipping_method": "Pickup",
      "shipping_details": "Pickup",
      "shipping_address": "",
      "sale_note": "Please call on arrival",
      "total_before_tax": 20.99, "tax_amount": 0, "discount_type": "fixed", "discount_amount": 0, "shipping_charges": 0
    },
    "lines": [
      { "name": "#16 BROWN BAGS 500/CS", "image": "https://.../img/default.png", "sku": "SKU4625", "size": "N/A", "upc": "SKU4625", "qty": 1, "unit_before": 20.99, "unit_final": 20.99, "line_total": 20.99, "discount": 0, "has_discount": false }
    ],
    "items_total": 20.99
  }
}
```
- `fulfillment`: `pickup`, `delivery` or `shipping`.
- `size` = variation name (`N/A` for single products). When `has_discount` is true, show `unit_before` struck through and `unit_final` as the price.

### GET `/customer/orders/create`
```json
{ "data": { "default_location_id": 1, "locations": [ { "id": 1, "label": "New York" } ], "shipping_address": "1 Main St", "fulfillment_options": [ "Pickup", "Delivery", "Shipping" ], "default_fulfillment": "Delivery" } }
```

### POST `/customer/orders`: place an order
```json
{
  "location_id": 1,
  "shipping_method": "Delivery",
  "shipping_details": "Delivery",
  "shipping_address": "1 Main St, Queens NY 11428",
  "sale_note": "Please call on arrival",
  "products": [
    { "product_id": 5541, "variation_id": 5541, "quantity": 2, "unit_price": 20.99 }
  ]
}
```
| Field | Rules |
|---|---|
| `location_id` | required, a location of this store |
| `products` | required, ≥ 1 |
| `products.*.product_id` / `variation_id` | required, must exist in this store |
| `products.*.quantity` | required, > 0 |
| `products.*.unit_price` | required, ≥ 0. Send the `price` returned by `/customer/products` |
| `shipping_method` | optional, max 100: `Pickup`, `Delivery` (default) or `Shipping` |
| `shipping_details` | optional, max 1000 (defaults to the method) |
| `shipping_address` | optional, max 1000 (defaults to the customer's shipping address) |
| `sale_note` | optional, max 500 |

The order is created as a **Sales Order** (`status: ordered`, `shipping_status: ordered`, `source: customer_app`) in the customer's group. Staff see it in Admin → Sales Orders, with the staff note "Ordered via Customer App by …".
Response `201`:
```json
{ "success": true, "message": "Order placed: 2026/0008", "data": { "order": { "...order row..." } } }
```
Errors: `422` validation · `422 Invalid location.` · `422 Could not place order. Please try again.`

### GET `/customer/documents[?start_date=&end_date=&q=]`
All **final invoices** (filters optional).
| Query | Notes |
|---|---|
| `start_date`, `end_date` | `YYYY-MM-DD` (either one may be used alone) |
| `q` | invoice number contains |

```json
{
  "data": {
    "filters": { "start_date": null, "end_date": null, "q": null },
    "summary": { "invoice_count": 12, "sales_total": 1828.56, "tax_total": 39.65, "paid_total": 1377.1 },
    "invoices": [ { "id": 447, "invoice_no": "0116", "transaction_date": "2026-09-29 14:51:50", "date": "2026-09-29", "final_total": 13.99, "tax_amount": 0, "total_paid": 13.99, "balance_due": 0, "payment_status": "paid" } ],
    "groups": [ { "date": "2026-09-29", "count": 2, "invoices": [ { "...invoice..." } ] } ]
  }
}
```
`summary` is the tax/statement summary for the filtered list. `groups` is the same list grouped by day (newest first).

### GET `/customer/documents/{id}/invoice[?format=html|json|pdf]`
The store's real invoice (US-Letter layout, same as Admin print). Only **final invoices** of this customer (an open sales order returns 404).

| `format` | Response |
|---|---|
| `html` (default) | `text/html`. Show it in a WebView (send the Bearer header) or print it |
| `json` | `{ "data": { "title": "0116", "html": "<!DOCTYPE html>..." } }` |
| `pdf` | `application/pdf` download for share/save |

### GET `/customer/products`: catalog / price check
| Query | Notes |
|---|---|
| `q` | name, SKU or sub-SKU contains |
| `location_id` | default: the current store location |
| `category_id` / `sub_category_id` | filters (sub-category wins) |
| `variation_id` | exact variation (barcode scan result) |
| `limit` | 1-80, default 20 |

```json
{ "data": [ { "product_id": 5541, "variation_id": 5541, "name": "#16 BROWN BAGS 500/CS", "sku": "SKU4625", "price": 20.99, "stock": 0, "image": "https://.../img/default.png" } ] }
```
Only active, sellable products. `name` includes the variation (`Shirt - Large`). `stock` = quantity at the location.

### GET `/customer/categories`
`[ { "id": 84, "name": "Beverages", "sub_categories": [ { "id": 110, "name": "Soda" } ] } ]`

### GET `/customer/profile`
```json
{ "data": {
  "customer": { "id": 1, "contact_id": "CO0001", "name": "Customer", "business_name": null, "email": "customer@example.com", "mobile": "012548963145", "initials": "C", "address_line_1": null, "address_line_2": null, "city": null, "state": null, "zip_code": null, "country": null, "shipping_address": null, "balance_due": 426.46 },
  "current_location_id": 1,
  "locations": [ { "id": 1, "label": "New York" } ]
} }
```

### POST `/customer/profile/password`
```json
{ "current_password": "12345678", "password": "newpass1", "password_confirmation": "newpass1" }
```
`password` must be at least 4 characters and confirmed. Response `message: "Password updated successfully."`
Errors: `422 Current password is incorrect.` · `422` validation (e.g. "The password confirmation does not match."). Other devices stay logged in. Call `/auth/logout-all` if the app should sign out everywhere.

---

## 5. Suggested app screens
| Screen | Endpoints |
|---|---|
| Server URL | `GET /ping` |
| Login | `POST /customer/auth/login` |
| Home | `GET /customer/home` |
| Shop / Price check | `GET /customer/categories`, `GET /customer/products` |
| Cart → Checkout | `GET /customer/orders/create` → `POST /customer/orders` |
| My orders | `GET /customer/orders` → `GET /customer/orders/{id}` |
| Documents | `GET /customer/documents` → `GET /customer/documents/{id}/invoice` |
| Profile | `GET /customer/profile`, `POST /customer/profile/password`, `POST /customer/locations/current`, logout |

## 6. Change log
- **1.0.0** (2026-10-10): first release. It matches the `/customer` PWA.
