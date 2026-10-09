# JAPS POS: Sales Rep App API (v1)

The REST API behind the native **Sales Representative** app. It replaces the `/sale` PWA (webview) and uses the same server code, so orders, prices, stock, permissions and invoices behave exactly as they do in the PWA and in Admin.

- Postman collection: `JAPS-POS-Sales-App.postman_collection.json` (same folder)
- All endpoints: `{BASE_URL}/api/v1/sale/...`

---

## 1. Base URL (one app, many client servers)

JAPS POS is installed separately for each client, so the app must not hard-code a server.

1. On first launch, show a **Server URL** screen, e.g. `https://pos.clientname.com`.
2. Normalize the input: trim it, add `https://` if no scheme is given, and remove any trailing `/` and any `/api...` suffix.
3. Validate it with `GET {BASE_URL}/api/v1/ping` (public). A JAPS POS server returns:

```json
{
  "success": true,
  "message": "Server reachable.",
  "data": {
    "app_name": "Japs POS",
    "api": "japspos-mobile",
    "api_version": "1.0.0",
    "base_url": "https://pos.clientname.com",
    "server_time": "2026-10-09T15:21:22-04:00",
    "apps": ["sale", "delivery", "customer"]
  }
}
```

Accept the URL only when `success === true` and `data.api === "japspos-mobile"`.

4. Save `BASE_URL` in secure/persistent storage. It is entered **once** and reused on every launch.
5. Put a **Change server** option on the login screen and in Settings. When the server changes, call logout (if logged in), delete the saved token, save the new URL and show login again. A token only works on the server that issued it.

---

## 2. Authentication

### Login
`POST /api/v1/sale/auth/login` (public)

| Field | Type | Required | Notes |
|---|---|---|---|
| `username` | string | yes | Staff username (same as the PWA) |
| `password` | string | yes | |
| `device_name` | string | no | e.g. "Samsung A54 – Android 14". Shown in token records |

Rules (same as the PWA):
- The user must be `active`, `allow_login = 1` and `user_type = user`.
- The business must be active.
- **Admins are refused** (403): this app is for sales reps only.
- The user needs at least one of: `so.create`, `so.view_own`, `so.view_all`, `sell.create`, `direct_sell.access`, `direct_sell.view`, `view_own_sell_only`.
- 10 failed attempts per minute (per username + IP) returns **429**.

Response `200`:
```json
{
  "success": true,
  "message": "Logged in.",
  "data": {
    "token": "12|bE6LUaNJLZpH96qbkMzpQXMyVzlZi3WwpA5Uravv",
    "token_type": "Bearer",
    "expires_at": "2027-10-09T15:21:24-04:00",
    "user": {
      "id": 3, "username": "sales", "surname": "Mr", "first_name": "Sales", "last_name": "Rep",
      "full_name": "Mr Sales Rep", "email": "sales@japspos.com", "contact_number": null,
      "language": "en", "role": "Sales Rep",
      "permissions": {
        "edit_order_price": false,
        "order_update": true,
        "order_delete": true,
        "customer_list": true,
        "customer_create": true,
        "expense_list": true,
        "expense_add": true,
        "stock_transfer_create": false,
        "attendance": true
      }
    },
    "business": {
      "id": 1, "name": "Japs POS", "logo": null, "time_zone": "America/New_York",
      "date_format": "m/d/Y", "time_format": "24", "currency_precision": 2, "quantity_precision": 2,
      "currency": { "id": 124, "code": "USD", "symbol": "$", "thousand_separator": ",", "decimal_separator": ".", "symbol_placement": "before" }
    }
  }
}
```
Errors: `401` wrong username/password · `403` not allowed (message explains why) · `422` missing fields · `429` too many attempts.

Use the `permissions` flags to show or hide features (price editing, edit/delete buttons, Customers, Expenses, Transfers).

### Using the token
Send on every request:
```
Authorization: Bearer {token}
Accept: application/json
```
- Tokens are valid for 365 days (`MOBILE_API_TOKEN_TTL_DAYS` in `.env`, 0 = never expires) or until logout.
- Each request re-checks the account. If the user is deactivated, the business is suspended or sales permission is removed, the server deletes the token and returns **403**. On **401 or 403 from any endpoint (except login)**, clear the token and go to the login screen.

| Method | Path | Purpose |
|---|---|---|
| GET | `/sale/auth/me` | User, permissions, business, current location |
| POST | `/sale/auth/logout` | Revoke this device's token |
| POST | `/sale/auth/logout-all` | Revoke all of this user's Sales-app tokens. Returns `{ "revoked": 3 }` |

---

## 3. Conventions

**Success envelope**
```json
{ "success": true, "message": "optional text to show", "data": { }, "meta": { } }
```
**Error envelope**
```json
{ "success": false, "message": "Human readable message", "errors": { "field": ["message"] } }
```

