# TourBeez Project Functionality and Technical Review

Last reviewed: 14 August 2026

## 1. Project overview

TourBeez is a tour discovery, booking, payment, and operations platform. It has two applications:

- `tourbeez-front`: the public React single-page application used by customers and partner-branded booking pages.
- `tourbeez-back`: the Laravel application that exposes the public API and provides the authenticated admin/operations portal.

The normal lifecycle is:

1. A visitor discovers or searches for a tour.
2. The visitor opens a tour, selects a date/time, passenger quantities, pickup, and add-ons.
3. The API creates an order/cart snapshot, including the selected currency and exchange rate.
4. Checkout collects customer details, applies eligible discounts, promo codes, deposits, fees, and taxes.
5. Stripe authorizes or collects the required amount.
6. The backend verifies the payment and Stripe webhooks reconcile the final order/payment state.
7. Staff manage the booking, customer, payment, driver, manifest, communication, and reporting lifecycle in admin.

## 2. Technology and application architecture

### Public frontend

- React 18 and React Router 7
- Vite 6 build system
- Redux Toolkit for authentication, session, location, wishlist, and currency state
- Axios-based API client with an API-key header and optional Sanctum bearer token
- Stripe Elements for payment collection
- Tailwind CSS plus application CSS
- Google Maps, Google reCAPTCHA, Google Analytics, Trustpilot widgets, Swiper/lightbox, and print support
- Lazy-loaded route pages to reduce the initial bundle cost

### Backend and admin

- Laravel 10 on PHP 8.1+
- Blade/AdminLTE admin interface
- Laravel Sanctum for frontend API authentication
- Laravel Breeze, Socialite, and OTP login support for backend authentication paths
- Spatie permission/role and activity-log packages
- Stripe PHP SDK for intents, captures, saved cards, refunds, and webhooks
- Mailgun/Symfony mailer and Twilio SMS
- Laravel Excel for imports and reports; DomPDF and QR Code for printable documents
- S3-compatible storage and Intervention Image for media
- Redis client support and Laravel scheduled commands

### Main domain areas

- Catalog: tours, parent/sub-tours, categories, tour types, collections, locations, images, and SEO.
- Availability: schedules, recurring days/times, disabled dates/slots, capacity, notice periods, and last-minute rules.
- Merchandising: passenger pricing, add-ons, pickups, inclusions, exclusions, optional items, features, FAQs, itinerary, and reviews.
- Commerce: cart/order, customer snapshot, order tours, order metadata, taxes/fees, special discounts, promo codes, vouchers, deposits, payments, refunds, and balances.
- Operations: users, customers, suppliers, drivers, vehicles, manifests, notifications, communications, expenses, and reports.
- Distribution: partners and partner-specific tour/checkout routes.

## 3. Public frontend functionality

### Global application behavior

- Restores the logged-in user and token from local storage.
- Detects a visitor currency when no explicit choice exists, persists the selected currency, and downloads conversion rates.
- Uses a shared `Price` component and currency utilities for display conversion.
- Captures page views through the Google Analytics hook.
- Provides shared header, footer, partner logos, trusted-site banner, location detection, Trustpilot reviews, alerts, loaders, modals, SEO metadata, and scroll restoration.
- Switches to a partner/company header and reduced chrome for company booking and iframe routes.

### Home and discovery

- Home content is loaded from `/home-listing` and includes hero/search content, destinations, cities, tour cards, and cancellation messaging.
- Search supports keyword and optional date input with live API results.
- Popular city and destination pages are API-driven and paginated.
- Location/category listing routes use the generic `/:slug/:id` page and a `type` query value to resolve the listing context.
- Tour listing supports category filters, pagination, cards, displayed pricing, and wishlist actions.
- Browsing-history recommendations are loaded from the recommendations API.
- A dedicated 2026 host-city tours landing page is present.

### Tour detail and availability

The tour detail page loads public content and booking information separately. It can show:

