# EcomApp - System Plan

> Multi-purpose e-commerce platform with food delivery and a product marketplace (TikTok Shop style).
> Supports restaurants (food delivery via drivers) and product shops (seller shipping or platform driver delivery).
> This document covers the full technical plan: features, database schema, system flows, folder structure, routes, and testing strategy.

## Tech Stack

| Layer          | Technology                                        |
| -------------- | ------------------------------------------------- |
| Backend        | Laravel 12, PHP 8.5                               |
| Authentication | Laravel Fortify                                   |
| Frontend       | Blade + Livewire + Tailwind CSS v4                |
| Real-time      | Laravel Broadcasting (Reverb) + Echo              |
| Database       | SQLite (MVP)                                      |
| Testing        | Pest v4                                           |
| Payment        | Polar.sh (digital) + Cash on Delivery (COD)       |
| Payment SDK    | `danestves/laravel-polar` (Laravel Polar adapter) |

---

## User Roles

Roles are stored as a `UserRole` enum column on the `users` table.

| Role                | Description                                                                                      |
| ------------------- | ------------------------------------------------------------------------------------------------ |
| **Admin**           | Manages all stores, users, drivers, sellers, and orders. Full dashboard access.                  |
| **User** (Customer) | Browses stores, places orders, tracks delivery/shipping, leaves reviews.                         |
| **Seller**          | Owns and manages shops, creates products, fulfills product orders, views sales dashboard.        |
| **Driver**          | Receives delivery assignments (food or platform-delivered products), updates location, delivers. |

---

## Store Types

Stores are the unified entity for both restaurants and product shops.

| Type           | Description                                                                                                                         |
| -------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| **Restaurant** | Sells food items via menu categories. Orders are fulfilled by platform drivers. Has operating hours, delivery fees, and prep times. |
| **Shop**       | Sells products with variants (size, color, etc.). Sellers can self-ship or use platform drivers for local delivery.                 |

---

## Features

### Admin Features

- Dashboard with platform-wide stats (orders today, revenue, active drivers, pending orders, active stores)
- Approve/reject seller shop applications
- CRUD all stores (restaurants and shops) with logo, cover image, and type-specific settings
- CRUD menu categories and menu items per restaurant
- Manage all products (view, flag, remove if policy-violating)
- Manage users (view, activate/deactivate, change roles)
- Manage sellers (view, approve/reject stores, view sales)
- Manage drivers (verify/unverify, view profiles and vehicle info)
- View and manage all orders (confirm, cancel, assign drivers)
- View reviews and ratings across all stores and products

### Seller Features

- Register as a seller and create a shop
- Shop dashboard with sales stats (orders, revenue, top products, pending orders)
- CRUD product categories within their shop
- CRUD products with multiple images, descriptions, pricing, and variants
- Manage product variants (size, color, etc.) with individual stock and pricing
- Manage inventory / stock levels
- View and fulfill incoming orders (confirm, process, ship with tracking number or mark ready for platform delivery)
- Choose fulfillment method per product: self-ship or platform driver delivery
- Manage shop profile (name, description, logo, cover image, return policy)

### Customer Features

- Browse restaurants (search, filter by active/open)
- Browse shops and products (search, filter by category, price range, rating)
- View restaurant menu organized by category
- View product details with variant selection, image gallery, and reviews
- Cart management (add items, update quantity, remove, clear) — cart is per-store
- Checkout page with delivery/shipping address selection, payment method choice (Polar / COD), and order notes
- Place order (creates order from cart; redirects to Polar checkout or proceeds directly for COD)
- Real-time order status tracking (delivery map for driver-delivered orders, shipping status for self-shipped)
- View order history
- Leave reviews (store rating + driver rating for food; product rating for shop products)
- Manage saved delivery/shipping addresses
- Manage profile

### Driver Features

- Dashboard with available delivery orders (food and platform-delivered product orders)
- Accept or reject delivery assignments
- Update order status (picked up, on the way, delivered)
- Broadcast current location to customer in real-time
- Toggle availability status (online/offline)
- View delivery history
- Manage driver profile (vehicle type, plate, license)

### Future Enhancements (Out of MVP Scope)

- Driver earnings tracking and commission system
- Seller commission and payout system
- Promo codes and discount system
- Push notifications (mobile)
- Delivery radius and distance-based fee calculation
- Product wishlists and favorites
- Shop follower system
- Live selling / streaming (TikTok Shop style)
- Seller analytics and insights
- Multi-shop cart with order splitting at checkout
- Product variant attribute management (global attribute templates)

---

## Database Schema

### Models & Relationships

```
User (UUID primary key)
 ├── hasMany  -> Order (as customer)
 ├── hasMany  -> Order (as driver)
 ├── hasMany  -> Address
 ├── hasMany  -> Review
 ├── hasMany  -> Store (as seller/owner)
 └── hasOne   -> DriverProfile

Store (SoftDeletes)
 ├── belongsTo -> User (owner/seller)
 ├── hasMany   -> Category
 ├── hasMany   -> MenuItem       (when type = Restaurant)
 ├── hasMany   -> Product        (when type = Shop)
 ├── hasMany   -> Order
 └── hasMany   -> Review

Category (SoftDeletes)
 ├── belongsTo -> Store
 ├── hasMany   -> MenuItem       (when store type = Restaurant)
 └── hasMany   -> Product        (when store type = Shop)

MenuItem (SoftDeletes)
 ├── belongsTo -> Store
 ├── belongsTo -> Category
 └── morphMany -> OrderItem (as itemable)

Product (SoftDeletes)
 ├── belongsTo -> Store
 ├── belongsTo -> Category
 ├── hasMany   -> ProductVariant
 ├── hasMany   -> ProductImage
 └── hasMany   -> Review

ProductVariant
 ├── belongsTo -> Product
 └── morphMany -> OrderItem (as itemable)

ProductImage
 └── belongsTo -> Product

Order
 ├── belongsTo -> User (customer)
 ├── belongsTo -> Store
 ├── belongsTo -> User (driver, nullable)
 ├── hasMany   -> OrderItem
 └── hasOne    -> Review

OrderItem
 ├── belongsTo -> Order
 └── morphTo   -> itemable (MenuItem or ProductVariant)

Review
 ├── belongsTo -> User (customer)
 ├── belongsTo -> Order (nullable)
 ├── belongsTo -> Store (nullable)
 ├── belongsTo -> Product (nullable)
 └── belongsTo -> User (driver, nullable)

Address
 └── belongsTo -> User

DriverProfile
 └── belongsTo -> User (one-to-one)
```

