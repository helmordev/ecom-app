# EcomApp - System Plan

> Food delivery application with Admin, Customer, and Driver roles.
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

| Role                | Description                                                                    |
| ------------------- | ------------------------------------------------------------------------------ |
| **Admin**           | Manages restaurants, menus, users, drivers, and orders. Full dashboard access. |
| **User** (Customer) | Browses restaurants, places orders, tracks delivery, leaves reviews.           |
| **Driver**          | Receives delivery assignments, updates location, completes deliveries.         |

---

## Features

### Admin Features

- Dashboard with stats (orders today, revenue, active drivers, pending orders)
- CRUD restaurants with logo, cover image, operating hours, delivery fee
- CRUD menu categories and menu items per restaurant
- Manage users (view, activate/deactivate, change roles)
- Manage drivers (verify/unverify, view profiles and vehicle info)
- View and manage all orders (confirm, cancel, assign drivers)
- View reviews and ratings

### Customer Features

- Browse restaurants (search, filter by active/open)
- View restaurant menu organized by category
- Cart management (add items, update quantity, remove, clear)
- Checkout page with delivery address selection, payment method choice (Polar / COD), and order notes
- Place order (creates order from cart; redirects to Polar checkout or proceeds directly for COD)
- Real-time order status tracking
- View order history
- Leave reviews (restaurant rating + driver rating)
- Manage saved delivery addresses
- Manage profile

### Driver Features

- Dashboard with available delivery orders
- Accept or reject delivery assignments
- Update order status (picked up, on the way, delivered)
- Broadcast current location to customer in real-time
- Toggle availability status (online/offline)
- View delivery history
- Manage driver profile (vehicle type, plate, license)

### Future Enhancements (Out of MVP Scope)

- Driver earnings tracking and commission system
- Restaurant owner role (self-managed restaurants)
- Promo codes and discount system
- Push notifications (mobile)
- Delivery radius and distance-based fee calculation

---

## Database Schema

### Models & Relationships

```
User (UUID primary key)
 ├── hasMany  -> Order (as customer)
 ├── hasMany  -> Order (as driver)
 ├── hasMany  -> Address
 ├── hasMany  -> Review
 └── hasOne   -> DriverProfile

Restaurant (SoftDeletes)
 ├── hasMany  -> Category
 ├── hasMany  -> MenuItem
 ├── hasMany  -> Order
 └── hasMany  -> Review

Category (SoftDeletes)
 ├── belongsTo -> Restaurant
 └── hasMany   -> MenuItem

MenuItem (SoftDeletes)
 ├── belongsTo -> Restaurant
 ├── belongsTo -> Category
 └── hasMany   -> OrderItem

Order
 ├── belongsTo -> User (customer)
 ├── belongsTo -> Restaurant
 ├── belongsTo -> User (driver, nullable)
 ├── hasMany   -> OrderItem
 └── hasOne    -> Review

OrderItem
 ├── belongsTo -> Order
 └── belongsTo -> MenuItem

Review
 ├── belongsTo -> User (customer)
 ├── belongsTo -> Order
 ├── belongsTo -> Restaurant
 └── belongsTo -> User (driver, nullable)

Address
 └── belongsTo -> User

DriverProfile
 └── belongsTo -> User (one-to-one)
```

### Table Definitions

**users** (extend existing)

| Column                  | Type      | Notes                  |
| ----------------------- | --------- | ---------------------- |
| id                      | uuid      | Primary key (existing) |
| name                    | string    | Existing               |
| email                   | string    | Existing, unique       |
| phone                   | string    | Nullable               |
| avatar                  | string    | Nullable, file path    |
| role                    | enum      | admin, user, driver    |
| email_verified_at       | timestamp | Existing               |
| password                | string    | Existing               |
| remember_token          | string    | Existing               |
| created_at / updated_at | timestamp | Existing               |

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

**restaurants** (SoftDeletes)

| Column                  | Type         | Notes                |
| ----------------------- | ------------ | -------------------- |
| id                      | uuid         | Primary key          |
| name                    | string       |                      |
| slug                    | string       | Unique               |
| description             | text         | Nullable             |
| address                 | string       |                      |
| phone                   | string       | Nullable             |
| email                   | string       | Nullable             |
| logo                    | string       | Nullable, file path  |
| cover_image             | string       | Nullable, file path  |
| is_active               | boolean      | Default true         |
| opening_time            | time         |                      |
| closing_time            | time         |                      |
| min_order_amount        | decimal(8,2) | Default 0            |
| delivery_fee            | decimal(8,2) | Default 0            |
| average_prep_time       | integer      | Minutes              |
| created_at / updated_at | timestamp    |                      |
| deleted_at              | timestamp    | Nullable, SoftDelete |

