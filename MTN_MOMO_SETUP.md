# MTN MoMo API — PiqDrop Integration

This document describes the MTN Mobile Money (MoMo) integration used alongside Stripe in PiqDrop (sender app), PiqRider (rider app), and the PiqDropAdmin backend.

**API base URL:** `https://admin.piqdrop.com/api`

---

## 1. Overview

- **Purpose:** Collect payments from senders (Request to Pay) and disburse earnings to riders (Mobile Money payouts).
- **Country:** Cameroon
- **Currency:** XAF (Central African CFA Franc)
- **Products used:** Collection (incoming payments), Disbursement (outgoing payouts).

MTN MoMo runs in parallel with Stripe: users can choose **Card (Stripe)** or **Mobile Money (MTN)** when paying or withdrawing.

---

## 2. Products & Subscriptions

| Product        | Use case                    | Subscription name | Started   |
|----------------|-----------------------------|------------------|-----------|
| **Collections**  | Senders pay via MoMo (escrow) | PiqDrop          | 02/22/2026 |
| **Disbursements**| Riders withdraw to MoMo      | PiqDrop          | 02/22/2026 |

Subscription keys (Primary / Secondary) are in the [MTN MoMo Developer Portal](https://momodeveloper.mtn.com) under your product subscriptions. **Do not commit these to version control** — they live in `.env` only.

---

## 3. Environment Variables (.env)

All MoMo configuration is read from `.env` in **PiqDropAdmin**. Add or update the following (values are examples; use your real keys and callback URL):

```env
# ─── MTN MoMo API ─────────────────────────────────────────────────────────────
MOMO_BASE_URL=https://sandbox.momodeveloper.mtn.com
MOMO_ENVIRONMENT=sandbox
MOMO_CURRENCY=XAF
MOMO_CALLBACK_URL=https://admin.piqdrop.com/api/momo/callback

# Collection (payments from senders)
MOMO_COLLECTION_PRIMARY_KEY=<your_collection_primary_key>
MOMO_COLLECTION_USER_ID=<collection_api_user_uuid>
MOMO_COLLECTION_API_KEY=<collection_api_key>

# Disbursement (payouts to riders)
MOMO_DISBURSEMENT_PRIMARY_KEY=<your_disbursement_primary_key>
MOMO_DISBURSEMENT_USER_ID=<disbursement_api_user_uuid>
MOMO_DISBURSEMENT_API_KEY=<disbursement_api_key>
```

| Variable                         | Description |
|----------------------------------|-------------|
| `MOMO_BASE_URL`                  | Sandbox: `https://sandbox.momodeveloper.mtn.com`. Production: `https://momodeveloper.mtn.com`. |
| `MOMO_ENVIRONMENT`               | `sandbox` or `production`. |
| `MOMO_CURRENCY`                  | **XAF** for Cameroon. Sandbox only supports **EUR** — use EUR in sandbox, XAF in production. |
| `MOMO_CALLBACK_URL`              | Public URL MTN calls for payment/disbursement status. Must be HTTPS and reachable from the internet. |
| `MOMO_COLLECTION_PRIMARY_KEY`    | Ocp-Apim-Subscription-Key from the **Collections** product. |
| `MOMO_COLLECTION_USER_ID`       | UUID of the API user created for Collection (via `POST /v1_0/apiuser`). |
| `MOMO_COLLECTION_API_KEY`       | API key for that Collection user (via `POST /v1_0/apiuser/{userId}/apikey`). |
| `MOMO_DISBURSEMENT_PRIMARY_KEY`  | Ocp-Apim-Subscription-Key from the **Disbursements** product. |
| `MOMO_DISBURSEMENT_USER_ID`     | UUID of the API user created for Disbursement. |
| `MOMO_DISBURSEMENT_API_KEY`     | API key for that Disbursement user. |

After changing `.env`, run:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 4. Backend (PiqDropAdmin) — Files & Routes

### Config

- **`config/services.php`** — `momo` array reads all `MOMO_*` env variables (see file for structure).

### Service

- **`app/Services/MomoService.php`**
  - OAuth2 token (cached) for Collection and Disbursement.
  - `requestToPay(amount, phone, referenceId, ...)` — Collection: Request to Pay.
  - `getPaymentStatus(referenceId)` — Collection: get transaction status.
  - `disburse(amount, phone, referenceId, ...)` — Disbursement: transfer to wallet.
  - `getDisbursementStatus(referenceId)` — Disbursement: get transfer status.
  - `mapMomoStatus(momoStatus)` — Maps MTN status to internal status (e.g. SUCCESSFUL → succeeded).

### Controller

- **`app/Http/Controllers/Api/MomoPaymentController.php`**
  - `requestToPay` — Initiate collection; create `Payment` with `payment_gateway=momo`.
  - `getStatus` — Poll status for a reference; sync to DB.
  - `createPackageAfterPayment` — Create package after successful MoMo escrow (mirrors Stripe flow).
  - `callback` — Public; MTN collection webhook; updates payment status.
  - `disburse` — Initiate rider payout to MoMo.
  - `disbursementCallback` — Public; MTN disbursement webhook; updates withdrawal status.

### API Routes (`routes/api.php`)

**Authenticated (Sanctum):**

| Method | Route                          | Controller method           |
|--------|--------------------------------|-----------------------------|
| POST   | `/api/momo/request-to-pay`     | `requestToPay`              |
| GET    | `/api/momo/status/{referenceId}`| `getStatus`                 |
| POST   | `/api/momo/create-package`     | `createPackageAfterPayment` |
| POST   | `/api/momo/disburse`           | `disburse`                  |

**Public (no auth — called by MTN):**

| Method | Route                           | Controller method           |
|--------|---------------------------------|-----------------------------|
| POST   | `/api/momo/callback`            | `callback`                  |
| POST   | `/api/momo/callback/disbursement`| `disbursementCallback`     |

Base URL for callbacks in MTN portal must match your API server: **`https://admin.piqdrop.com`**. Full callback URLs:

- Collection: `https://admin.piqdrop.com/api/momo/callback`
- Disbursement: `https://admin.piqdrop.com/api/momo/callback/disbursement`

### Database

- **Migration:** `database/migrations/2026_02_22_000000_add_momo_fields_to_payments_table.php`
  - Adds: `payment_gateway` (default `stripe`), `momo_reference_id` (nullable, unique), `momo_phone_number` (nullable).
  - Makes `stripe_payment_intent_id` nullable so MoMo payments can be stored in the same table.

Run:

```bash
php artisan migrate
```

- **Model:** `app/Models/Payment.php` — `payment_gateway`, `momo_reference_id`, `momo_phone_number` are in `$fillable`.

---

## 5. Frontend Integration

### PiqDrop (sender app)

- **`services/payment.service.ts`**
  - `initiateMomoPayment(amount, phone, packageId?, packageData?)` → `POST /api/momo/request-to-pay`
  - `checkMomoStatus(referenceId)` → `GET /api/momo/status/{referenceId}`
  - `createPackageAfterMomoPayment(momoReferenceId, packageData)` → `POST /api/momo/create-package`

- **`app/payment.tsx`**
  - Payment method selector: **Card (Stripe)** | **Mobile Money (MTN)**.
  - For MoMo: phone number input (with country code hint), then “Approve on your phone” screen with polling until success/failure.

### PiqRider (rider app)

- **`services/wallet.service.ts`**
  - `requestMomoWithdrawal(amount, phone)` → `POST /api/momo/disburse`

- **`app/withdraw.tsx`**
  - Withdrawal method selector: **Bank (Stripe Connect)** | **Mobile Money (MTN)**.
  - For MoMo: phone number input, then confirm and submit.

---

## 6. Country & Currency (Cameroon / XAF)

- **Country:** Cameroon  
- **Currency:** **XAF** (Central African CFA Franc).

**Important:**

- **Sandbox:** Only **EUR** is supported. For local testing, set `MOMO_CURRENCY=EUR` and use sandbox test number (e.g. `46733123454`).
- **Production (Cameroon):** Set `MOMO_CURRENCY=XAF`, `MOMO_ENVIRONMENT=production`, and `MOMO_BASE_URL=https://momodeveloper.mtn.com` (no `sandbox.`).

Phone numbers for Cameroon are in E.164 format (e.g. `2376XXXXXXXX`). Frontend hints should guide users to include country code without `+`.

---

## 7. Sandbox vs Production

| Item           | Sandbox                          | Production (Cameroon)        |
|----------------|----------------------------------|------------------------------|
| Base URL       | `https://sandbox.momodeveloper.mtn.com` | `https://momodeveloper.mtn.com` |
| Environment    | `sandbox`                        | `production`                 |
| Currency       | EUR only                         | XAF                          |
| Test payer     | e.g. `46733123454`               | Real MTN MoMo numbers        |
| Callback URL   | Must be public HTTPS             | Same                         |

---

## 8. Callback URL Configuration

1. **Collection callback:** Configure in MTN Developer Portal (Collections product) or ensure your server accepts `POST /api/momo/callback`.  
   Full URL: `https://<your-domain>/api/momo/callback`.

2. **Disbursement callback:** Same idea for disbursement status.  
   Full URL: `https://<your-domain>/api/momo/callback/disbursement`.

3. Callbacks must be **public** (no auth) and **HTTPS**. Laravel routes for these are defined **outside** the `auth:sanctum` group.

---

## 9. Quick Test (Sandbox)

1. Set `MOMO_CURRENCY=EUR` and use sandbox credentials.
2. From PiqDrop, choose **Mobile Money**, enter phone `46733123454`, amount (e.g. 1 EUR), and submit.
3. Backend creates a Request to Pay; sandbox may auto-approve or simulate. Use **Get status** (or app polling) to see status.
4. For disbursement, from PiqRider choose **Mobile Money**, enter amount and test number, and submit.

---

## 10. Summary Checklist

- [ ] `.env` in PiqDropAdmin has all 9 `MOMO_*` variables; currency set to **XAF** for production (Cameroon).
- [ ] `php artisan migrate` run so `payments` has `payment_gateway`, `momo_reference_id`, `momo_phone_number`.
- [ ] Callback URLs are public, HTTPS, and registered where required by MTN.
- [ ] Sandbox testing uses EUR and sandbox test number; production uses XAF and real MTN MoMo numbers for Cameroon.

For subscription keys, API user creation, and API key generation, use the [MTN MoMo Developer Portal](https://momodeveloper.mtn.com) and keep all secrets in `.env` only (never in this file or in code).