### Table Definitions

**users** (extend existing)

| Column                  | Type      | Notes                       |
| ----------------------- | --------- | --------------------------- |
| id                      | uuid      | Primary key (existing)      |
| name                    | string    | Existing                    |
| email                   | string    | Existing, unique            |
| phone                   | string    | Nullable                    |
| avatar                  | string    | Nullable, file path         |
| role                    | enum      | admin, user, seller, driver |
| email_verified_at       | timestamp | Existing                    |
| password                | string    | Existing                    |
| remember_token          | string    | Existing                    |
| created_at / updated_at | timestamp | Existing                    |

**addresses**

| Column                  | Type          | Notes                |
| ----------------------- | ------------- | -------------------- |
| id                      | uuid          | Primary key          |
| user_id                 | uuid          | Foreign key -> users |
| label                   | string        | e.g. "Home", "Work"  |
| address_line_1          | string        |                      |
| address_line_2          | string        | Nullable             |
| city                    | string        |                      |
| state                   | string        |                      |
| postal_code             | string        |                      |
| latitude                | decimal(10,7) | Nullable             |
| longitude               | decimal(10,7) | Nullable             |
| is_default              | boolean       | Default false        |
| created_at / updated_at | timestamp     |                      |

**driver_profiles**

| Column                  | Type          | Notes                        |
| ----------------------- | ------------- | ---------------------------- |
| id                      | uuid          | Primary key                  |
| user_id                 | uuid          | Foreign key -> users, unique |
| vehicle_type            | enum          | bicycle, motorcycle, car     |
| vehicle_plate           | string        | Nullable                     |
| license_number          | string        |                              |
| is_available            | boolean       | Default false                |
| is_verified             | boolean       | Default false                |
| current_latitude        | decimal(10,7) | Nullable                     |
| current_longitude       | decimal(10,7) | Nullable                     |
| created_at / updated_at | timestamp     |                              |

**stores** (SoftDeletes)

| Column                  | Type         | Notes                                                     |
| ----------------------- | ------------ | --------------------------------------------------------- |
| id                      | uuid         | Primary key                                               |
| owner_id                | uuid         | Foreign key -> users (seller), nullable for admin-created |
| type                    | enum         | restaurant, shop (StoreType)                              |
| name                    | string       |                                                           |
| slug                    | string       | Unique                                                    |
| description             | text         | Nullable                                                  |
| address                 | string       |                                                           |
| phone                   | string       | Nullable                                                  |
| email                   | string       | Nullable                                                  |
| logo                    | string       | Nullable, file path                                       |
| cover_image             | string       | Nullable, file path                                       |
| is_active               | boolean      | Default true                                              |
| is_approved             | boolean      | Default false (requires admin approval)                   |
| opening_time            | time         | Nullable (restaurants only)                               |
| closing_time            | time         | Nullable (restaurants only)                               |
| min_order_amount        | decimal(8,2) | Default 0                                                 |
| delivery_fee            | decimal(8,2) | Default 0 (restaurants, platform delivery)                |
| average_prep_time       | integer      | Nullable, minutes (restaurants only)                      |
| return_policy           | text         | Nullable (shops only)                                     |
| created_at / updated_at | timestamp    |                                                           |
| deleted_at              | timestamp    | Nullable, SoftDelete                                      |

**categories** (SoftDeletes)

| Column                  | Type      | Notes                                         |
| ----------------------- | --------- | --------------------------------------------- |
| id                      | uuid      | Primary key                                   |
| store_id                | uuid      | Foreign key -> stores                         |
| name                    | string    |                                               |
| slug                    | string    | Unique per store (composite: store_id + slug) |
| description             | text      | Nullable                                      |
| sort_order              | integer   | Default 0                                     |
| is_active               | boolean   | Default true                                  |
| created_at / updated_at | timestamp |                                               |
| deleted_at              | timestamp | Nullable, SoftDelete                          |

**menu_items** (SoftDeletes) — for restaurants only

| Column                  | Type         | Notes                                         |
| ----------------------- | ------------ | --------------------------------------------- |
| id                      | uuid         | Primary key                                   |
| store_id                | uuid         | Foreign key -> stores                         |
| category_id             | uuid         | Foreign key -> categories                     |
| name                    | string       |                                               |
| slug                    | string       | Unique per store (composite: store_id + slug) |
| description             | text         | Nullable                                      |
| price                   | decimal(8,2) |                                               |
| image                   | string       | Nullable, file path                           |
| is_available            | boolean      | Default true                                  |
| preparation_time        | integer      | Minutes, nullable                             |
| created_at / updated_at | timestamp    |                                               |
| deleted_at              | timestamp    | Nullable, SoftDelete                          |

**products** (SoftDeletes) — for shops only

| Column                  | Type          | Notes                                         |
| ----------------------- | ------------- | --------------------------------------------- |
| id                      | uuid          | Primary key                                   |
| store_id                | uuid          | Foreign key -> stores                         |
| category_id             | uuid          | Foreign key -> categories                     |
| name                    | string        |                                               |
| slug                    | string        | Unique per store (composite: store_id + slug) |
| description             | text          | Nullable                                      |
| price                   | decimal(10,2) |                                               |
| sku                     | string        | Nullable, unique                              |
| stock                   | integer       | Default 0, used when has_variants = false     |
| has_variants            | boolean       | Default false                                 |
| weight                  | decimal(8,2)  | Nullable, grams (for shipping calculation)    |
| fulfillment_method      | enum          | seller_shipping, platform_delivery            |
| is_available            | boolean       | Default true                                  |
| created_at / updated_at | timestamp     |                                               |
| deleted_at              | timestamp     | Nullable, SoftDelete                          |

**product_variants**

