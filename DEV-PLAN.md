# EcomApp - Development Plan (1 Week)

> Daily development goals organized by feature. Each day builds on the previous day's work.
> Every feature includes its models, migrations, actions, controllers, views, services, and tests.

## Prerequisites

Before starting, install required packages:

```bash
composer require livewire/livewire laravel/fortify danestves/laravel-polar
php artisan fortify:install
php artisan polar:install
```

---

## Day 1 — Foundation & Authentication

**Goal:** Set up the entire database layer, all enums, all models with relationships and factories, authentication system, middleware, and base layouts.

### Feature 1: Database Foundation

All migrations, models, enums, and factories. This is the backbone everything else depends on.

**Enums:**

- [ ] `UserRole` — Admin, User, Seller, Driver
- [ ] `StoreType` — Restaurant, Shop
- [ ] `OrderType` — FoodDelivery, ProductOrder
- [ ] `OrderStatus` — Pending, Confirmed, Processing, ReadyForPickup, Shipped, Assigned, PickedUp, OnTheWay, Delivered, Cancelled
- [ ] `FulfillmentMethod` — PlatformDelivery, SellerShipping
- [ ] `PaymentMethod` — CashOnDelivery, Polar
- [ ] `PaymentStatus` — Pending, Paid, Failed, Refunded
- [ ] `VehicleType` — Bicycle, Motorcycle, Car

**Migrations (in order):**

- [ ] Extend `users` table — add `phone`, `avatar`, `role` columns
- [ ] Create `addresses` table
- [ ] Create `driver_profiles` table
- [ ] Create `stores` table
- [ ] Create `categories` table
- [ ] Create `menu_items` table
- [ ] Create `products` table
- [ ] Create `product_variants` table
- [ ] Create `product_images` table
- [ ] Create `orders` table
- [ ] Create `order_items` table
- [ ] Create `reviews` table
- [ ] Create `notifications` table (`php artisan notifications:table`)

**Models (with relationships, casts, factories):**

- [ ] Update `User` model — add role cast, relationships (orders, addresses, reviews, stores, driverProfile)
- [ ] Create `Address` model + factory
- [ ] Create `DriverProfile` model + factory (states: `available`, `verified`)
- [ ] Create `Store` model + factory (states: `restaurant`, `shop`, `approved`, `inactive`)
- [ ] Create `Category` model + factory
- [ ] Create `MenuItem` model + factory
- [ ] Create `Product` model + factory (states: `withVariants`, `sellerShipping`, `platformDelivery`)
- [ ] Create `ProductVariant` model + factory
- [ ] Create `ProductImage` model + factory
- [ ] Create `Order` model + factory (states: `foodDelivery`, `productOrder`, `delivered`, `cancelled`, `shipped`, `paidWithPolar`, `paidWithCod`, `sellerShipping`, `platformDelivery`)
- [ ] Create `OrderItem` model + factory
- [ ] Create `Review` model + factory

**Seeders:**

- [ ] `DatabaseSeeder` — create admin user, sample data for development

**Event Stubs:**

- [ ] Create all 8 event class stubs (empty classes with correct channels/payload signatures): `OrderPlaced`, `OrderStatusUpdated`, `OrderPaid`, `OrderShipped`, `DriverLocationUpdated`, `NewDeliveryAvailable`, `OrderAssignedToDriver`, `StoreApproved`

> These are created as stubs now so actions on Days 2-6 can dispatch them. Full broadcasting implementation is on Day 7.

**Tests:**

- [ ] Unit tests for all enums (values, labels)
- [ ] Unit tests for all model `toArray()`, relationships, casts
- [ ] Run migrations and verify schema

### Feature 2: Authentication & Authorization

- [ ] Configure Fortify (login, register, password reset, email verification)
- [ ] Update registration to support role selection (Customer or Seller)
- [ ] Create `EnsureUserHasRole` middleware
- [ ] Register middleware alias in `bootstrap/app.php`
- [ ] Exclude `polar/*` from CSRF in `bootstrap/app.php`
- [ ] Create auth views: `login`, `register`, `forgot-password`, `reset-password`, `verify-email`
- [ ] Create `CreateNewUser` action (used by Fortify)
- [ ] Create `UpdateUserProfile` action

**Tests:**