**categories** (SoftDeletes)

| Column                  | Type      | Notes                                                   |
| ----------------------- | --------- | ------------------------------------------------------- |
| id                      | uuid      | Primary key                                             |
| restaurant_id           | uuid      | Foreign key -> restaurants                              |
| name                    | string    |                                                         |
| slug                    | string    | Unique per restaurant (composite: restaurant_id + slug) |
| description             | text      | Nullable                                                |
| sort_order              | integer   | Default 0                                               |
| is_active               | boolean   | Default true                                            |
| created_at / updated_at | timestamp |                                                         |
| deleted_at              | timestamp | Nullable, SoftDelete                                    |

**menu_items** (SoftDeletes)

| Column                  | Type         | Notes                                                   |
| ----------------------- | ------------ | ------------------------------------------------------- |
| id                      | uuid         | Primary key                                             |
| restaurant_id           | uuid         | Foreign key -> restaurants                              |
| category_id             | uuid         | Foreign key -> categories                               |
| name                    | string       |                                                         |
| slug                    | string       | Unique per restaurant (composite: restaurant_id + slug) |
| description             | text         | Nullable                                                |
| price                   | decimal(8,2) |                                                         |
| image                   | string       | Nullable, file path                                     |
| is_available            | boolean      | Default true                                            |
| preparation_time        | integer      | Minutes, nullable                                       |
| created_at / updated_at | timestamp    |                                                         |
| deleted_at              | timestamp    | Nullable, SoftDelete                                    |

**orders**

| Column                  | Type          | Notes                                   |
| ----------------------- | ------------- | --------------------------------------- |
| id                      | uuid          | Primary key                             |
| user_id                 | uuid          | Foreign key -> users (customer)         |
| restaurant_id           | uuid          | Foreign key -> restaurants              |
| driver_id               | uuid          | Nullable, foreign key -> users (driver) |
| status                  | enum          | See OrderStatus enum                    |
| delivery_address        | string        | Snapshot of address at order time       |
| delivery_latitude       | decimal(10,7) | Nullable                                |
| delivery_longitude      | decimal(10,7) | Nullable                                |
| subtotal                | decimal(10,2) |                                         |
| delivery_fee            | decimal(8,2)  |                                         |
| total                   | decimal(10,2) |                                         |
| payment_method          | enum          | cash_on_delivery, polar                 |
| payment_status          | enum          | pending, paid, failed, refunded         |
| polar_checkout_id       | string        | Nullable, Polar checkout session ID     |
| polar_order_id          | string        | Nullable, Polar order ID                |
| notes                   | text          | Nullable, customer notes                |
| estimated_delivery_at   | timestamp     | Nullable                                |
| delivered_at            | timestamp     | Nullable                                |
| cancelled_at            | timestamp     | Nullable                                |
| created_at / updated_at | timestamp     |                                         |

**order_items**

| Column                  | Type          | Notes                           |
| ----------------------- | ------------- | ------------------------------- |
| id                      | uuid          | Primary key                     |
| order_id                | uuid          | Foreign key -> orders           |
| menu_item_id            | uuid          | Foreign key -> menu_items       |
| quantity                | integer       |                                 |
| unit_price              | decimal(8,2)  | Snapshot of price at order time |
| total_price             | decimal(10,2) | quantity \* unit_price          |
| special_instructions    | text          | Nullable                        |
| created_at / updated_at | timestamp     |                                 |

**reviews**

| Column                  | Type      | Notes                           |
| ----------------------- | --------- | ------------------------------- |
| id                      | uuid      | Primary key                     |
| user_id                 | uuid      | Foreign key -> users (customer) |
| order_id                | uuid      | Foreign key -> orders, unique   |
| restaurant_id           | uuid      | Foreign key -> restaurants      |
| driver_id               | uuid      | Nullable, foreign key -> users  |
| restaurant_rating       | tinyint   | 1-5                             |
| driver_rating           | tinyint   | 1-5, nullable                   |
| comment                 | text      | Nullable                        |
| created_at / updated_at | timestamp |                                 |

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