| Column                  | Type          | Notes                                  |
| ----------------------- | ------------- | -------------------------------------- |
| id                      | uuid          | Primary key                            |
| product_id              | uuid          | Foreign key -> products                |
| name                    | string        | Display name, e.g. "Red / Large"       |
| sku                     | string        | Nullable, unique                       |
| price                   | decimal(10,2) | Variant-specific price                 |
| stock                   | integer       | Default 0                              |
| attributes              | json          | e.g. {"color": "Red", "size": "Large"} |
| is_available            | boolean       | Default true                           |
| sort_order              | integer       | Default 0                              |
| created_at / updated_at | timestamp     |                                        |

**product_images**

| Column                  | Type      | Notes                   |
| ----------------------- | --------- | ----------------------- |
| id                      | uuid      | Primary key             |
| product_id              | uuid      | Foreign key -> products |
| image                   | string    | File path               |
| sort_order              | integer   | Default 0               |
| is_primary              | boolean   | Default false           |
| created_at / updated_at | timestamp |                         |

**orders**

| Column                  | Type          | Notes                                    |
| ----------------------- | ------------- | ---------------------------------------- |
| id                      | uuid          | Primary key                              |
| user_id                 | uuid          | Foreign key -> users (customer)          |
| store_id                | uuid          | Foreign key -> stores                    |
| driver_id               | uuid          | Nullable, foreign key -> users (driver)  |
| type                    | enum          | food_delivery, product_order (OrderType) |
| status                  | enum          | See OrderStatus enum                     |
| fulfillment_method      | enum          | platform_delivery, seller_shipping       |
| delivery_address        | string        | Snapshot of address at order time        |
| delivery_latitude       | decimal(10,7) | Nullable                                 |
| delivery_longitude      | decimal(10,7) | Nullable                                 |
| subtotal                | decimal(10,2) |                                          |
| delivery_fee            | decimal(8,2)  | For driver delivery                      |
| shipping_fee            | decimal(8,2)  | For seller shipping                      |
| total                   | decimal(10,2) |                                          |
| payment_method          | enum          | cash_on_delivery, polar                  |
| payment_status          | enum          | pending, paid, failed, refunded          |
| polar_checkout_id       | string        | Nullable, Polar checkout session ID      |
| polar_order_id          | string        | Nullable, Polar order ID                 |
| tracking_number         | string        | Nullable (seller-shipped product orders) |
| notes                   | text          | Nullable, customer notes                 |
| estimated_delivery_at   | timestamp     | Nullable                                 |
| shipped_at              | timestamp     | Nullable                                 |
| delivered_at            | timestamp     | Nullable                                 |
| cancelled_at            | timestamp     | Nullable                                 |
| created_at / updated_at | timestamp     |                                          |

**order_items**

| Column                  | Type          | Notes                                         |
| ----------------------- | ------------- | --------------------------------------------- |
| id                      | uuid          | Primary key                                   |
| order_id                | uuid          | Foreign key -> orders                         |
| itemable_type           | string        | Polymorphic type (MenuItem or ProductVariant) |
| itemable_id             | uuid          | Polymorphic ID                                |
| product_name            | string        | Snapshot of item name at order time           |
| variant_name            | string        | Nullable, snapshot of variant name            |
| quantity                | integer       |                                               |
| unit_price              | decimal(8,2)  | Snapshot of price at order time               |
| total_price             | decimal(10,2) | quantity \* unit_price                        |
| special_instructions    | text          | Nullable                                      |
| created_at / updated_at | timestamp     |                                               |

**reviews**

| Column                  | Type      | Notes                             |
| ----------------------- | --------- | --------------------------------- |
| id                      | uuid      | Primary key                       |
| user_id                 | uuid      | Foreign key -> users (customer)   |
| order_id                | uuid      | Nullable, foreign key -> orders   |
| store_id                | uuid      | Nullable, foreign key -> stores   |
| product_id              | uuid      | Nullable, foreign key -> products |
| driver_id               | uuid      | Nullable, foreign key -> users    |
| rating                  | tinyint   | 1-5 (store or product rating)     |
| driver_rating           | tinyint   | 1-5, nullable                     |
| comment                 | text      | Nullable                          |
| created_at / updated_at | timestamp |                                   |

**notifications** (Laravel built-in)

| Column                  | Type      | Notes                   |
| ----------------------- | --------- | ----------------------- |
| id                      | uuid      | Primary key             |
| type                    | string    | Notification class name |
| notifiable_type         | string    | Polymorphic type        |
| notifiable_id           | uuid      | Polymorphic ID          |
| data                    | text      | JSON payload            |
| read_at                 | timestamp | Nullable                |
| created_at / updated_at | timestamp |                         |

> Migration for this table: `php artisan notifications:table`

### Enums

| Enum                | Values                                                                                                                          |
| ------------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| `UserRole`          | `Admin`, `User`, `Seller`, `Driver`                                                                                             |
| `StoreType`         | `Restaurant`, `Shop`                                                                                                            |
| `OrderType`         | `FoodDelivery`, `ProductOrder`                                                                                                  |
| `OrderStatus`       | `Pending`, `Confirmed`, `Processing`, `ReadyForPickup`, `Shipped`, `Assigned`, `PickedUp`, `OnTheWay`, `Delivered`, `Cancelled` |
| `FulfillmentMethod` | `PlatformDelivery`, `SellerShipping`                                                                                            |
| `PaymentMethod`     | `CashOnDelivery`, `Polar`                                                                                                       |
| `PaymentStatus`     | `Pending`, `Paid`, `Failed`, `Refunded`                                                                                         |
| `VehicleType`       | `Bicycle`, `Motorcycle`, `Car`                                                                                                  |

### Order Status Per Order Type

**Food delivery** (fulfillment: PlatformDelivery):
`Pending -> Confirmed -> Processing -> ReadyForPickup -> Assigned -> PickedUp -> OnTheWay -> Delivered`

**Product order — seller shipping** (fulfillment: SellerShipping):
`Pending -> Confirmed -> Processing -> Shipped -> Delivered`

**Product order — platform delivery** (fulfillment: PlatformDelivery):
`Pending -> Confirmed -> Processing -> ReadyForPickup -> Assigned -> PickedUp -> OnTheWay -> Delivered`