- [ ] Feature test: user registration (customer + seller)
- [ ] Feature test: login / logout
- [ ] Feature test: role middleware (allowed, denied, unauthenticated)
- [ ] Feature test: email verification flow

### Feature 3: Base Layouts & Components

- [ ] Create `guest.blade.php` layout (unauthenticated pages)
- [ ] Create `app.blade.php` layout (customer, includes `@polarEmbedScript`)
- [ ] Create `admin.blade.php` layout
- [ ] Create `seller.blade.php` layout
- [ ] Create `driver.blade.php` layout
- [ ] Create shared Blade components: `nav-link`, `button`, `input`, `card`, `badge`, `modal`

**End of Day 1 Checklist:**

- All 12 models exist with relationships, casts, and factories
- All 8 enums exist
- All migrations run clean
- Auth works (register, login, logout, password reset)
- Role middleware blocks unauthorized access
- 5 layouts render correctly

---

## Day 2 — Store & Menu Management (Admin + Restaurant)

**Goal:** Admin can CRUD stores (restaurants), categories, and menu items. Policies protect access. All views are functional.

### Feature 4: Store Management (Admin)

**Actions:**

- [ ] `CreateStore` — validates, generates slug, creates store
- [ ] `UpdateStore` — validates, updates store fields
- [ ] `DeleteStore` — soft deletes store
- [ ] `ToggleStoreStatus` — toggles `is_active`
- [ ] `ApproveStore` — sets `is_approved = true`, `is_active = true`, dispatches `StoreApproved` event

**Controllers & Requests:**

- [ ] `Admin\StoreController` — index, create, store, show, edit, update, destroy, approve, reject
- [ ] `StoreStoreRequest` — validation rules for creating a store
- [ ] `UpdateStoreRequest` — validation rules for updating a store

**Policy:**

- [ ] `StorePolicy` — admin manages all, seller manages own, public views active+approved

**Views:**

- [ ] `admin/stores/index.blade.php` — list all stores with type badge, active/approved status
- [ ] `admin/stores/create.blade.php` — form with type selector (restaurant/shop) and type-specific fields
- [ ] `admin/stores/edit.blade.php` — edit form
- [ ] `admin/stores/show.blade.php` — store detail with categories and items/products

**Tests:**

- [ ] Feature tests for Admin\StoreController (all CRUD endpoints + approve/reject)
- [ ] Policy tests for StorePolicy
- [ ] Action tests for CreateStore, UpdateStore, DeleteStore, ApproveStore

### Feature 5: Menu Management — Categories & Menu Items (Admin)

**Actions:**

- [ ] `CreateCategory` — validates, generates slug scoped to store
- [ ] `UpdateCategory` — validates, updates category
- [ ] `DeleteCategory` — soft deletes category
- [ ] `CreateMenuItem` — validates, creates menu item under store + category
- [ ] `UpdateMenuItem` — validates, updates menu item
- [ ] `DeleteMenuItem` — soft deletes menu item
- [ ] `ToggleMenuItemAvailability` — toggles `is_available`

**Controllers & Requests:**

- [ ] `Admin\CategoryController` — index, create, store, edit, update, destroy (nested under store)
- [ ] `Admin\MenuItemController` — index, create, store, edit, update, destroy (nested under store)
- [ ] `StoreCategoryRequest`, `UpdateCategoryRequest`
- [ ] `StoreMenuItemRequest`, `UpdateMenuItemRequest`

**Views:**

- [ ] `admin/categories/index.blade.php`, `create.blade.php`, `edit.blade.php`
- [ ] `admin/menu-items/index.blade.php`, `create.blade.php`, `edit.blade.php`

**Tests:**

- [ ] Feature tests for Admin\CategoryController (CRUD, scoped to store)
- [ ] Feature tests for Admin\MenuItemController (CRUD, scoped to store)
- [ ] Action tests for all menu-related actions

**End of Day 2 Checklist:**

- Admin can create/edit/delete stores (restaurants and shops)
- Admin can create/edit/delete categories per store
- Admin can create/edit/delete menu items per restaurant
- Approve/reject workflow for stores works
- All actions have feature tests passing

---

## Day 3 — Product & Seller System (Shop)

**Goal:** Sellers can register, create a shop, manage products with variants and images, and manage stock.

### Feature 6: Seller Store Setup

**Controllers & Views:**