| HTTP | Meaning |
|---|---|
| 200 / 201 | OK / created |
| 401 | Missing, invalid or expired token: go to login |
| 403 | No permission (or account blocked: go to login) |
| 404 | Record not found or not yours |
| 405 | Wrong HTTP method |
| 422 | Validation or business-rule error: show `message`, map `errors` to fields |
| 429 | Too many login attempts |
| 500 | Server error (`message` is generic in production) |

**Pagination**: list endpoints return 20 rows per page. Pass `?page=N`. `meta` contains:
```json
{ "current_page": 1, "last_page": 3, "per_page": 20, "total": 47, "from": 1, "to": 20, "has_more": true }
```

**Dates**: `transaction_date` and similar fields are `YYYY-MM-DD HH:mm:ss` in the **business time zone** (`business.time_zone`). Time-clock fields are ISO-8601 with an offset.
**Money**: plain numbers. Format them with `business.currency` and `currency_precision`.
**Request bodies**: JSON (`Content-Type: application/json`) or form data. File upload (expenses) must be `multipart/form-data`.
**Current location**: the rep's selected business location is stored **per device (token)**, like the PWA session. Endpoints that need a location use it when you don't pass `location_id`.

---

## 4. Dashboard and account

### GET `/sale/dashboard`
Home screen: stats, recent orders, newest products and top sellers for the current location.
```json
{
  "success": true,
  "data": {
    "user_name": "Sales",
    "role_name": "Sales Rep",
    "current_location_id": 1,
    "current_location_name": "Japs POS (BL0001)",
    "current_location": { "id": 1, "location_id": "BL0001", "name": "Japs POS", "landmark": "New York", "city": "New York", "state": "New York", "zip_code": "11011", "country": "USA", "mobile": null, "email": null, "latitude": 43.069, "longitude": -76.172 },
    "locations": [ { "id": 1, "name": "Japs POS (BL0001)" } ],
    "stats": {
      "total_orders": 25, "today_orders": 2, "today_total": 120.5, "today_cash_total": 80,
      "today_credit_total": 40.5, "today_credit_orders": 1, "month_orders": 14, "month_total": 2210.4,
      "paid_orders": 6, "due_orders": 19
    },
    "recent_orders": [ "...order row (see 5.1)..." ],
    "new_products": [ { "id": 5541, "variation_id": 5541, "name": "Item", "sku": "SKU4625", "price": 20.99, "image": "https://.../img/default.png", "has_image": false, "in_stock": true } ],
    "top_products": [ { "id": 4266, "variation_id": 4266, "name": "Item", "sku": "0795", "price": 17, "qty_sold": 54, "sales_amount": 918, "image": "...", "has_image": true, "in_stock": true } ]
  }
}
```
Totals use the converted invoice amount once an order has been invoiced.

### GET `/sale/account`
`data.user` {id, username, first_name, last_name, email, contact_number}, `data.locations` [{id, name}], `data.current_location_id`.

### GET `/sale/locations`
`{ current_location_id, current_location: {...}, locations: [{id, name}] }`. Only the locations this user is permitted to use.

### POST `/sale/locations/current`
Body `{ "location_id": 2 }`. Changes the current location for this device. Response is the same as GET `/sale/locations`, plus `message: "Location updated: …"`. `422 Invalid location.` if the location isn't permitted.

### POST `/sale/sync`
Clears the server catalog cache. `data.synced_at` (ISO). Call it on pull-to-refresh of the catalog.

---

## 5. Orders

An order is a **Sales Order** (`type=sales_order`, `status=ordered`, `source=sale_app`), the same record Admin sees under Sales Orders. **Estimates** are quotations (`type=sell`, `is_quotation=1`). **Back orders** are sales orders with `staff_note = back_order`. No stock is deducted until Admin converts the order to an invoice.

### 5.1 Order row (used in lists)
```json
{
  "id": 448,
  "invoice_no": "2026/0007",
  "doc_type": "sales_order",
  "type": "sales_order",
  "status": "ordered",
  "sale_status": "sale",
  "sale_status_label": "Sale",
  "is_back_order": false,
  "transaction_date": "2026-10-09 15:21:57",
  "final_total": 41.98,
  "display_total": 41.98,
  "payment_status": "due",
  "prefer_payment_method": "credit_sale",
  "is_credit_sale": true,
  "terms": "Credit",
  "converted_invoice_no": null,
  "shipping_status": "ordered",
  "shipping_method": "Delivery",
  "location_id": 1,
  "can_edit": true,
  "can_show_edit": true,
  "can_delete": true,
  "customer": { "id": 1390, "name": "Tony", "business_name": "Tony & Prem Service Station", "display_name": "Tony & Prem Service Station", "mobile": "13474972046" }
}
```
- `doc_type`: `sales_order` or `estimate`.
- `sale_status`: `sale` (open) · `back_order` · `invoiced` (Admin converted it) · `estimate`.
- `display_total`: invoice total after conversion (Admin may change qty or price), otherwise the order total.
- `can_show_edit`: show the Edit button. `can_edit`: editing is allowed now (false once invoiced). `can_delete`: show Delete.
- `terms`: `Cash` or `Credit` (`payment_type` cash / due).