All types can transition to `Cancelled` from any non-terminal status (subject to cancellation rules).

---

## Payment Integration

The application supports two payment methods. The customer chooses at checkout.

### Payment Methods Overview

| Method               | How It Works                                                                                          | Payment Status Flow           |
| -------------------- | ----------------------------------------------------------------------------------------------------- | ----------------------------- |
| **Polar.sh**         | Customer is redirected to Polar's hosted checkout (or embedded checkout). Webhook confirms payment.   | Pending -> Paid (via webhook) |
| **Cash on Delivery** | No online payment. Driver collects cash upon delivery. Driver marks payment as collected on delivery. | Pending -> Paid (on delivery) |

> **Note:** COD is only available for platform-delivered orders (food delivery and product orders using platform drivers). Seller-shipped product orders require Polar payment.

### Polar.sh Integration (Digital Payment)

**Package:** `danestves/laravel-polar`

**Setup:**

1. Install via `composer require danestves/laravel-polar`
2. Run `php artisan polar:install` (publishes config, migrations, views)
3. Add `Billable` trait to the `User` model
4. Configure `.env` with `POLAR_ACCESS_TOKEN` and `POLAR_WEBHOOK_SECRET`
5. Exclude `polar/*` from CSRF verification in `bootstrap/app.php`
6. Register webhook endpoint in Polar dashboard pointing to `{APP_URL}/polar/webhook`

**Environment Variables:**

```
POLAR_ACCESS_TOKEN=<your_access_token>
POLAR_WEBHOOK_SECRET=<your_webhook_secret>
POLAR_PATH=polar
```

**Checkout Flow (Polar):**

```
Customer selects "Pay Online" at checkout
  -> App creates an internal Order (status: PENDING, payment_status: PENDING, payment_method: POLAR)
  -> App calls $user->checkout(['product_id'])
       ->withMetadata(['order_id' => $order->id])
       ->withSuccessUrl(route('customer.orders.show', $order) . '?checkout_id={CHECKOUT_ID}')
  -> Customer is redirected to Polar's hosted checkout page
  -> Customer completes payment on Polar
  -> Polar sends `order.created` webhook to {APP_URL}/polar/webhook
  -> App webhook listener matches metadata.order_id to internal Order
  -> App updates Order: payment_status -> PAID, stores polar_checkout_id and polar_order_id
  -> App dispatches OrderPaid event
  -> Normal order lifecycle continues (Admin/Seller confirms, processes, etc.)
```

**Webhook Events to Handle:**

| Polar Event     | App Action                                                       |
| --------------- | ---------------------------------------------------------------- |
| `order.created` | Match to internal order via metadata, set payment_status to Paid |
| `order.updated` | Handle refunds if applicable, set payment_status to Refunded     |

**Embedded Checkout (Alternative):**

- Include `@polarEmbedScript` in the `<head>` of the checkout layout
- Use `<x-polar-button :checkout="$checkout" />` Blade component for in-page checkout
- Avoids full redirect; customer stays on the app

**Sandbox vs Production:**

- Use Polar sandbox (`sandbox.polar.sh`) during development and testing
- Use `polar listen http://localhost:8000/` CLI for local webhook testing
- Switch to production tokens for deployment

### Cash on Delivery (COD) Integration

**Flow:**

```
Customer selects "Cash on Delivery" at checkout
  -> App creates an internal Order (status: PENDING, payment_status: PENDING, payment_method: COD)
  -> No external payment step; order proceeds directly into the order lifecycle
  -> Admin/Seller confirms order -> Driver assigned -> Driver picks up -> Driver delivers
  -> Driver marks delivery as complete
  -> App automatically sets payment_status -> PAID (COD collected)
  -> OrderDelivered event dispatched
```

**COD-Specific Rules:**

- COD is only available for platform-delivered orders (FulfillmentMethod::PlatformDelivery)
- Seller-shipped product orders must use Polar (no COD for seller shipping)
- No upfront payment is required; the order enters the lifecycle immediately
- The driver collects the exact `total` amount in cash upon delivery
- Payment status transitions to `Paid` only when the order status reaches `Delivered`
- If the order is cancelled before delivery, payment_status stays `Pending` (no money exchanged)
- Admin dashboard shows COD orders with a distinct badge so staff can track cash collection

---

## System Flows

### Food Delivery Order Lifecycle

```
Customer places food order (from cart -> checkout -> confirm)
  -> If payment_method == POLAR:
       -> Customer is redirected to Polar checkout
       -> Polar webhook confirms payment (payment_status: PAID)
  -> If payment_method == COD:
       -> No external payment step
  -> Order status: PENDING
  -> Event: OrderPlaced (Admin notified via broadcast)

Admin confirms order
  -> Order status: CONFIRMED
  -> Event: OrderStatusUpdated (Customer notified)

Admin/Restaurant marks as processing (preparing food)
  -> Order status: PROCESSING

Admin marks food as ready
  -> Order status: READY_FOR_PICKUP
  -> Event: NewDeliveryAvailable (Available drivers notified)

Driver accepts delivery
  -> Order status: ASSIGNED
  -> Event: OrderAssignedToDriver (Customer sees driver info)

Driver picks up food from restaurant
  -> Order status: PICKED_UP

Driver en route to customer
  -> Order status: ON_THE_WAY
  -> Event: DriverLocationUpdated (Customer sees live location)

Driver completes delivery
  -> Order status: DELIVERED
  -> If payment_method == COD: payment_status -> PAID (cash collected)
  -> Event: OrderStatusUpdated (Customer prompted to review)
```

### Product Order Lifecycle — Seller Shipping

```
Customer places product order (from cart -> checkout -> confirm)
  -> Payment method: POLAR only (COD not available for seller shipping)
  -> Customer is redirected to Polar checkout
  -> Polar webhook confirms payment (payment_status: PAID)
  -> Order status: PENDING
  -> Event: OrderPlaced (Seller notified)

Seller confirms order
  -> Order status: CONFIRMED
  -> Event: OrderStatusUpdated (Customer notified)

Seller processes and packs the order
  -> Order status: PROCESSING

Seller ships the order (provides tracking number)
  -> Order status: SHIPPED
  -> shipped_at timestamp is set
  -> tracking_number is stored
  -> Event: OrderShipped (Customer notified with tracking info)

Order is delivered (seller or customer marks as delivered)
  -> Order status: DELIVERED
  -> delivered_at timestamp is set
  -> Event: OrderStatusUpdated (Customer prompted to review)
```