- [ ] `Seller\DashboardController` — seller dashboard (placeholder stats for now)
- [ ] `Seller\StoreController` — create shop, edit shop profile
- [ ] `Seller\ProfileController` — view/edit seller profile
- [ ] `UpdateStoreProfileRequest` — validation for shop profile updates
- [ ] `seller/dashboard.blade.php`
- [ ] `seller/store/create.blade.php` — create shop form
- [ ] `seller/store/edit.blade.php` — edit shop profile form
- [ ] `seller/profile.blade.php`

**Tests:**

- [ ] Feature test: seller creates shop (pending approval)
- [ ] Feature test: seller edits shop profile
- [ ] Feature test: seller cannot access store until approved

### Feature 7: Product Management (Seller)

**Actions:**

- [ ] `CreateProduct` — validates, generates slug, creates product under seller's store
- [ ] `UpdateProduct` — validates, updates product
- [ ] `DeleteProduct` — soft deletes product
- [ ] `ToggleProductAvailability` — toggles `is_available`
- [ ] `CreateProductVariant` — creates variant with attributes, price, stock
- [ ] `UpdateProductVariant` — updates variant
- [ ] `DeleteProductVariant` — deletes variant (adjusts product stock)
- [ ] `ManageProductImages` — upload, reorder, set primary, delete images

**Controllers & Requests:**

- [ ] `Seller\CategoryController` — CRUD categories for seller's shop
- [ ] `Seller\ProductController` — index, create, store, show, edit, update, destroy
- [ ] `Seller\ProductVariantController` — store, update, destroy (nested under product)
- [ ] `StoreProductRequest`, `UpdateProductRequest`
- [ ] `StoreProductVariantRequest`, `UpdateProductVariantRequest`

**Policy:**

- [ ] `ProductPolicy` — seller manages own store's products only, admin can view/flag all

**Views:**

- [ ] `seller/categories/index.blade.php`, `create.blade.php`, `edit.blade.php`
- [ ] `seller/products/index.blade.php` — product list with stock, variant count, status
- [ ] `seller/products/create.blade.php` — create form with image upload, fulfillment method selection
- [ ] `seller/products/edit.blade.php` — edit form with variant management and image management
- [ ] `seller/products/show.blade.php` — product detail with variants, images, stock

### Feature 8: Stock Management

**Service:**

- [ ] `StockService` — decrement stock on order, restore on cancellation, variant-level tracking, low stock checks

**Actions:**

- [ ] `UpdateStock` — manually update stock for a product or variant

**Tests:**

- [ ] Feature tests for Seller\ProductController (full CRUD)
- [ ] Feature tests for Seller\ProductVariantController (create, update, delete)
- [ ] Feature tests for product image upload/delete
- [ ] Policy tests for ProductPolicy
- [ ] Unit tests for StockService (decrement, restore, variant-level)
- [ ] Action tests for all product-related actions

**End of Day 3 Checklist:**

- Seller can register and create a shop (pending approval)
- Seller can CRUD categories within their shop
- Seller can CRUD products with images and fulfillment method
- Seller can manage product variants (size, color) with individual pricing and stock
- StockService correctly tracks inventory at product and variant level
- All actions have tests passing

---

## Day 4 — Customer Browsing & Cart

**Goal:** Customers can browse stores and products, view details with variants/images, and manage a cart.

### Feature 9: Store & Product Browsing (Public + Customer)

**Controllers:**

- [ ] `Customer\StoreController` — index (browse stores), show (view store menu or products)
- [ ] `Customer\ProductController` — index (browse all products), show (product detail)
- [ ] `Customer\ProfileController` — show/edit customer profile

**Views:**

- [ ] `customer/stores/index.blade.php` — browse all stores with type filter (restaurant/shop), search, status
- [ ] `customer/stores/show.blade.php` — view store: menu for restaurant, product listing for shop
- [ ] `customer/products/index.blade.php` — browse all products across shops with filters (category, price, rating)
- [ ] `customer/products/show.blade.php` — product detail with image gallery, variant selector, reviews, add-to-cart
- [ ] `customer/profile.blade.php` — view/edit customer profile

**Blade Components:**

- [ ] `product-card.blade.php` — reusable product card for listings
- [ ] `variant-selector.blade.php` — variant selection UI (dropdowns/buttons for attributes)
- [ ] `image-gallery.blade.php` — product image gallery with thumbnails
- [ ] `rating-stars.blade.php` — star rating display