- Gallery, overview, breadcrumb, map/location, video, social sharing, and popular badge.
- Features, itinerary, FAQs, inclusions, exclusions, optionals, add-ons, and refund/cancellation information.
- Trustpilot/product reviews, similar tours, and wishlist controls.
- Parent/sub-tour alternatives for a selected date.
- Calendar availability, disabled dates, valid sessions, minimum notice, and booking cut-offs.
- Passenger price labels and quantities, pickup choice, add-ons, and the calculated amount.

The booking endpoint is schedule-aware. It resolves recurring schedules, deleted slots, date availability, next available dates, session duration, and minimum notice rules.

### Cart creation

- Selecting quantities/add-ons posts the booking to `/cart/add`.
- The backend creates an order/cart snapshot rather than relying only on values displayed by the browser.
- The snapshot stores the source/base amounts, selected display/payment currency, and conversion rate used for that booking.
- A session ID supports guest checkout; authenticated users are linked to their account.
- Partner/company context is carried into the booking when applicable.

### Checkout

Checkout loads the order by its opaque/session token and provides:

- Tour/date/time and line-item summary.
- Passenger and add-on quantities.
- Customer contact and pickup details.
- Special discount, promo code, taxes/fees, booking fee, deposit/payment amount, subtotal, total, paid amount, and remaining balance.
- Promo-code validation through `/apply-promo-code`.
- Tour-specific/global deposit rules through the deposit-rule endpoint.
- Stripe payment form and optional saved-card/setup behavior.
- Order update before payment intent confirmation, plus a checkout-error endpoint for diagnostic state.

The frontend is responsible for presentation and input, while the backend recalculates and persists authoritative totals.

### Checkout completion

- `/checkout-success` verifies the Stripe result with the backend.
- The success view shows the final persisted invoice-style totals rather than trusting redirect query values.
- A separate success route exists for non-standard completion flows.
- Logged-in customers can view bookings in profile/dashboard and open a detailed order page.

### Customer account

- Registration, login, logout, password reset request, and password update.
- Protected profile, dashboard, and order-detail routes.
- Profile/contact information update.
- Customer order history.
- Wishlist creation, removal, count/status, and wishlist tour listing.

### Informational and lead-generation pages

- About, contact, careers, help, disability support, cancellation policy, privacy policy, terms, tickets, sitemap, and supplier registration.
- Contact, career, and supplier forms submit to backend endpoints; reCAPTCHA is available for form protection.

### Partner-branded booking

The frontend supports live and staging variants of:

- `/:company/tour/:slug`
- `/:company/tour/:slug/:iframe`
- `/:company/checkout/:id`
- `/:company/checkout-success`

These routes reuse the core tour and payment flow while retaining company/partner attribution and using partner-oriented layout behavior.

## 4. Pricing, currency, discount, and tax rules

### Currency model

- CAD is the exchange-rate base used by the scheduled conversion-rate updater.
- The public UI can display and pay in a selected supported currency.
- An order stores its currency and `current_rate`; related payment entries also retain rate context.
- Booking-time snapshots protect already-booked quantities from later exchange-rate changes. Newly added quantities can use the current applicable price/rate and are tracked as adjustment quantities.
- Fixed CAD discounts are converted to the order currency before being saved/displayed; percentage discounts are calculated from the applicable line amount.
- Stripe amounts are converted between decimal major units and integer minor units.
- Zero-decimal currencies, including JPY, are normalized to whole currency units for Stripe and for paid/balance reconciliation.

### Special discounts

- Special discounts apply only when the configured rule is enabled, its charge mode is `NONE`, and the tour date satisfies the notice-day threshold.
- Eligible labels are passenger-like labels such as Adult, Child, Senior, Group, or Participant; unrelated fee/add-on labels do not automatically receive the passenger discount.
- Discount type can be percentage or fixed, and the value is capped so a line cannot become negative.

### Promo codes and vouchers

- Promo codes can be created, edited, copied, enabled/disabled, and applied during checkout.
- The backend validates the code and persists a distinct `PROMOCODE` payment/credit record so it is not confused with cash paid.
- Promo redemption is guarded during payment completion/webhook processing to prevent repeat redemption.
- Vouchers are managed separately and have a public lookup endpoint.