### Product Order Lifecycle — Platform Delivery

```
Customer places product order with platform delivery
  -> Payment method: POLAR or COD
  -> Same flow as Polar/COD described above
  -> Order status: PENDING
  -> Event: OrderPlaced (Seller notified)

Seller confirms order
  -> Order status: CONFIRMED

Seller packs the order
  -> Order status: PROCESSING

Seller marks order as ready for pickup
  -> Order status: READY_FOR_PICKUP
  -> Event: NewDeliveryAvailable (Available drivers notified)

Driver accepts delivery
  -> Order status: ASSIGNED
  -> Same driver delivery flow as food delivery (PICKED_UP -> ON_THE_WAY -> DELIVERED)
```

### Order Status Transition Rules

Only the following transitions are valid per order type:

**Food delivery and product orders with platform delivery:**

```
PENDING          -> CONFIRMED, CANCELLED
CONFIRMED        -> PROCESSING, CANCELLED
PROCESSING       -> READY_FOR_PICKUP, CANCELLED
READY_FOR_PICKUP -> ASSIGNED, CANCELLED
ASSIGNED         -> PICKED_UP, CANCELLED
PICKED_UP        -> ON_THE_WAY
ON_THE_WAY       -> DELIVERED
DELIVERED        -> (terminal state)
CANCELLED        -> (terminal state)
```

**Product orders with seller shipping:**

```
PENDING     -> CONFIRMED, CANCELLED
CONFIRMED   -> PROCESSING, CANCELLED
PROCESSING  -> SHIPPED, CANCELLED
SHIPPED     -> DELIVERED
DELIVERED   -> (terminal state)
CANCELLED   -> (terminal state)
```

**Cancellation rules:**

- **Customer** can cancel when status is `Pending` or `Confirmed` only.
- **Seller** can cancel their own store's orders at any non-terminal status before `Shipped`/`Assigned`.
- **Admin** can cancel at any non-terminal status.
- **Driver** cannot cancel, but can be unassigned by Admin (reverts to `ReadyForPickup`).

### Driver Assignment Flow

```
Order marked READY_FOR_PICKUP
  -> Broadcast to all available & verified drivers
  -> First driver to accept gets assigned (race condition handled via DB lock)
  -> Other drivers see order removed from available list
  -> Order status: ASSIGNED
  -> Customer notified with driver details
```

### Seller Store Approval Flow

```
Seller registers and creates a shop
  -> Store created with is_approved = false, is_active = false
  -> Admin notified of new store application
  -> Admin reviews store details and approves or rejects
  -> If approved: is_approved = true, is_active = true
  -> Seller notified of approval status
  -> Seller can now add products and start receiving orders
```

---

## Broadcasting Events

| Event                   | Channel                     | Payload                | Listeners                   |
| ----------------------- | --------------------------- | ---------------------- | --------------------------- |
| `OrderPlaced`           | `private-admin-orders`      | order details          | Admin dashboard             |
| `OrderPlaced`           | `private-seller.{sellerId}` | order details          | Seller dashboard            |
| `OrderStatusUpdated`    | `private-orders.{orderId}`  | order, new status      | Customer tracking page      |
| `OrderPaid`             | `private-orders.{orderId}`  | order, payment method  | Customer tracking page      |
| `OrderShipped`          | `private-orders.{orderId}`  | order, tracking number | Customer tracking page      |
| `DriverLocationUpdated` | `private-orders.{orderId}`  | latitude, longitude    | Customer map                |
| `NewDeliveryAvailable`  | `private-drivers`           | order summary          | Available drivers dashboard |
| `OrderAssignedToDriver` | `private-driver.{driverId}` | order, store details   | Specific driver             |
| `StoreApproved`         | `private-seller.{sellerId}` | store details          | Seller notification         |

---

## Folder Structure (Action Pattern)