**Livewire:**

- [ ] `ProductBrowser` component — search, filter, paginate products

**Routes:**

- [ ] Set up public routes: `/stores`, `/restaurants`, `/shops`, `/stores/{store:slug}`, `/products`, `/products/{product:slug}`

**Tests:**

- [ ] Feature test: browse stores (filter by type)
- [ ] Feature test: view restaurant menu
- [ ] Feature test: view shop products
- [ ] Feature test: view product detail with variants
- [ ] Feature test: public routes accessible without auth

### Feature 10: Cart System

**Service:**

- [ ] `CartService` — session-based, per-store scoping, add/remove menu items or product variants, quantity management, total calculation, clear cart, store-switch warning

**Livewire:**

- [ ] `Cart` component — add items, update quantity, remove items, show totals, link to checkout

**Controllers:**

- [ ] `Customer\CartController` — show cart page

**Views:**

- [ ] `customer/cart.blade.php` — cart page (wraps Livewire Cart component)
- [ ] `livewire/cart.blade.php` — interactive cart with item list, quantities, totals

**Tests:**

- [ ] Unit tests for CartService (add, remove, update quantity, clear, per-store scoping, totals)
- [ ] Livewire component test for Cart (add item, update quantity, remove, clear)
- [ ] Feature test: store-switch clears cart with warning

**End of Day 4 Checklist:**

- Public can browse stores (filtered by restaurant/shop)
- Public can browse products with search and filters
- Product detail page shows images, variants, and reviews
- Cart works with both menu items and product variants
- Cart is scoped per-store (switching store clears cart)
- CartService has full unit test coverage

---

## Day 5 — Checkout, Orders & Payment

**Goal:** Customers can checkout, place orders (food or product), and pay via Polar or COD. Orders are created with correct type and fulfillment method.

### Feature 11: Checkout & Order Placement

**Actions:**

- [ ] `PlaceOrder` — creates order from cart, snapshots item prices, sets order type and fulfillment method, decrements stock, clears cart, dispatches `OrderPlaced` event

**Service:**

- [ ] `OrderService` — order creation from cart, status transition validation per order type, event dispatching on status change

**Controllers & Requests:**

- [ ] `Customer\CheckoutController` — show checkout page (address selection, payment method, notes)
- [ ] `Customer\OrderController` — store (place order), index (order history), show (track order)
- [ ] `PlaceOrderRequest` — validation (address, payment method, cart not empty)

**Policy:**

- [ ] `OrderPolicy` — customer views own orders, seller views own store's orders, admin views all, driver views assigned only

**Views:**

- [ ] `customer/checkout.blade.php` — checkout page (address picker, payment method radio, order notes, order summary, confirm button)
- [ ] `customer/orders/index.blade.php` — order history with status badges, type badges
- [ ] `customer/orders/show.blade.php` — order detail with status timeline, tracking info

**Routes:**

- [ ] Customer routes: `/cart`, `/checkout`, `POST /orders`, `/orders`, `/orders/{order}`

**Tests:**

- [ ] Feature test: place food delivery order (COD)
- [ ] Feature test: place food delivery order (Polar)
- [ ] Feature test: place product order with seller shipping (Polar only)
- [ ] Feature test: place product order with platform delivery (COD + Polar)
- [ ] Feature test: stock decremented on order placement
- [ ] Feature test: COD rejected for seller-shipped products
- [ ] Unit test: OrderService status transition validation (valid + invalid per order type)

### Feature 12: Payment Integration

**Actions:**

- [ ] `CreatePolarCheckout` — creates Polar checkout session with metadata, success URL
- [ ] `HandlePolarWebhook` — matches webhook to internal order, updates payment_status, stores Polar IDs

**Service:**

- [ ] `PaymentService` — orchestrates Polar checkout creation, COD rules enforcement, payment status updates

**Listener:**

- [ ] `HandlePolarWebhookEvent` — listens for Polar webhook events

**Setup:**

- [ ] Configure Polar environment variables
- [ ] Add `Billable` trait to User model
- [ ] Set up webhook route

**Routes:**

- [ ] `GET /orders/{order}/payment-success` — Polar redirect-back
- [ ] `POST /polar/webhook` — Polar webhook endpoint

**Tests:**