### 5.2 GET `/sale/orders`: report and lists
| Query | Values | Default |
|---|---|---|
| `section` | `orders`, `invoices`, `payments`, `visits`, `day`, `quotes`, `credit` | none (report only) |
| `period` | `daily`, `weekly`, `monthly` | `monthly` |
| `month` | `YYYY-MM` (monthly) | current month |
| `day` | `YYYY-MM-DD` (daily, or the anchor for weekly) | today |
| `status` | `sale`, `invoiced`, `estimate`, `orders`, `payments`, `credit`. **Without** `section` it searches all dates | none |
| `q` | invoice no / converted invoice no / customer name / mobile / business name | none |
| `contact_id` | one customer | none |
| `page` | page number | 1 |

Response (always contains the report blocks):
```json
{
  "success": true,
  "data": {
    "section": "orders",
    "list_title": "Orders",
    "period": "monthly",
    "period_label": "Oct 2026",
    "month": "2026-10",
    "day": "2026-10-09",
    "q": null, "status": null, "contact_id": null,
    "transactions": { "quotes": 0, "quotes_total": 0, "orders": 3, "orders_total": 150.5, "invoices": 1, "invoices_total": 60, "credit": 1, "credit_total": 40.5, "payments": 2, "payments_total": 110 },
    "goals": { "orders_current": 150.5, "invoices_current": 60, "payments_current": 110, "visits_current": 3, "orders_target": 1000, "invoices_target": 1000, "payments_target": 1000, "visits_target": 10 },
    "report_stats": { "orders": 25, "orders_total": 3100, "quotes": 2, "quotes_total": 90, "invoiced": 6, "credit": 9, "credit_total": 1200, "cash": 16, "cash_total": 1900 },
    "day_report": { "date": "Friday, Oct 9, 2026", "start": "9:12 AM", "end": "3:21 PM", "orders": 3, "orders_total": 150.5, "cash": 110, "credit": 40.5, "invoices": 1, "invoices_total": 60, "visits": 3 },
    "orders": [ { "...order row...": "", "display_no": "2026/0007" } ]
  },
  "meta": { "current_page": 1, "last_page": 1, "per_page": 20, "total": 3, "from": 1, "to": 3, "has_more": false }
}
```
- **Goals**: the target is the previous period's total. If the previous period was 0, the target is current × 1.2, or else a default (1000 money / 10 visits).
- `display_no`: in `section=invoices` this is the converted invoice number.
- `section=visits` returns `visit_customers` (paginated) instead of `orders`:
  `[{ "id", "name", "business_name", "display_name", "mobile", "visit_orders", "visit_total" }]`.
- Without `section` and `status`, `orders` is empty: the PWA shows the report screen in that case.

### 5.3 GET `/sale/orders/{id}`: order details
Only the rep's own orders and estimates (otherwise 404). After Admin converts the order, the lines, totals and payments come from the **invoice** (snapshot), like the PWA.
```json
{
  "success": true,
  "data": {
    "...order row fields...": "",
    "shipping_details": "Delivery",
    "shipping_address": "206-06 Jamaica Ave, Queens Village NY 11428",
    "sale_note": "Leave at back door",
    "customer": { "id": 1390, "contact_id": "113147680", "name": "...", "business_name": "...", "display_name": "...", "mobile": "...", "email": null },
    "amounts": {
      "subtotal": 41.98, "discount": 0, "line_discount": 0, "discount_label": "Discount",
      "tax": 0, "shipping": 0, "packing": 0, "packing_label": "Packing charge", "round_off": 0,
      "extras": [ { "label": "Fuel surcharge", "amount": 2 } ],
      "total": 41.98, "paid": 0, "due": 41.98, "show_paid": false
    },
    "items": [
      { "product_id": 5541, "variation_id": 5541, "name": "#16 BROWN BAGS 500/CS", "variation_name": null, "quantity_text": "1 Box + 2 Pieces  =  14 Pc", "total": 41.98, "list_total": 41.98, "discount": 0, "discount_percent": 0 }
    ],
    "lines": [
      { "id": 194, "product_id": 5541, "variation_id": 5541, "product_name": "#16 BROWN BAGS 500/CS", "variation_name": null, "sku": "SKU4625", "image": "https://.../img/default.png", "sub_unit_id": null, "unit_name": "Pieces", "unit_multiplier": 1, "quantity": 2, "quantity_base": 2, "unit_price": 20.99, "list_price": 20.99, "unit_price_base": 20.99, "line_total": 41.98, "line_list_total": 41.98, "line_discount": 0, "sell_line_note": "" }
    ],
    "payments": [ { "id": 10, "amount": 20, "method": "cash", "paid_on": "2026-10-09 16:00:00", "payment_ref_no": "SP2026/0001", "is_return": false, "note": null } ]
  }
}
```
- `items`: lines grouped per variation, as the PWA displays them (mixed units in `quantity_text`).
- `lines`: raw lines. `quantity` and `unit_price` are in the selected unit. `quantity_base` and `unit_price_base` are in the base unit.
- Show `paid` and `due` only when `amounts.show_paid` is true (invoiced).