```
app/
├── Actions/
│   ├── Auth/
│   │   ├── CreateNewUser.php
│   │   └── UpdateUserProfile.php
│   ├── Store/
│   │   ├── CreateStore.php
│   │   ├── UpdateStore.php
│   │   ├── DeleteStore.php
│   │   ├── ToggleStoreStatus.php
│   │   └── ApproveStore.php
│   ├── Menu/
│   │   ├── CreateCategory.php
│   │   ├── UpdateCategory.php
│   │   ├── DeleteCategory.php
│   │   ├── CreateMenuItem.php
│   │   ├── UpdateMenuItem.php
│   │   ├── DeleteMenuItem.php
│   │   └── ToggleMenuItemAvailability.php
│   ├── Product/
│   │   ├── CreateProduct.php
│   │   ├── UpdateProduct.php
│   │   ├── DeleteProduct.php
│   │   ├── ToggleProductAvailability.php
│   │   ├── CreateProductVariant.php
│   │   ├── UpdateProductVariant.php
│   │   ├── DeleteProductVariant.php
│   │   ├── ManageProductImages.php
│   │   └── UpdateStock.php
│   ├── Order/
│   │   ├── PlaceOrder.php
│   │   ├── ConfirmOrder.php
│   │   ├── CancelOrder.php
│   │   ├── UpdateOrderStatus.php
│   │   ├── ShipOrder.php
│   │   └── AssignDriverToOrder.php
│   ├── Payment/
│   │   ├── CreatePolarCheckout.php
│   │   └── HandlePolarWebhook.php
│   ├── Driver/
│   │   ├── AcceptDelivery.php
│   │   ├── UpdateDriverLocation.php
│   │   ├── CompleteDelivery.php
│   │   └── ToggleDriverAvailability.php
│   └── Review/
│       └── CreateReview.php
│
├── Enums/
│   ├── UserRole.php
│   ├── StoreType.php
│   ├── OrderType.php
│   ├── OrderStatus.php
│   ├── FulfillmentMethod.php
│   ├── PaymentMethod.php
│   ├── PaymentStatus.php
│   └── VehicleType.php
│
├── Events/
│   ├── OrderPlaced.php
│   ├── OrderStatusUpdated.php
│   ├── OrderPaid.php
│   ├── OrderShipped.php
│   ├── DriverLocationUpdated.php
│   ├── NewDeliveryAvailable.php
│   ├── OrderAssignedToDriver.php
│   └── StoreApproved.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── StoreController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── MenuItemController.php
│   │   │   ├── OrderController.php
│   │   │   ├── UserController.php
│   │   │   ├── SellerController.php
│   │   │   └── DriverController.php
│   │   ├── Seller/
│   │   │   ├── DashboardController.php
│   │   │   ├── StoreController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── ProductController.php
│   │   │   ├── ProductVariantController.php
│   │   │   ├── OrderController.php
│   │   │   └── ProfileController.php
│   │   ├── Customer/
│   │   │   ├── StoreController.php
│   │   │   ├── ProductController.php
│   │   │   ├── CartController.php
│   │   │   ├── CheckoutController.php
│   │   │   ├── OrderController.php
│   │   │   ├── ReviewController.php
│   │   │   ├── AddressController.php
│   │   │   └── ProfileController.php
│   │   └── Driver/
│   │       ├── DashboardController.php
│   │       ├── DeliveryController.php
│   │       └── ProfileController.php
│   ├── Middleware/
│   │   └── EnsureUserHasRole.php
│   └── Requests/
│       ├── Admin/
│       │   ├── StoreStoreRequest.php
│       │   ├── UpdateStoreRequest.php
│       │   ├── StoreCategoryRequest.php
│       │   ├── UpdateCategoryRequest.php
│       │   ├── StoreMenuItemRequest.php
│       │   └── UpdateMenuItemRequest.php
│       ├── Seller/
│       │   ├── StoreProductRequest.php
│       │   ├── UpdateProductRequest.php
│       │   ├── StoreProductVariantRequest.php
│       │   ├── UpdateProductVariantRequest.php
│       │   ├── ShipOrderRequest.php
│       │   └── UpdateStoreProfileRequest.php
│       ├── Customer/
│       │   ├── PlaceOrderRequest.php
│       │   ├── StoreReviewRequest.php
│       │   ├── StoreAddressRequest.php
│       │   └── UpdateAddressRequest.php
│       └── Driver/
│           └── UpdateLocationRequest.php
│
├── Listeners/
│   └── HandlePolarWebhookEvent.php
│
├── Livewire/
│   ├── Cart.php
│   ├── OrderTracker.php
│   ├── ProductBrowser.php
│   ├── DriverMap.php
│   ├── Admin/
│   │   ├── OrderBoard.php
│   │   └── DashboardStats.php
│   ├── Seller/
│   │   ├── OrderManager.php
│   │   ├── StockManager.php
│   │   └── DashboardStats.php
│   └── Driver/
│       ├── AvailableOrders.php
│       └── DeliveryStatus.php
│
├── Models/
│   ├── User.php
│   ├── Address.php
│   ├── DriverProfile.php
│   ├── Store.php
│   ├── Category.php
│   ├── MenuItem.php
│   ├── Product.php
│   ├── ProductVariant.php
│   ├── ProductImage.php
│   ├── Order.php
│   ├── OrderItem.php
│   └── Review.php
│
├── Notifications/
│   ├── OrderConfirmedNotification.php
│   ├── OrderReadyNotification.php
│   ├── OrderShippedNotification.php
│   ├── DeliveryCompletedNotification.php
│   ├── NewOrderNotification.php
│   └── StoreApprovedNotification.php
│
├── Policies/
│   ├── OrderPolicy.php
│   ├── StorePolicy.php
│   ├── ProductPolicy.php
│   ├── ReviewPolicy.php
│   ├── AddressPolicy.php
│   └── DriverProfilePolicy.php
│
├── Providers/
│   └── AppServiceProvider.php
│
└── Services/
    ├── CartService.php
    ├── OrderService.php
    ├── PaymentService.php
    ├── DeliveryService.php
    └── StockService.php

resources/
├── css/
│   └── app.css
├── js/
│   └── app.js
└── views/
    ├── layouts/
    │   ├── app.blade.php              (Customer layout, includes @polarEmbedScript)
    │   ├── admin.blade.php            (Admin layout)
    │   ├── seller.blade.php           (Seller layout)
    │   ├── driver.blade.php           (Driver layout)
    │   └── guest.blade.php            (Unauthenticated layout)
    ├── components/
    │   ├── nav-link.blade.php
    │   ├── button.blade.php
    │   ├── input.blade.php
    │   ├── card.blade.php
    │   ├── badge.blade.php
    │   ├── modal.blade.php
    │   ├── rating-stars.blade.php
    │   ├── product-card.blade.php
    │   ├── variant-selector.blade.php
    │   └── image-gallery.blade.php
    ├── auth/
    │   ├── login.blade.php
    │   ├── register.blade.php
    │   ├── forgot-password.blade.php
    │   ├── reset-password.blade.php
    │   └── verify-email.blade.php
    ├── admin/
    │   ├── dashboard.blade.php
    │   ├── stores/
    │   │   ├── index.blade.php
    │   │   ├── create.blade.php
    │   │   ├── edit.blade.php
    │   │   └── show.blade.php
    │   ├── categories/
    │   │   ├── index.blade.php
    │   │   ├── create.blade.php
    │   │   └── edit.blade.php
    │   ├── menu-items/
    │   │   ├── index.blade.php
    │   │   ├── create.blade.php
    │   │   └── edit.blade.php
    │   ├── orders/
    │   │   ├── index.blade.php
    │   │   └── show.blade.php
    │   ├── users/
    │   │   └── index.blade.php
    │   ├── sellers/
    │   │   ├── index.blade.php
    │   │   └── show.blade.php
    │   └── drivers/
    │       ├── index.blade.php
    │       └── show.blade.php
    ├── seller/
    │   ├── dashboard.blade.php
    │   ├── store/
    │   │   ├── edit.blade.php
    │   │   └── create.blade.php
    │   ├── categories/
    │   │   ├── index.blade.php
    │   │   ├── create.blade.php
    │   │   └── edit.blade.php
    │   ├── products/
    │   │   ├── index.blade.php
    │   │   ├── create.blade.php
    │   │   ├── edit.blade.php
    │   │   └── show.blade.php
    │   ├── orders/
    │   │   ├── index.blade.php
    │   │   └── show.blade.php
    │   └── profile.blade.php
    ├── customer/
    │   ├── stores/
    │   │   ├── index.blade.php         (Browse all stores)
    │   │   └── show.blade.php          (View store: menu or product listing)
    │   ├── products/
    │   │   ├── index.blade.php         (Browse all products across shops)
    │   │   └── show.blade.php          (Product detail with variants, images, reviews)
    │   ├── cart.blade.php
    │   ├── checkout.blade.php
    │   ├── orders/
    │   │   ├── index.blade.php
    │   │   └── show.blade.php
    │   ├── addresses/
    │   │   ├── index.blade.php
    │   │   ├── create.blade.php
    │   │   └── edit.blade.php
    │   └── profile.blade.php
    ├── driver/
    │   ├── dashboard.blade.php
    │   ├── deliveries/
    │   │   ├── index.blade.php
    │   │   └── show.blade.php
    │   └── profile.blade.php
    └── livewire/
        ├── cart.blade.php
        ├── order-tracker.blade.php
        ├── product-browser.blade.php
        ├── driver-map.blade.php
        ├── admin/
        │   ├── order-board.blade.php
        │   └── dashboard-stats.blade.php
        ├── seller/
        │   ├── order-manager.blade.php
        │   ├── stock-manager.blade.php
        │   └── dashboard-stats.blade.php
        └── driver/
            ├── available-orders.blade.php
            └── delivery-status.blade.php

routes/
├── web.php
├── channels.php
└── console.php
```