- [ ] Feature test: Polar checkout creation and redirect
- [ ] Feature test: Polar webhook processes payment (order.created)
- [ ] Feature test: Polar webhook handles refund (order.updated)
- [ ] Feature test: COD order skips payment step
- [ ] Feature test: COD not available for seller-shipped orders
- [ ] Unit test: PaymentService methods

**End of Day 5 Checklist:**

- Customers can complete checkout with address and payment method
- Food delivery orders created with PlatformDelivery fulfillment
- Product orders created with correct fulfillment method (SellerShipping or PlatformDelivery)
- Polar checkout redirects and webhook processes payment
- COD orders proceed without payment
- COD is blocked for seller-shipped product orders
- Stock is decremented on order placement
- Order history shows all orders with correct type and status

---

## Day 6 — Order Fulfillment & Driver System

**Goal:** Sellers fulfill product orders (confirm, ship). Admin manages food orders. Drivers accept and complete deliveries.

### Feature 13: Seller Order Fulfillment

**Actions:**

- [ ] `ConfirmOrder` — seller/admin confirms order, transitions to Confirmed
- [ ] `UpdateOrderStatus` — generic status updater with transition validation
- [ ] `ShipOrder` — seller ships order, sets tracking number and shipped_at
- [ ] `CancelOrder` — cancels order with cancellation rules, restores stock

**Controllers & Requests:**

- [ ] `Seller\OrderController` — index (incoming orders), show, confirm, process, ship, ready, cancel
- [ ] `ShipOrderRequest` — validation (tracking_number required)

**Views:**

- [ ] `seller/orders/index.blade.php` — incoming orders with status filters, fulfillment type badges
- [ ] `seller/orders/show.blade.php` — order detail with action buttons (confirm, process, ship/ready, cancel)

**Tests:**

- [ ] Feature test: seller confirms order
- [ ] Feature test: seller ships order with tracking number
- [ ] Feature test: seller marks order ready for platform pickup
- [ ] Feature test: seller cancels order (stock restored)
- [ ] Feature test: seller cannot access other store's orders
- [ ] Feature test: invalid status transitions are rejected

### Feature 14: Admin Order Management

**Controllers:**

- [ ] `Admin\OrderController` — index (all orders), show, update status, assign driver
- [ ] `Admin\UserController` — index (list users)
- [ ] `Admin\SellerController` — index (list sellers), show (seller detail + store)
- [ ] `Admin\DriverController` — index (list drivers), show (driver profile), verify

**Actions:**

- [ ] `AssignDriverToOrder` — admin assigns specific driver to order

**Views:**

- [ ] `admin/orders/index.blade.php` — all orders with type/status/fulfillment filters
- [ ] `admin/orders/show.blade.php` — order detail with admin actions
- [ ] `admin/users/index.blade.php` — user list
- [ ] `admin/sellers/index.blade.php`, `show.blade.php` — seller management
- [ ] `admin/drivers/index.blade.php`, `show.blade.php` — driver management with verify toggle

**Tests:**

- [ ] Feature test: admin views all orders
- [ ] Feature test: admin updates order status
- [ ] Feature test: admin assigns driver
- [ ] Feature test: admin verifies/unverifies driver

### Feature 15: Driver System & Delivery

**Actions:**

- [ ] `AcceptDelivery` — driver accepts order (DB lock to prevent race condition)
- [ ] `UpdateDriverLocation` — updates driver lat/lng
- [ ] `CompleteDelivery` — marks delivery complete, sets payment_status for COD
- [ ] `ToggleDriverAvailability` — toggles online/offline

**Service:**

- [ ] `DeliveryService` — driver assignment with DB locking, delivery time estimation

**Controllers & Requests:**

- [ ] `Driver\DashboardController` — available orders for delivery
- [ ] `Driver\DeliveryController` — index (history), show, accept, update status
- [ ] `Driver\ProfileController` — view/edit driver profile
- [ ] `UpdateLocationRequest` — validation (latitude, longitude)

**Policy:**

- [ ] `DriverProfilePolicy` — driver manages own profile, admin views/verifies all

**Views:**

- [ ] `driver/dashboard.blade.php` — available orders (food + platform-delivered products)
- [ ] `driver/deliveries/index.blade.php` — delivery history
- [ ] `driver/deliveries/show.blade.php` — delivery detail with action buttons
- [ ] `driver/profile.blade.php` — edit profile (vehicle type, plate, license)

**Tests:**