### Taxes, fees, and totals

- Tax rules support percentage and fixed-per-order calculation.
- The taxable subtotal is calculated after eligible special/promo discounts and includes applicable booking fees.
- Booking fees are tracked separately from customer payment credits to prevent double counting.
- Payment summaries distinguish succeeded payments, authorized/uncaptured amounts, discounts, promo credits, refunds, and non-customer marketplace/commission entries.
- Balance is derived from authoritative gross totals minus valid credits; refunds reduce paid amount.
- When an existing booking is edited, the system tracks only the newly added quantity/tax adjustment when deciding what remains payable.

## 5. Stripe and payment lifecycle

### Customer payment flow

1. The frontend updates the order with customer and checkout selections.
2. `/create-payment-intent` creates or updates the Stripe intent using an idempotency key.
3. The amount is normalized for the selected currency and Stripe currency exponent.
4. Stripe Elements confirms the intent.
5. `/verify-payment` validates the result and persists order/payment details.
6. Stripe webhooks independently reconcile intent, capture, refund, and order balance state.

### Supported operational payment actions

Admin order management supports:

- Charging an additional balance.
- Authorizing an initial amount for later capture.
- Capturing or cancelling an uncaptured authorization.
- Adding or removing a stored card.
- Viewing payment details.
- Single and multi-payment refunds.
- Recording internal payment actions and preserving webhook/payment logs.

Webhook receipts and idempotency support reduce duplicate processing. Payment records separate customer money from discounts, booking fees, refunds, commissions, and excluded/third-party collections.

## 6. Backend public API

Public application routes are grouped behind API-key middleware, except provider webhooks. Main endpoint groups are:

- Catalog/discovery: categories, subcategories, home listing, popular cities/destinations, all destinations, city detail, recommendations, and location banners.
- Tours: listing, category listing, search, detail, booking data, add-ons, deposit rule, sub-tours, and session times.
- Commerce: cart add/update/read, checkout, order checkout detail, customer order list/detail, promo application, coupon/voucher lookup, payment intent, payment verification, and save card.
- Identity: register, login, forgot password, logout, password update, and authenticated profile update.
- Engagement: wishlist, contact, careers, and supplier application.
- Providers: Stripe webhook and Mailgun event webhook.

The API commonly uses encrypted IDs or session/order tokens for browser-facing order access. Sanctum protects authenticated profile mutation, while the shared API key protects the public application API surface.

## 7. Admin portal functionality

All principal admin routes are behind authenticated `admin` middleware. Role/permission data is provided by Spatie permission models.

### Dashboard and analytics

- Admin dashboard.
- Tour-wise report.
- Period comparison screen and comparison-data endpoint.
- Overview and revenue reporting with filters.
- Invoice, invoice-detail, customer, schedule-pricing, and price-schedule reports.
- Excel exports for revenue, invoices, customers, schedules, manifests, and tours.

### Tour/catalog management

- Create, edit, clone, preview, publish/disable, reorder, bulk delete, export, and price import/update.
- Parent/sub-tour management.
- Basic details, pricing, add-ons, scheduling, calendar events, locations, pickups, itinerary, FAQ, inclusions, exclusions, optionals, taxes/fees, gallery, and booking rules.
- SEO metadata, info SEO, focus keywords, SEO score, and review configuration.
- Partner assignments.
- Notification, reminder, follow-up, and payment-request message settings per tour.
- Special deposit configuration, schedule-specific pricing, disabled slots, minimum notice, and last-minute behavior.
- Supporting master data: categories, tour types, collections, countries, states, cities, add-ons, pickups, taxes, itineraries, FAQs, features, inclusions, exclusions, and optionals.

### Order and customer operations