| Enum            | Values                                                                                                              |
| --------------- | ------------------------------------------------------------------------------------------------------------------- |
| `UserRole`      | `Admin`, `User`, `Driver`                                                                                           |
| `OrderStatus`   | `Pending`, `Confirmed`, `Preparing`, `ReadyForPickup`, `Assigned`, `PickedUp`, `OnTheWay`, `Delivered`, `Cancelled` |
| `PaymentMethod` | `CashOnDelivery`, `Polar`                                                                                           |
| `PaymentStatus` | `Pending`, `Paid`, `Failed`, `Refunded`                                                                             |
| `VehicleType`   | `Bicycle`, `Motorcycle`, `Car`                                                                                      |

---

## Payment Integration

The application supports two payment methods. The customer chooses at checkout.

### Payment Methods Overview

| Method               | How It Works                                                                                          | Payment Status Flow           |
| -------------------- | ----------------------------------------------------------------------------------------------------- | ----------------------------- |
| **Polar.sh**         | Customer is redirected to Polar's hosted checkout (or embedded checkout). Webhook confirms payment.   | Pending -> Paid (via webhook) |
| **Cash on Delivery** | No online payment. Driver collects cash upon delivery. Driver marks payment as collected on delivery. | Pending -> Paid (on delivery) |

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
  -> Normal order lifecycle continues (Admin confirms, prepares, etc.)
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
  -> Admin confirms order -> Driver assigned -> Driver picks up -> Driver delivers
  -> Driver marks delivery as complete
  -> App automatically sets payment_status -> PAID (COD collected)
  -> OrderDelivered event dispatched
```

**COD-Specific Rules:**

- No upfront payment is required; the order enters the lifecycle immediately
- The driver collects the exact `total` amount in cash upon delivery
- Payment status transitions to `Paid` only when the order status reaches `Delivered`
- If the order is cancelled before delivery, payment_status stays `Pending` (no money exchanged)
- Admin dashboard shows COD orders with a distinct badge so staff can track cash collection

---

## System Flows

### Order Lifecycle

```
Customer places order (from cart -> checkout -> confirm)
  -> If payment_method == POLAR:
       -> Customer is redirected to Polar checkout
       -> Polar webhook confirms payment (payment_status: PAID)
       -> Order status: PENDING
       -> Event: OrderPlaced (Admin notified via broadcast)
  -> If payment_method == COD:
       -> No external payment step
       -> Order status: PENDING
       -> Event: OrderPlaced (Admin notified via broadcast)

Admin confirms order
  -> Order status: CONFIRMED
  -> Event: OrderStatusUpdated (Customer notified)

Admin marks restaurant as preparing
  -> Order status: PREPARING

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

### Order Status Transition Rules

Only the following transitions are valid:

```
PENDING       -> CONFIRMED, CANCELLED
CONFIRMED     -> PREPARING, CANCELLED
PREPARING     -> READY_FOR_PICKUP, CANCELLED
READY_FOR_PICKUP -> ASSIGNED, CANCELLED
ASSIGNED      -> PICKED_UP, CANCELLED
PICKED_UP     -> ON_THE_WAY
ON_THE_WAY    -> DELIVERED
DELIVERED     -> (terminal state)
CANCELLED     -> (terminal state)
```

**Cancellation rules:**

- **Customer** can cancel when status is `Pending` or `Confirmed` only.
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

---

## Broadcasting Events

| Event                   | Channel                     | Payload                   | Listeners                   |
| ----------------------- | --------------------------- | ------------------------- | --------------------------- |
| `OrderPlaced`           | `private-admin-orders`      | order details             | Admin dashboard             |
| `OrderStatusUpdated`    | `private-orders.{orderId}`  | order, new status         | Customer tracking page      |
| `OrderPaid`             | `private-orders.{orderId}`  | order, payment method     | Customer tracking page      |
| `DriverLocationUpdated` | `private-orders.{orderId}`  | latitude, longitude       | Customer map                |
| `NewDeliveryAvailable`  | `private-drivers`           | order summary             | Available drivers dashboard |
| `OrderAssignedToDriver` | `private-driver.{driverId}` | order, restaurant details | Specific driver             |

---

## Folder Structure (Action Pattern)