---

## Route Structure

### Public Routes

```
GET  /                                  Welcome / landing page
GET  /stores                            Browse all stores (restaurants + shops)
GET  /restaurants                       Browse restaurants only
GET  /shops                             Browse shops only
GET  /stores/{store:slug}               View store (menu for restaurant, products for shop)
GET  /products                          Browse all products across shops
GET  /products/{product:slug}           View product detail (images, variants, reviews)
```

### Authentication Routes (Fortify)

```
GET|POST  /login                        Login
GET|POST  /register                     Register (customer or seller registration)
POST      /logout                       Logout
GET|POST  /forgot-password              Request password reset
GET|POST  /reset-password/{token}       Reset password
GET       /email/verify                 Email verification notice
GET       /email/verify/{id}/{hash}     Verify email
POST      /email/verification-notification  Resend verification
```

### Customer Routes (auth + role:user)

```
GET     /cart                           View cart (Livewire component)
GET     /checkout                       Checkout page (address, payment method, notes, confirm)
POST    /orders                         Place order (from checkout, handles both Polar and COD)

GET     /orders                         Order history
GET     /orders/{order}                 Track order (Livewire order tracker / shipping status)
GET     /orders/{order}/payment-success Payment success callback (Polar redirect-back)
POST    /orders/{order}/review          Submit review

GET     /addresses                      List saved addresses
POST    /addresses                      Create address
GET     /addresses/create               Create address form
GET     /addresses/{address}/edit       Edit address form
PUT     /addresses/{address}            Update address
DELETE  /addresses/{address}            Delete address

GET     /profile                        View/edit profile
PUT     /profile                        Update profile
```

### Seller Routes (auth + role:seller, prefix: /seller)

```
GET     /seller/dashboard               Seller dashboard (sales stats, pending orders)

GET     /seller/store/create            Create shop form (if no shop yet)
POST    /seller/store                   Create shop
GET     /seller/store/edit              Edit shop profile
PUT     /seller/store                   Update shop profile

GET     /seller/categories              List product categories
POST    /seller/categories              Create category
GET     /seller/categories/create       Create form
GET     /seller/categories/{category}/edit  Edit form
PUT     /seller/categories/{category}   Update category
DELETE  /seller/categories/{category}   Delete category

GET     /seller/products                List products
POST    /seller/products                Create product
GET     /seller/products/create         Create form
GET     /seller/products/{product}      View product (with variants, images, stock)
GET     /seller/products/{product}/edit Edit form
PUT     /seller/products/{product}      Update product
DELETE  /seller/products/{product}      Delete product

POST    /seller/products/{product}/variants         Create variant
PUT     /seller/products/{product}/variants/{variant}  Update variant
DELETE  /seller/products/{product}/variants/{variant}  Delete variant

POST    /seller/products/{product}/images           Upload images
DELETE  /seller/products/{product}/images/{image}    Delete image

GET     /seller/orders                  List incoming orders
GET     /seller/orders/{order}          View order details
PUT     /seller/orders/{order}/confirm  Confirm order
PUT     /seller/orders/{order}/process  Mark as processing
PUT     /seller/orders/{order}/ship     Ship order (with tracking number)
PUT     /seller/orders/{order}/ready    Mark ready for platform pickup
PUT     /seller/orders/{order}/cancel   Cancel order

GET     /seller/profile                 View/edit seller profile
PUT     /seller/profile                 Update seller profile
```

### Admin Routes (auth + role:admin, prefix: /admin)