### 5.4 GET `/sale/orders/{id}/invoice`: printable invoice
The same US-Letter invoice as Admin → Sales Order → Print (the converted invoice once invoiced, keeping the SO number).

| `format` | Response |
|---|---|
| `html` (default) | `text/html` page. Load it in a WebView or print it |
| `json` | `{ "data": { "title": "2026/0007", "html": "<!DOCTYPE html>..." } }` |
| `pdf` | `application/pdf` download (`2026_0007.pdf`) |

Send the Bearer token with this request (load the HTML with headers or fetch it first). `422 Could not open invoice.` on failure.

### 5.5 GET `/sale/orders/create?contact_id={id}[&saved={savedId}]`: order screen data
`contact_id` is required (the PWA always starts from a customer).
```json
{
  "data": {
    "can_edit_price": false,
    "payment_types": [ { "key": "cash", "label": "Cash" }, { "key": "due", "label": "Credit (due)" } ],
    "order_modes": [ { "key": "new_order", "label": "New order" }, { "key": "estimate", "label": "Estimate" }, { "key": "back_order", "label": "Back order" } ],
    "fulfillment_options": [ "Pickup", "Delivery", "Shipping" ],
    "shipping_statuses": [ "ordered", "packed", "shipped", "delivered", "cancelled" ],
    "default_location_id": 1,
    "locations": [ { "id": 1, "name": "Japs POS (BL0001)" } ],
    "customer": { "id": 1390, "text": "Biz — Name (mobile)", "display_name": "Biz", "shipping_address": "...", "address": "...", "credit_limit": 0, "acct": "113147680" },
    "saved_draft": null
  }
}
```
`saved_draft` is the customer's saved cart (or the one named by `saved`). Pre-fill the cart from it (see section 6).

### 5.6 POST `/sale/orders`: create order, estimate or back order
```json
{
  "contact_id": 1390,
  "location_id": 1,
  "order_mode": "new_order",
  "payment_type": "cash",
  "shipping_method": "Delivery",
  "shipping_details": "Delivery",
  "shipping_address": "206-06 Jamaica Ave, Queens Village NY 11428",
  "shipping_status": "ordered",
  "sale_note": "Leave at back door",
  "products": [
    { "product_id": 5541, "variation_id": 5541, "quantity": 2, "unit_price": 20.99, "sub_unit_id": 1 }
  ]
}
```
| Field | Rules |
|---|---|
| `contact_id` | required, customer of this business |
| `location_id` | required, permitted location |
| `products` | required, ≥ 1 |
| `products.*.quantity` | > 0, in the **selected unit** |
| `products.*.unit_price` | ≥ 0, price of the **selected unit** (base price × unit multiplier) |
| `products.*.sub_unit_id` | optional unit id from the product's `units` list (default: base unit) |
| `payment_type` | required: `cash` or `due` (credit) |
| `shipping_details` | required, max 1000. The PWA sends the fulfillment (`Pickup`/`Delivery`/`Shipping`), optionally followed by `": details"` |
| `shipping_address` | required, max 1000. Use `Customer Pickup` for pickup orders |
| `shipping_method` | optional: `Pickup`, `Delivery`, `Shipping` |
| `shipping_status` | optional: ordered · packed · shipped · delivered · cancelled (default ordered) |
| `order_mode` | optional: `new_order` (default), `estimate`, `back_order` |
| `sale_note` | optional, max 500 |

**Price rule**: when the user does **not** have `permissions.edit_order_price`, the server ignores `unit_price` and uses the catalog price for the selected unit. With the permission, a lower price is stored as a line discount against the list price; the product's master price is never changed.

Response `201`: full order details (5.3), `message: "Sales order created: 2026/0007"` (or "Estimate created: …" / "Back order created: …"). The customer's saved cart is removed.
Errors: `422` validation (`errors`), `422 Invalid location.`, `404` unknown customer, `422 Could not create order. …`.