```
app/
├── Actions/
│   ├── Auth/
│   │   ├── CreateNewUser.php
│   │   └── UpdateUserProfile.php
│   ├── Restaurant/
│   │   ├── CreateRestaurant.php
│   │   ├── UpdateRestaurant.php
│   │   ├── DeleteRestaurant.php
│   │   └── ToggleRestaurantStatus.php
│   ├── Menu/
│   │   ├── CreateCategory.php
│   │   ├── UpdateCategory.php
│   │   ├── DeleteCategory.php
│   │   ├── CreateMenuItem.php
│   │   ├── UpdateMenuItem.php
│   │   ├── DeleteMenuItem.php
│   │   └── ToggleMenuItemAvailability.php
│   ├── Order/
│   │   ├── PlaceOrder.php
│   │   ├── ConfirmOrder.php
│   │   ├── CancelOrder.php
│   │   ├── UpdateOrderStatus.php
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
│   ├── OrderStatus.php
│   ├── PaymentMethod.php
│   ├── PaymentStatus.php
│   └── VehicleType.php
│
├── Events/
│   ├── OrderPlaced.php
│   ├── OrderStatusUpdated.php
│   ├── OrderPaid.php
│   ├── DriverLocationUpdated.php
│   ├── NewDeliveryAvailable.php
│   └── OrderAssignedToDriver.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── RestaurantController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── MenuItemController.php
│   │   │   ├── OrderController.php
│   │   │   ├── UserController.php
│   │   │   └── DriverController.php
│   │   ├── Customer/
│   │   │   ├── RestaurantController.php
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
│       │   ├── StoreRestaurantRequest.php
│       │   ├── UpdateRestaurantRequest.php
│       │   ├── StoreCategoryRequest.php
│       │   ├── UpdateCategoryRequest.php
│       │   ├── StoreMenuItemRequest.php
│       │   └── UpdateMenuItemRequest.php
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
│   ├── DriverMap.php
│   ├── Admin/
│   │   ├── OrderBoard.php
│   │   └── DashboardStats.php
│   └── Driver/
│       ├── AvailableOrders.php
│       └── DeliveryStatus.php
│
├── Models/
│   ├── User.php
│   ├── Address.php
│   ├── DriverProfile.php
│   ├── Restaurant.php
│   ├── Category.php
│   ├── MenuItem.php
│   ├── Order.php
│   ├── OrderItem.php
│   └── Review.php
│
├── Notifications/
│   ├── OrderConfirmedNotification.php
│   ├── OrderReadyNotification.php
│   ├── DeliveryCompletedNotification.php
│   └── NewOrderNotification.php
│
├── Policies/
│   ├── OrderPolicy.php
│   ├── RestaurantPolicy.php
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
    └── DeliveryService.php

resources/
├── css/
│   └── app.css
├── js/
│   └── app.js
└── views/
    ├── layouts/
    │   ├── app.blade.php              (Customer layout, includes @polarEmbedScript)
    │   ├── admin.blade.php            (Admin layout)
    │   ├── driver.blade.php           (Driver layout)
    │   └── guest.blade.php            (Unauthenticated layout)
    ├── components/
    │   ├── nav-link.blade.php
    │   ├── button.blade.php
    │   ├── input.blade.php
    │   ├── card.blade.php
    │   ├── badge.blade.php
    │   ├── modal.blade.php
    │   └── rating-stars.blade.php
    ├── auth/
    │   ├── login.blade.php
    │   ├── register.blade.php
    │   ├── forgot-password.blade.php
    │   ├── reset-password.blade.php
    │   └── verify-email.blade.php
    ├── admin/
    │   ├── dashboard.blade.php
    │   ├── restaurants/
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
    │   └── drivers/
    │       ├── index.blade.php
    │       └── show.blade.php
    ├── customer/
    │   ├── restaurants/
    │   │   ├── index.blade.php
    │   │   └── show.blade.php
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
        ├── driver-map.blade.php
        ├── admin/
        │   ├── order-board.blade.php
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
GET  /restaurants                       Browse restaurants
GET  /restaurants/{restaurant:slug}     View restaurant menu
```

### Authentication Routes (Fortify)

```
GET|POST  /login                        Login
GET|POST  /register                     Register
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
GET     /orders/{order}                 Track order (Livewire order tracker)
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

### Admin Routes (auth + role:admin, prefix: /admin)

```
GET     /admin/dashboard                Admin dashboard