- [ ] Feature test: driver accepts delivery (race condition handled)
- [ ] Feature test: driver updates delivery status flow (PickedUp -> OnTheWay -> Delivered)
- [ ] Feature test: COD payment marked as paid on delivery completion
- [ ] Feature test: driver toggles availability
- [ ] Feature test: driver cannot accept when unavailable/unverified
- [ ] Policy tests for DriverProfilePolicy
- [ ] Unit test: DeliveryService assignment logic

**End of Day 6 Checklist:**

- Seller can confirm, process, and ship product orders with tracking numbers
- Seller can mark platform-delivery orders as ready for pickup
- Admin can manage all orders, assign drivers, update statuses
- Admin can manage users, sellers, and drivers
- Drivers can view available orders, accept deliveries, update status, complete delivery
- COD payment is automatically marked as paid when driver completes delivery
- Stock is restored on order cancellation
- Driver assignment handles race conditions with DB locking

---

## Day 7 — Reviews, Real-time, Dashboards & Polish

**Goal:** Complete the review system, set up real-time broadcasting, build dashboard stats, and polish the entire application.

### Feature 16: Review System

**Actions:**

- [ ] `CreateReview` — validates one review per order, creates review for store/product + optional driver rating

**Controllers & Requests:**

- [ ] `Customer\ReviewController` — store review
- [ ] `StoreReviewRequest` — validation (rating 1-5, comment optional, driver_rating optional)

**Policy:**

- [ ] `ReviewPolicy` — customer reviews own delivered orders only, one per order

**Views:**

- [ ] Review form on `customer/orders/show.blade.php` (after delivery)
- [ ] Reviews display on store and product detail pages

**Tests:**

- [ ] Feature test: customer submits review for delivered order
- [ ] Feature test: customer cannot review twice for same order
- [ ] Feature test: customer cannot review undelivered order
- [ ] Policy tests for ReviewPolicy

### Feature 17: Address Management

**Controllers & Requests:**

- [ ] `Customer\AddressController` — index, create, store, edit, update, destroy
- [ ] `StoreAddressRequest`, `UpdateAddressRequest`

**Policy:**

- [ ] `AddressPolicy` — customer manages own addresses only

**Views:**

- [ ] `customer/addresses/index.blade.php`, `create.blade.php`, `edit.blade.php`

**Tests:**

- [ ] Feature test: customer CRUD addresses
- [ ] Policy tests for AddressPolicy

### Feature 18: Real-time Broadcasting

**Events:**

- [ ] `OrderPlaced` — broadcasts to admin + seller channels
- [ ] `OrderStatusUpdated` — broadcasts to customer order channel
- [ ] `OrderPaid` — broadcasts to customer order channel
- [ ] `OrderShipped` — broadcasts to customer order channel
- [ ] `DriverLocationUpdated` — broadcasts to customer order channel
- [ ] `NewDeliveryAvailable` — broadcasts to drivers channel
- [ ] `OrderAssignedToDriver` — broadcasts to specific driver channel
- [ ] `StoreApproved` — broadcasts to seller channel

**Channel Authorization:**

- [ ] `channels.php` — authorize `private-admin-orders`, `private-seller.{sellerId}`, `private-orders.{orderId}`, `private-drivers`, `private-driver.{driverId}`

**Livewire Components:**

- [ ] `OrderTracker` — real-time order status updates for customer
- [ ] `DriverMap` — real-time driver location on map
- [ ] `Admin\OrderBoard` — live order board with status columns
- [ ] `Admin\DashboardStats` — live dashboard statistics
- [ ] `Seller\OrderManager` — live incoming order management
- [ ] `Seller\StockManager` — stock level management interface
- [ ] `Seller\DashboardStats` — seller dashboard statistics
- [ ] `Driver\AvailableOrders` — live available orders for drivers
- [ ] `Driver\DeliveryStatus` — active delivery status management

**Views:**

- [ ] `livewire/order-tracker.blade.php`
- [ ] `livewire/driver-map.blade.php`
- [ ] `livewire/admin/order-board.blade.php`, `dashboard-stats.blade.php`
- [ ] `livewire/seller/order-manager.blade.php`, `stock-manager.blade.php`, `dashboard-stats.blade.php`
- [ ] `livewire/driver/available-orders.blade.php`, `delivery-status.blade.php`

**Tests:**