### 5.7 GET `/sale/orders/{id}/edit`
`403` if the order is invoiced ("This order is invoiced and cannot be edited.") or the user lacks `so.update`/`so.update_own`. Estimates can't be edited.
```json
{
  "data": {
    "...same option lists as 5.5...": "",
    "order": { "...order row..." },
    "default_location_id": 1,
    "locations": [ ],
    "customer": { },
    "payment_type": "due",
    "shipping_method": "Delivery",
    "shipping_details": "Delivery",
    "shipping_address": "...",
    "shipping_status": "ordered",
    "sale_note": "...",
    "lines": [
      { "product_id": 5541, "variation_id": 5541, "transaction_sell_lines_id": 194, "name": "Item", "price": 20.99, "base_price": 20.99, "catalog_base_price": 20.99, "quantity": 2, "enable_stock": 0, "product_type": "single", "unit_id": 1, "unit_name": "Pieces", "sub_unit_id": 1, "allow_decimal": 0, "units": [ { "id": 1, "name": "Pieces", "multiplier": 1, "allow_decimal": 0 } ] }
    ]
  }
}
```

### 5.8 PUT `/sale/orders/{id}`: update order
Same body as 5.6 except `order_mode` (not used). Add `transaction_sell_lines_id` for lines that already exist. **Lines you leave out are deleted.** Response: order details (5.3), `message: "Sales order updated: 2026/0007"`. The activity log records which items were removed.

### 5.9 DELETE `/sale/orders/{id}`
Needs `so.delete`, or `so.delete_own` on your own order. Estimates return 403. Response `{ "message": "Sale deleted successfully" }`.

---

## 6. Saved carts (drafts)
One saved cart per customer, stored on the server (shared with the PWA for the same user).

| Method | Path | Body / query | Response |
|---|---|---|---|
| GET | `/sale/saved-orders` | none | `{ saved_orders: [ { id, contact_id, contact_name, payment_type, shipping_address, shipping_details, cart: [...], total, saved_at } ] }` |
| POST | `/sale/order-draft` | `{ contact_id (req), contact_name, payment_type, shipping_address, shipping_details, cart: [ {product_id, variation_id, quantity, unit_price, sub_unit_id, ...} ] (req, ≥1) }` | `{ id }` |
| DELETE | `/sale/order-draft?contact_id=1390` | none | Clears that customer's cart |
| DELETE | `/sale/saved-orders/{savedId}` | none | Removes one saved cart |

`cart` items are stored exactly as sent (keep the fields your cart UI needs). `total` = Σ quantity × unit_price. Creating or updating an order for the customer clears the cart.

---

## 7. Catalog

### GET `/sale/catalog`
`{ default_location_id, locations: [{id,name}], categories: [ {id, name, sub_categories: [{id,name}]} ] }`

### GET `/sale/products`: product search
| Query | Notes |
|---|---|
| `q` | name, SKU, sub-SKU or variation name (contains) |
| `location_id` | stock location (defaults to the current location) |
| `category_id` / `sub_category_id` | filters (sub-category wins) |
| `variation_id` | exact variation (barcode/scan result) |
| `limit` | 1-100, default 30 |