```
GET     /admin/dashboard                Admin dashboard

GET     /admin/stores                   List all stores
POST    /admin/stores                   Create store (admin can create restaurants directly)
GET     /admin/stores/create            Create form
GET     /admin/stores/{store}           Show store
GET     /admin/stores/{store}/edit      Edit form
PUT     /admin/stores/{store}           Update store
DELETE  /admin/stores/{store}           Soft delete store
PUT     /admin/stores/{store}/approve   Approve seller's store
PUT     /admin/stores/{store}/reject    Reject seller's store

GET     /admin/stores/{store}/categories          List categories
POST    /admin/stores/{store}/categories          Create category
GET     /admin/stores/{store}/categories/create   Create form
GET     /admin/stores/{store}/categories/{category}/edit  Edit form
PUT     /admin/stores/{store}/categories/{category}      Update
DELETE  /admin/stores/{store}/categories/{category}      Soft delete

GET     /admin/stores/{store}/menu-items          List items (restaurants)
POST    /admin/stores/{store}/menu-items          Create item
GET     /admin/stores/{store}/menu-items/create   Create form
GET     /admin/stores/{store}/menu-items/{menuItem}/edit  Edit form
PUT     /admin/stores/{store}/menu-items/{menuItem}      Update
DELETE  /admin/stores/{store}/menu-items/{menuItem}      Soft delete

GET     /admin/orders                   List all orders (food + product)
GET     /admin/orders/{order}           View order details
PUT     /admin/orders/{order}/status    Update order status
PUT     /admin/orders/{order}/assign-driver  Assign driver to order

GET     /admin/users                    List users
GET     /admin/sellers                  List sellers
GET     /admin/sellers/{seller}         View seller and their store
GET     /admin/drivers                  List drivers
GET     /admin/drivers/{driver}         View driver profile
PUT     /admin/drivers/{driver}/verify  Verify/unverify driver
```

### Driver Routes (auth + role:driver, prefix: /driver)

```
GET     /driver/dashboard               Driver dashboard (available orders: food + platform-delivered products)
GET     /driver/deliveries              Delivery history
GET     /driver/deliveries/{order}      Delivery details
POST    /driver/deliveries/{order}/accept   Accept delivery
PUT     /driver/deliveries/{order}/status   Update delivery status
POST    /driver/location                Update current location
PUT     /driver/availability            Toggle online/offline
GET     /driver/profile                 View profile
PUT     /driver/profile                 Update profile
```

### Webhook Routes (no auth, signature-verified)

```
POST    /polar/webhook                  Polar webhook endpoint (handled by laravel-polar package)
```

---

## Services

| Service           | Responsibility                                                                                                                                                                                                |
| ----------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `CartService`     | Session-based cart management. Add/remove items (menu items or product variants), calculate totals, clear cart. Scoped per store (switching store clears cart with a warning).                                |
| `OrderService`    | Order lifecycle management. Creates order from cart, validates status transitions per order type (food vs product, self-ship vs platform delivery), dispatches events on status change.                       |
| `PaymentService`  | Payment method orchestration. Creates Polar checkout sessions for digital payment. Handles COD payment marking on delivery. Enforces COD availability rules (platform delivery only). Updates payment_status. |
| `DeliveryService` | Driver assignment logic with DB-level locking to prevent race conditions. Delivery time estimation. Handles both food delivery and platform-delivered product orders.                                         |
| `StockService`    | Product inventory management. Decrements stock on order placement, restores stock on cancellation. Handles both simple products and variant-level stock tracking.                                             |

---

## Middleware

| Middleware          | Alias  | Purpose                                                                          |
| ------------------- | ------ | -------------------------------------------------------------------------------- |
| `EnsureUserHasRole` | `role` | Checks authenticated user's role against allowed roles. Returns 403 on mismatch. |

**Usage in routes:**

```php
->middleware('role:admin')
->middleware('role:user')
->middleware('role:seller')
->middleware('role:driver')
->middleware('role:admin,seller')  // multiple roles allowed
```

**Registration in `bootstrap/app.php`:**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    ]);

    $middleware->validateCsrfTokens(except: [
        'polar/*',
    ]);
})
```

---

## Policies

| Policy                | Model           | Key Rules                                                                                                         |
| --------------------- | --------------- | ----------------------------------------------------------------------------------------------------------------- |
| `OrderPolicy`         | `Order`         | Customer views own orders only. Seller views orders for their store. Admin views all. Driver views assigned only. |
| `StorePolicy`         | `Store`         | Seller manages own store only. Admin manages all. Public can view active + approved stores.                       |
| `ProductPolicy`       | `Product`       | Seller manages products in own store only. Admin can view/flag all. Public can view available.                    |
| `ReviewPolicy`        | `Review`        | Customer creates review for own delivered orders only. One review per order.                                      |
| `AddressPolicy`       | `Address`       | Customer manages own addresses only.                                                                              |
| `DriverProfilePolicy` | `DriverProfile` | Driver manages own profile only. Admin can view/verify all.                                                       |

---

## Testing Strategy

Tests are written with Pest v4. All models have factories with relevant states.

### Test Directory Structure

```
tests/
├── Feature/
│   ├── Actions/
│   │   ├── Auth/
│   │   ├── Store/
│   │   ├── Menu/
│   │   ├── Product/
│   │   ├── Order/
│   │   ├── Payment/
│   │   ├── Driver/
│   │   └── Review/
│   ├── Http/
│   │   ├── Admin/
│   │   ├── Seller/
│   │   ├── Customer/
│   │   └── Driver/
│   ├── Livewire/
│   ├── Middleware/
│   ├── Policies/
│   └── Webhooks/
├── Unit/
│   ├── Models/
│   ├── Enums/
│   ├── Services/
│   └── Actions/
└── Browser/
```

### Coverage Targets

- Feature tests for every Action class (success + failure paths)
- HTTP tests for every controller endpoint (authentication, authorization, validation, happy path)
- Livewire component tests for all interactive components
- Policy tests for every authorization rule
- Model relationship and scope tests
- Broadcasting event structure and channel authorization tests
- Middleware role-check tests (allowed, denied, unauthenticated)
- Service class unit tests (CartService, OrderService, PaymentService, DeliveryService, StockService)
- Order status transition validation tests per order type (valid + invalid transitions)
- Stock management tests (decrement on order, restore on cancellation, variant-level tracking)
- Payment flow tests:
    - Polar checkout creation and redirect
    - Polar webhook signature verification and payload handling
    - COD payment status transition on delivery completion
    - COD availability enforcement (platform delivery only)
    - Payment method validation at checkout
- Seller flow tests:
    - Store creation and approval workflow
    - Product CRUD with variants and images
    - Order fulfillment (confirm, process, ship with tracking)
    - Stock updates and availability
- Factories with states: `unverified`, `admin`, `seller`, `driver`, `available`, `verified`, `restaurant`, `shop`, `approved`, `withVariants`, `delivered`, `cancelled`, `shipped`, `paid_with_polar`, `paid_with_cod`, `seller_shipping`, `platform_delivery`, etc.