- Search/filter/list orders and open an encrypted order edit page.
- Create internal/manual orders and import orders from Excel.
- Edit tour quantities, add-ons, date/time, pickup, customer data, discounts, promo, tax, total, and status.
- Remove an order tour/line and bulk-delete orders where allowed.
- Send confirmation or custom emails and SMS from templates.
- View order email history, activity history, payment state, and provider logs.
- Generate/download order and tour manifests.
- Customer management covers direct users and order-source customer snapshots.

### Driver, vehicle, and manifest operations

- Driver and vehicle directories.
- Driver and vehicle manifests with exports.
- Assign/remove drivers from orders.
- Passenger pickup and driver pickup emails.
- Tour itinerary lookup for operations.

### Promotions, partners, and vouchers

- Promo CRUD, copy-row action, and checkout application.
- Voucher CRUD and public voucher lookup.
- Partner CRUD and partner-tour associations.
- Table views use server-side pagination where implemented, with the recently standardized default of 20 items on updated master screens.

### Users, access, suppliers, and communication

- Admin users, customers, suppliers, drivers, roles, and permissions.
- Admin profile and supplier-profile update.
- Email and SMS template management with preview/test actions.
- Navbar and full notification views, read-one, and mark-all-read.
- Contact-submission administration.
- Activity, descriptive, and order logs.

### Media and site settings

- Upload library, file metadata, preview/download/delete, image compression/storage, and optional S3 backing.
- Banner management.
- YouTube upload integration.
- Customer tour-gallery upload through signed links, order verification, and admin approval/rejection.
- General, email, payment-method, third-party, and activation settings.
- Global special-deposit setting and admin currency switcher.
- Cache/config/permission-cache maintenance actions.
- Business-expense CRUD.

## 8. Scheduled and asynchronous behavior

Laravel scheduler currently runs:

- Currency conversion-rate refresh twice daily at 01:00 and 13:00.
- Abandoned-order scan every 20 minutes; it sends reminders at roughly 20 minutes and 24 hours when templates and customer email are available.
- Tour schedule expiry notification daily at 09:00 for schedules expiring within five days.
- Completed-tour/feedback notification daily at 09:00 for qualifying completed tours, including Trustpilot invitation delivery.

The project also contains Laravel jobs/failed-job infrastructure, Stripe webhook receipt/log persistence, Mailgun event tracking, order email history, and application activity logs.

## 9. SEO and discoverability

- React pages can set SEO metadata through `react-helmet-async`.
- Tour admin contains SEO fields, focus keyword, scoring, and preview-related configuration.
- Backend publishes XML sitemap and ROR feeds for categories, destinations, tours, and static pages.
- Tour and destination slugs are used throughout public routes.

## 10. Important data ownership rules

- The backend is authoritative for availability, discount eligibility, totals, payment state, and balance.
- The browser-selected currency must travel with the order; display conversion alone is not sufficient.
- Order/customer/tour snapshots preserve what was actually booked even if catalog data later changes.
- Payment records are an accounting ledger, not only a list of Stripe charges. Their type and status affect totals differently.
- Stripe redirect success is not final proof by itself; verification and webhook reconciliation are both part of completion.
- Partner source, creator, customer, payment, driver, and activity relationships provide the audit/operations context for an order.

## 11. Review findings and maintenance recommendations

The platform has broad working coverage, but the review found maintainability and correctness risks worth prioritizing:

1. **Very large controllers.** Admin `OrderController`, admin `TourController`, API `OrderController`, API `PaymentController`, and `ReportController` contain thousands of lines and multiple business concerns. Moving booking calculation, payment orchestration, reports, notifications, and manifests into dedicated services/actions would reduce regression risk.
2. **Legacy/duplicate methods remain in production files.** Several methods have numeric suffixes such as `...234`, `...32432`, or older alternate implementations. There are also duplicated route declarations (for example city/category search, schedule-delete slots, sitemap/ROR paths). These should be removed only after route and regression-test confirmation.
3. **Pricing logic spans frontend and backend.** The backend correctly needs final authority, but parallel formulas exist in React checkout and multiple backend controllers/services. A single versioned quote/total response consumed by every screen would prevent product-detail, checkout, success, and admin totals from diverging.
4. **Currency configuration is fragmented.** Frontend environment selection is hard-coded to production in `Constants.jsx`, while API calls also read environment variables directly in places. Configuration should use one environment strategy and one currency conversion module.
5. **Automated coverage is concentrated in services.** Current tests cover authentication, discount/tax/payment-summary rules, idempotency, Stripe currency behavior, webhook configuration, and YouTube upload. High-value end-to-end tests are still needed for full booking, promo plus special discount, partner booking, existing-order quantity change, deposit/capture, refund, and JPY checkout.
6. **Operational commands suppress some exceptions.** For example, abandoned-order processing catches errors without surfacing them. Failures should be logged and monitored so missed reminders are observable.
7. **Secrets and provider configuration need cleanup.** The conversion-rate command contains a provider key in source. Provider credentials should be environment/config values and rotated if this repository has been shared.
8. **Admin maintenance actions use GET routes.** Cache clearing and database/maintenance-style actions should use POST with explicit authorization/audit protection to avoid accidental or crawler-triggered execution.
9. **Public API access should be reviewed.** Most frontend endpoints rely on a shared API key rather than user authentication. Order/session-token endpoints should be tested for ownership, token entropy, expiry, rate limiting, and information disclosure.
10. **Frontend quality gates are limited.** A lint script exists, but there is no frontend unit/e2e test script. Adding checkout-focused tests and a production build/lint CI gate would catch display and calculation regressions earlier.

## 12. Recommended regression checklist

Before any release affecting booking or payment, verify at minimum:

- CAD and one converted two-decimal currency from home through success/admin.
- JPY or another zero-decimal currency from quote through Stripe, paid total, and zero balance.
- Product detail, checkout, checkout success, admin order, invoice, and report show the same persisted totals.
- Percentage and fixed special discounts, including notice-day eligibility.
- Percentage/fixed promo combined with special discount and tax.
- Full payment, partial/deposit payment, authorization/capture, additional payment, and refund.
- Add/remove quantity on an already-paid order without repricing the locked quantity.
- Add-on, pickup, disabled slot, minimum notice, and sub-tour flows.
- Guest, logged-in, partner-branded, and iframe checkout.
- Duplicate payment-intent/webhook delivery does not duplicate payment or promo redemption.
- Abandoned, confirmation, pickup, schedule-expiry, and completed-tour communications.

## 13. Key code locations

### Frontend

- Routes/bootstrap: `tourbeez-front/src/main.jsx`
- Global layout/state initialization: `tourbeez-front/src/App.jsx`
- Tour detail/booking: `tourbeez-front/src/pages/Detail.jsx` and `src/components/Layouts/Detail/`
- Checkout/payment UI: `tourbeez-front/src/components/Layouts/Booking/`
- Currency state/display: `tourbeez-front/src/redux/currencySlice.js`, `src/components/Global/Price.jsx`, and `src/utils/convertCurrency.js`
- API client/config: `tourbeez-front/src/utils/api.jsx` and `src/config/Constants.jsx`

### Backend

- Public API routes: `tourbeez-back/routes/api.php`
- Admin routes: `tourbeez-back/routes/admin.php`
- Tour availability API: `app/Http/Controllers/API/TourController.php`
- Cart/checkout API: `app/Http/Controllers/API/OrderController.php`
- Stripe/payment API: `app/Http/Controllers/API/PaymentController.php`
- Admin order operations: `app/Http/Controllers/OrderController.php`
- Admin tour management: `app/Http/Controllers/TourController.php`
- Checkout calculations: `app/Services/CheckoutTotalService.php`, `CheckoutDiscountService.php`, and `CheckoutTaxService.php`
- Payment accounting: `app/Services/OrderPaymentSummaryService.php`
- Scheduler: `app/Console/Kernel.php`

This document describes functionality confirmed from the current routes, controllers, services, models, React pages/components, and scheduled commands. Features present only as commented code or unused numeric-suffix legacy methods are not treated as active functionality.