```json
{
  "data": [
    {
      "product_id": 5541, "variation_id": 5541,
      "name": "#16 BROWN BAGS 500/CS (SKU4625)", "sku": "SKU4625",
      "price": 20.99, "base_price": 20.99, "stock": 0, "enable_stock": 0,
      "product_type": "single", "unit_id": 1, "unit_name": "Pieces", "allow_decimal": 0,
      "units": [ { "id": 1, "name": "Pieces", "multiplier": 1, "allow_decimal": 0 }, { "id": 7, "name": "Box", "multiplier": 12, "allow_decimal": 0 } ],
      "tax_id": null, "category_id": 84, "sub_category_id": 0, "category_name": "Beverages",
      "image": "https://.../uploads/img/1.jpg", "has_image": true
    }
  ]
}
```
- Only active, sellable products (not modifiers, not "not for selling").
- `stock` is the quantity at the location. Only enforce it when `enable_stock = 1` (sales orders don't deduct stock).
- **Units**: price for a unit = `base_price × multiplier`. Send `sub_unit_id` = the unit id, and `quantity` / `unit_price` in that unit. Respect `allow_decimal` for quantity input.

### GET `/sale/categories`
Category tree `[ { id, name, sub_categories: [ {id, name} ] } ]`.

### GET `/sale/last-purchases?contact_id={id}[&location_id=]`
The customer's recent items (last 40 orders/invoices), one row per variation, with the last unit, quantity, price and date. Used for "Order again":
```json
{ "data": [ { "product_id": 5541, "variation_id": 5541, "name": "Item (SKU4625)", "sku": "SKU4625", "price": 20.99, "base_price": 20.99, "quantity": 3, "last_qty": 3, "last_price": 20.99, "last_date": "10/09/2026", "stock": 0, "enable_stock": 0, "product_type": "single", "unit_id": 1, "unit_name": "Pieces", "sub_unit_id": 1, "allow_decimal": 0, "units": [ ] } ] }
```

### GET `/sale/product-history?contact_id={id}&variation_id={id}`
The last 20 order lines of this product for the customer:
`[ { invoice_no, date: "10/09/2026", date_short: "Oct 9", quantity, unit_price, line_total, unit_name } ]`

---

## 8. Customers
Listing and viewing need `customer.view` or `customer.view_own`. Creating needs `customer.create`.

### GET `/sale/customers?q=&page=`
```json
{
  "data": {
    "can_list": true, "can_create": true, "q": null,
    "customers": [ { "id": 1390, "contact_id": "113147680", "name": "...", "business_name": "...", "display_name": "...", "mobile": "...", "email": null, "address_line_1": "...", "city": "...", "shipping_address": "...", "credit_limit": 0, "last_order_total": 51.96, "created_at": "2026-09-01 10:00:00" } ],
    "zero_sale_reasons": [ { "id": 1, "name": "call back" } ]
  },
  "meta": { "...pagination..." }
}
```
Create-only users get `can_list: false` and an empty list (show the add form). No permission at all returns `403`.

### GET `/sale/customers/search?q=`
Quick picker, max 40 active customers. Matches name, mobile, email, business, account no, address and city.
`[ { id, text, display_name, name, mobile, address, initials, shipping_address, credit_limit, acct } ]`

### GET `/sale/customers/create`
`{ can_list, customer_groups: [ {id: 0, name: "None"}, {id, name} ], contact_types: ["individual","business"] }`

### POST `/sale/customers`
| Field | Rules |
|---|---|
| `first_name` | required |
| `last_name`, `supplier_business_name` | optional |
| `contact_type_radio` | `individual` (default) / `business` |
| `contact_id` | optional account no (auto-generated when empty) |
| `customer_group_id` | optional (0 / null = none) |
| `mobile`, `alternate_number`, `landline`, `email` (valid email), `tax_number` | optional |
| `address_line_1`, `address_line_2`, `city`, `state`, `zip_code`, `country`, `shipping_address` | optional |
| `credit_limit` | optional number |
| `allow_customer_app` | `0`/`1`. `1` enables Customer-app login |
| `customer_app_password` | password for the Customer app (if empty, the mobile number is used) |

Response `201`: the customer detail object (below), `message: "Customer added: John Doe"`.

### GET `/sale/customers/{id}`: customer profile
```json
{
  "data": {
    "customer": {
      "id": 1390, "contact_id": "113147680", "type": "customer", "contact_type": "business",
      "name": "...", "prefix": null, "first_name": "...", "middle_name": null, "last_name": null,
      "business_name": "...", "display_name": "...", "email": null, "mobile": "...", "alternate_number": null,
      "landline": null, "tax_number": "...", "address_line_1": "...", "address_line_2": null, "city": null,
      "state": null, "country": null, "zip_code": null, "shipping_address": "...", "address": "one-line address",
      "customer_group_id": null, "credit_limit": 0, "pay_term_number": null, "pay_term_type": null, "dob": null,
      "contact_status": "active", "custom_field1": null, "custom_field2": null, "custom_field3": null, "custom_field4": null,
      "allow_customer_app": false, "created_at": "2026-09-01 10:00:00"
    },
    "group_name": null,
    "address": "206-06 Jamaica Ave ...",
    "aging": { "current": 120, "d30": 0, "d60": 0, "d90": 50 },
    "open_balance": 170,
    "open_invoices": [ { "id": 440, "kind": "Invoice", "name": "...", "invoice_no": "0107", "date": "09/16/2026", "due_date": "09/20/2026", "amount": 269.5, "balance": 120, "status": "Sent", "acct": "113147680" } ],
    "history": [ { "id": 440, "kind": "Invoice", "name": "...", "invoice_no": "0107", "date": "09/16/2026", "amount": 269.5, "balance": 120, "status": "Sent", "is_own_order": true } ],
    "zero_sale_reasons": [ ]
  }
}
```
- `aging` buckets are by invoice age in days: `current` < 30, `d30` 30-59, `d60` 60-89, `d90` ≥ 90.
- `history` includes final invoices (`kind: Invoice`, with `balance`) and sales orders (`kind: Order`, `balance: null`). `is_own_order` = this rep created it, so open `/sale/orders/{id}`. Otherwise use the print endpoint.

### GET `/sale/customers/{id}/invoices?q=`
`{ customer, q, aging, open_balance, open_invoices }`. Open invoices only, filtered by invoice no / name.

### GET `/sale/customers/{id}/history?q=`
`{ customer, q, history }`

### GET `/sale/customers/{id}/invoices/{invoiceId}/print?format=html|json|pdf`
Prints any **final invoice** or **sales order** of that customer (not only the rep's own). The format options are the same as 5.4.

### GET `/sale/customers/{id}/payment[?invoice_id=]`
```json
{ "data": { "customer": { }, "payment_methods": [ { "key": "cash", "label": "Cash" }, { "key": "card", "label": "Card" } ], "preselect_invoice_id": null, "aging": { }, "open_balance": 170, "open_invoices": [ ] } }
```
`payment_methods` are only the methods enabled for the **current location**.

### POST `/sale/customers/{id}/payment`
| Field | Rules |
|---|---|
| `amount` | required, > 0 (`"1,250.00"` and `"$50"` are accepted) |
| `method` | required, must be one of `payment_methods[].key` |
| `paid_on` | optional `YYYY-MM-DD HH:mm:ss` (default now) |
| `note` | optional, max 500 |
| `pay_mode` | `pay` (default) or `credit` (reverse/credit entry) |
| `signature` | optional PNG data URL `data:image/png;base64,...` (customer signature) |

The amount is applied to the customer's due invoices, oldest first (same as Admin "Pay contact due"). Any excess becomes advance credit.
Response: `{ open_balance, aging, open_invoices }` (refreshed), `message: "Payment saved."`
Errors: `422 This payment method is not active for your location.` · `422 Enter a payment amount.`

### POST `/sale/customers/{id}/zero-sale`
Logs a visit without an order. Body `{ "reason_id": 1, "comment": "optional, max 2000" }`. The current location is stored with it. `201 Zero sale saved.`

### GET `/sale/zero-sale-reasons`
`[ { id, name } ]` (active reasons, managed in Admin).

---

## 9. Expenses
| Method | Path | Permission |
|---|---|---|
| GET | `/sale/expenses?page=` | `view_own_expense` (own) or `all_expense.access` (all) |
| GET | `/sale/expenses/create` | `expense.add` |
| POST | `/sale/expenses` | `expense.add` |

**GET `/sale/expenses`**: if the user can only add expenses, the response is `{ can_list: false, can_add: true, expenses: [] }`.
```json
{ "data": { "can_list": true, "can_add": true, "expenses": [ { "id": 451, "type": "expense", "ref_no": "EP2026/0003", "transaction_date": "2026-10-09 10:30:00", "final_total": 12.5, "payment_status": "paid", "additional_notes": "Fuel", "category": { "id": 1, "name": "Fuel" }, "location": { "id": 1, "name": "Japs POS" }, "contact": null, "document": "https://.../uploads/documents/1791573868_receipt.pdf" } ] }, "meta": { } }
```

**GET `/sale/expenses/create`**
```json
{ "data": { "current_location_id": 1, "locations": [ {"id":1,"name":"Japs POS (BL0001)"} ], "categories": [ {"id":1,"name":"Fuel"} ], "sub_categories": [ { "parent_id": 1, "items": [ {"id": 5, "name": "Diesel"} ] } ], "payment_methods": [ {"key":"cash","label":"Cash"} ], "payment_methods_by_location": [ { "location_id": 1, "methods": [ {"key":"cash","label":"Cash"} ] } ], "document_max_kb": 5000 } }
```
When the user changes location, show that location's `payment_methods_by_location`.

**POST `/sale/expenses`** (`multipart/form-data`, or JSON when there is no file)
| Field | Rules |
|---|---|
| `location_id` | required, permitted |
| `final_total` | required, ≥ 0.01 |
| `transaction_date` | required: `YYYY-MM-DD` or `YYYY-MM-DD HH:mm` |
| `expense_category_id`, `expense_sub_category_id` | optional |
| `contact_id` | optional customer id |
| `payment_method` | optional. Must be active for the location, otherwise the first active one is used |
| `ref_no` | optional (auto-generated when empty) |
| `additional_notes` | optional, max 1000 |
| `document` | optional file: pdf, csv, zip, doc, docx, jpeg, jpg, png (max 5 MB) |

The expense is created **paid**, with the expense set "for" the logged-in user. Response `201`: `{ id, ref_no, transaction_date, final_total, location_id, payment_status }`. An invalid file type returns `422 Something went wrong, please try again later`.

---

## 10. Delivery (rep's own orders)
### GET `/sale/delivery?start_date=&end_date=&q=`
The rep's own sales orders (default: the last 30 days, max 200), ordered as one route from the current POS location: same city first, then the nearest ZIP, then the street.
```json
{ "data": { "start_date": "2026-09-09", "end_date": "2026-10-09", "q": null, "origin_name": "Japs POS", "origin_city": "New York",
  "routes": [ { "label": "Japs POS", "count": 1, "total": 51.96, "orders": [ { "stop_no": 1, "id": 440, "invoice_no": "2026/0005", "transaction_date": "2026-09-17 14:29:11", "final_total": 51.96, "shipping_status": "ordered", "shipping_address": "...", "customer": { "id": 1390, "name": "...", "business_name": "...", "mobile": "...", "address_line_1": "...", "city": "...", "state": "...", "zip_code": "..." } } ] } ] } }
```

---

## 11. Timesheet (time clock)
This is the POS time clock (Admin → Attendance). Sales-app users can always clock in and out.

| Method | Path | Body |
|---|---|---|
| GET | `/sale/timesheet` | none. Status + last 14 entries |
| GET | `/sale/timesheet/status` | none |
| POST | `/sale/timesheet/clock-in` | `{ latitude, longitude, location_id?, notes? }` |
| POST | `/sale/timesheet/start-break` | `{ reason? }` |
| POST | `/sale/timesheet/end-break` | none |
| POST | `/sale/timesheet/clock-out` | `{ latitude, longitude, location_id? }` |

Status object (the `data` of every clock action):
```json
{ "state": "clocked_in", "clock_in_at": "2026-10-09T09:00:00-04:00", "break_start_at": null, "worked_seconds": 3600, "break_seconds": 0, "worked_label": "01:00:00", "break_label": "00:00:00" }
```
`state`: `clocked_out` · `clocked_in` · `on_break`.
**Geofence**: when the location (the current one, or `location_id`) has coordinates, `latitude`/`longitude` are required and must be within the location radius (default 200 m), unless the user has `attendance.clock_offsite`. A refused action returns `422` with `message` (e.g. "You are already clocked in", or the outside-geofence message) and `data` = the current status.

`/sale/timesheet` entries: `[ { id, status, clock_in_at, clock_out_at, worked_seconds, break_seconds, notes, breaks: [ { id, break_start_at, break_end_at, reason } ] } ]`

---

## 12. Stock transfers
| Method | Path | Notes |
|---|---|---|
| GET | `/sale/transfers?page=` | Transfers created by this user |
| GET | `/sale/transfers/create` | `{ can_create, locations, statuses: [pending, in_transit, completed] }` |
| GET | `/sale/transfers/products?location_id=&q=&limit=` | Stock-managed products at the source location, with the transfer price (purchase price inc. tax) and stock |
| POST | `/sale/transfers` | Needs `stock_transfer.create` |
| GET | `/sale/transfers/{id}` | Details + lines |

Transfer row:
```json
{ "id": 30, "ref_no": "ST2026/0004", "transaction_date": "2026-10-09 10:00:00", "status": "pending", "status_label": "Pending", "final_total": 85, "additional_notes": null, "from_location": { "id": 1, "name": "Main" }, "to_location": { "id": 2, "name": "Branch" }, "receive_status": "pending" }
```
Details add `shipping_charges` and `lines: [ { id, product_id, variation_id, product_name, variation_name, sku, quantity, unit_name, quantity_in_unit, unit_price, line_total } ]`.

**POST `/sale/transfers`**
```json
{
  "location_id": 1,
  "transfer_location_id": 2,
  "transaction_date": "2026-10-09 10:00",
  "ref_no": null,
  "status": "pending",
  "shipping_charges": 0,
  "additional_notes": "",
  "products": [ { "product_id": 4266, "variation_id": 4266, "quantity": 5, "unit_price": null, "sub_unit_id": null } ]
}
```
- `status`: `pending`, `in_transit` or `completed`. **Completed moves the stock immediately.**
- `unit_price` left empty = purchase price × unit multiplier. `final_total` is calculated by the server (lines + shipping).
- The source and destination must be different permitted locations.
Response `201`: transfer row. `403` without permission.

---

## 13. Suggested app screens

| Screen | Endpoints |
|---|---|
| Server URL | `GET /ping` |
| Login | `POST /sale/auth/login` |
| Home | `GET /sale/dashboard` |
| Customers → customer | `/sale/customers`, `/sale/customers/{id}`, `/payment`, `/zero-sale` |
| New order | `/sale/orders/create` → `/sale/products`, `/sale/last-purchases`, `/sale/product-history`, `/sale/order-draft` → `POST /sale/orders` |
| Orders / Reports | `/sale/orders?section=…` → `/sale/orders/{id}` → `/invoice` |
| Catalog | `/sale/catalog`, `/sale/products` |
| More | expenses, delivery, timesheet, transfers, account/location, logout |

## 14. Change log
- **1.0.0** (2026-10-10): first release. It matches the `/sale` PWA feature by feature.