GET     /admin/restaurants              List restaurants
POST    /admin/restaurants              Create restaurant
GET     /admin/restaurants/create       Create form
GET     /admin/restaurants/{restaurant} Show restaurant
GET     /admin/restaurants/{restaurant}/edit  Edit form
PUT     /admin/restaurants/{restaurant} Update restaurant
DELETE  /admin/restaurants/{restaurant} Soft delete restaurant

GET     /admin/restaurants/{restaurant}/categories          List categories
POST    /admin/restaurants/{restaurant}/categories          Create category
GET     /admin/restaurants/{restaurant}/categories/create   Create form
GET     /admin/restaurants/{restaurant}/categories/{category}/edit  Edit form
PUT     /admin/restaurants/{restaurant}/categories/{category}      Update
DELETE  /admin/restaurants/{restaurant}/categories/{category}      Soft delete

GET     /admin/restaurants/{restaurant}/menu-items          List items
POST    /admin/restaurants/{restaurant}/menu-items          Create item
GET     /admin/restaurants/{restaurant}/menu-items/create   Create form
GET     /admin/restaurants/{restaurant}/menu-items/{menuItem}/edit  Edit form
PUT     /admin/restaurants/{restaurant}/menu-items/{menuItem}      Update
DELETE  /admin/restaurants/{restaurant}/menu-items/{menuItem}      Soft delete

GET     /admin/orders                   List all orders
GET     /admin/orders/{order}           View order details
PUT     /admin/orders/{order}/status    Update order status
PUT     /admin/orders/{order}/assign-driver  Assign driver to order

GET     /admin/users                    List users
GET     /admin/drivers                  List drivers
GET     /admin/drivers/{driver}         View driver profile
PUT     /admin/drivers/{driver}/verify  Verify/unverify driver
```

### Driver Routes (auth + role:driver, prefix: /driver)

```
GET     /driver/dashboard               Driver dashboard (available orders)
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

| Service           | Responsibility                                                                                                                                              |
| ----------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `CartService`     | Session-based cart management. Add/remove items, calculate totals, clear cart. Scoped per restaurant (switching restaurant clears the cart with a warning). |
| `OrderService`    | Order lifecycle management. Creates order from cart, validates status transitions (see transition rules above), dispatches events on status change.         |
| `PaymentService`  | Payment method orchestration. Creates Polar checkout sessions for digital payment. Handles COD payment marking on delivery. Updates payment_status.         |
| `DeliveryService` | Driver assignment logic with DB-level locking to prevent race conditions. Delivery time estimation.                                                         |

---

## Middleware

| Middleware          | Alias  | Purpose                                                                          |
| ------------------- | ------ | -------------------------------------------------------------------------------- |
| `EnsureUserHasRole` | `role` | Checks authenticated user's role against allowed roles. Returns 403 on mismatch. |

**Usage in routes:**

```php
->middleware('role:admin')
->middleware('role:user')
->middleware('role:driver')
->middleware('role:admin,driver')  // multiple roles allowed
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

| Policy                | Model           | Key Rules                                                                    |
| --------------------- | --------------- | ---------------------------------------------------------------------------- |
| `OrderPolicy`         | `Order`         | Customer views own orders only. Admin views all. Driver views assigned only. |
| `RestaurantPolicy`    | `Restaurant`    | Admin only for create/update/delete. Public for view.                        |
| `ReviewPolicy`        | `Review`        | Customer creates review for own delivered orders only. One review per order. |
| `AddressPolicy`       | `Address`       | Customer manages own addresses only.                                         |
| `DriverProfilePolicy` | `DriverProfile` | Driver manages own profile only. Admin can view/verify all.                  |

---

## Testing Strategy

Tests are written with Pest v4. All models have factories with relevant states.

### Test Directory Structure

```
tests/
├── Feature/
│   ├── Actions/
│   │   ├── Auth/
│   │   ├── Restaurant/
│   │   ├── Menu/
│   │   ├── Order/
│   │   ├── Payment/
│   │   ├── Driver/
│   │   └── Review/
│   ├── Http/
│   │   ├── Admin/
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
- Service class unit tests (CartService, OrderService, PaymentService, DeliveryService)
- Order status transition validation tests (valid + invalid transitions)
- Payment flow tests:
    - Polar checkout creation and redirect
    - Polar webhook signature verification and payload handling
    - COD payment status transition on delivery completion
    - Payment method validation at checkout
- Factories with states: `unverified`, `admin`, `driver`, `available`, `verified`, `delivered`, `cancelled`, `paid_with_polar`, `paid_with_cod`, etc.