- [ ] Unit tests for all event classes (correct channels, payload)
- [ ] Feature tests for channel authorization
- [ ] Livewire component tests for all interactive components

### Feature 19: Admin Dashboard & Management

**Controllers:**

- [ ] `Admin\DashboardController` — dashboard with aggregated stats

**Views:**

- [ ] `admin/dashboard.blade.php` — orders today, revenue, active drivers, pending orders, active stores, recent orders

**Notifications:**

- [ ] `OrderConfirmedNotification`
- [ ] `OrderReadyNotification`
- [ ] `OrderShippedNotification`
- [ ] `DeliveryCompletedNotification`
- [ ] `NewOrderNotification`
- [ ] `StoreApprovedNotification`

### Feature 20: Final Testing & Polish

- [ ] Run full test suite (`php artisan test --compact`)
- [ ] Fix any failing tests
- [ ] Run Pint for code formatting (`vendor/bin/pint --dirty --format agent`)
- [ ] Run Larastan for static analysis
- [ ] Verify all routes work end-to-end
- [ ] Verify database seeder creates usable development data
- [ ] Review all views for consistent styling and responsive design
- [ ] Confirm order lifecycle flows work for all 3 types (food delivery, product self-ship, product platform delivery)
- [ ] Confirm payment flows work (Polar + COD with enforcement rules)

**End of Day 7 Checklist:**

- Review system works (per-order, one per order, for delivered orders only)
- Address management works (CRUD, customer-scoped)
- Broadcasting events fire on all status changes
- All Livewire components render and update in real-time
- Admin dashboard shows platform-wide statistics
- Seller dashboard shows store-specific statistics
- Notifications sent on key order events
- Full test suite passes
- Code is formatted and passes static analysis

---

## Feature-to-Day Mapping

| #   | Feature                                                              | Day   |
| --- | -------------------------------------------------------------------- | ----- |
| 1   | Database Foundation (enums, migrations, models, factories)           | Day 1 |
| 2   | Authentication & Authorization (Fortify, middleware, role selection) | Day 1 |
| 3   | Base Layouts & Blade Components                                      | Day 1 |
| 4   | Store Management — Admin CRUD                                        | Day 2 |
| 5   | Menu Management — Categories & Menu Items (Admin)                    | Day 2 |
| 6   | Seller Store Setup (registration, create shop, approval)             | Day 3 |
| 7   | Product Management — Products, Variants, Images (Seller)             | Day 3 |
| 8   | Stock Management (StockService)                                      | Day 3 |
| 9   | Store & Product Browsing (public + customer)                         | Day 4 |
| 10  | Cart System (CartService + Livewire)                                 | Day 4 |
| 11  | Checkout & Order Placement (OrderService)                            | Day 5 |
| 12  | Payment Integration (Polar + COD + PaymentService)                   | Day 5 |
| 13  | Seller Order Fulfillment (confirm, ship, ready)                      | Day 6 |
| 14  | Admin Order Management (status updates, driver assignment)           | Day 6 |
| 15  | Driver System & Delivery (DeliveryService, accept, deliver)          | Day 6 |
| 16  | Review System                                                        | Day 7 |
| 17  | Address Management                                                   | Day 7 |
| 18  | Real-time Broadcasting (events, channels, Livewire)                  | Day 7 |
| 19  | Admin Dashboard & Notifications                                      | Day 7 |
| 20  | Final Testing & Polish                                               | Day 7 |

---

## Daily Summary

| Day   | Theme                       | Key Deliverables                                                      |
| ----- | --------------------------- | --------------------------------------------------------------------- |
| **1** | Foundation & Auth           | 8 enums, 13 migrations, 12 models+factories, auth system, 5 layouts   |
| **2** | Store & Menu (Admin)        | Store CRUD, category CRUD, menu item CRUD, StorePolicy                |
| **3** | Product & Seller (Shop)     | Seller flow, product CRUD, variants, images, StockService             |
| **4** | Browsing & Cart             | Public browsing, product detail, CartService, Cart Livewire           |
| **5** | Checkout & Payment          | Order placement, OrderService, Polar integration, COD, PaymentService |
| **6** | Fulfillment & Drivers       | Seller fulfillment, admin orders, driver system, DeliveryService      |
| **7** | Reviews, Real-time & Polish | Reviews, broadcasting, dashboards, notifications, full test pass      |
