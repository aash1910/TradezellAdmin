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
| `MOMO_CURRENCY`                  | **XAF** for Cameroon production. **Sandbox only accepts EUR** — set `MOMO_CURRENCY=EUR` when using sandbox or you will get a failed Request to Pay. |
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

## 3.1 Callback URL (required for async result)

Transfer and RequestToPay are **asynchronous**: MTN responds with `202 Accepted` and later sends a **callback** to your server with the final result. Your server must be set up to receive that callback.

### Sandbox

- **Register the callback host when creating the API user.** The host is set via **`providerCallbackHost`** in the body of `POST /v1_0/apiuser` — it is **not** a separate portal setting. Use the **domain only** (no `https://`, no path), e.g. `admin.piqdrop.com`.
- In each Request to Pay or Transfer request you must send **`X-Callback-Url`** (this app does). The URL’s **host** must match the `providerCallbackHost` you used when creating that API user.
- Only **HTTPS** is allowed. Your callback endpoint must accept **PUT and POST**.

**If you get INVALID_CALLBACK_URL_HOST:** The host in `X-Callback-Url` (e.g. `admin.piqdrop.com`) does not match the `providerCallbackHost` used when the API user was created. Fix:

1. Create a **new** API user for the Collection product with the correct host:
   ```http
   POST https://sandbox.momodeveloper.mtn.com/v1_0/apiuser
   X-Reference-Id: <new-uuid>
   Ocp-Apim-Subscription-Key: <your_collection_primary_key>
   Content-Type: application/json

   {"providerCallbackHost": "admin.piqdrop.com"}
   ```
2. Generate a new API key for that user: `POST .../v1_0/apiuser/<new-uuid>/apikey` (with `Content-Length: 0`).
3. Update `.env`: set `MOMO_COLLECTION_USER_ID` to the new UUID and `MOMO_COLLECTION_API_KEY` to the new key. Do the same for Disbursement if you use callbacks there.
4. Clear config/cache and retry.

Keep `MOMO_CALLBACK_URL=https://admin.piqdrop.com/api/momo/callback` so the backend sends the same host in `X-Callback-Url`.

### Production

- After go-live you register the callback host in the **Accounts Portal** when creating API keys.
- Same rules: HTTPS only; allow PUT and POST on the callback listener.

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

---

## 11. Troubleshooting

### 401 "Access denied due to invalid subscription key"

This happens when the **server** (e.g. admin.piqdrop.com) requests a MoMo token and MTN rejects the `Ocp-Apim-Subscription-Key`.

**Fix:**

1. **Confirm .env on the server**  
   On the machine that runs the API (admin.piqdrop.com), ensure `.env` contains all MoMo variables and that there are no typos or extra spaces:
   - `MOMO_COLLECTION_PRIMARY_KEY` = your **Collections** product **Primary key** from [momodeveloper.mtn.com](https://momodeveloper.mtn.com) → Subscriptions → PiqDrop (Collections).
   - `MOMO_COLLECTION_USER_ID` and `MOMO_COLLECTION_API_KEY` = the API user you created for the Collection product.

2. **Clear config cache on the server**  
   After changing `.env`, run:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

3. **Confirm environment**  
   - Sandbox: `MOMO_BASE_URL=https://sandbox.momodeveloper.mtn.com` and use the **sandbox** subscription keys.  
   - Production: `MOMO_BASE_URL=https://momodeveloper.mtn.com` and use **production** subscription keys.  
   Mixing sandbox keys with production URL (or the other way around) will cause 401.

4. **Re-copy keys from the portal**  
   In [MTN MoMo Developer Portal](https://momodeveloper.mtn.com) → your app → **Collections** subscription, copy the **Primary key** again and set it as `MOMO_COLLECTION_PRIMARY_KEY` on the server. Do not use the Secondary key for `Ocp-Apim-Subscription-Key`.

### "Callback URL does not match the configured value" (INVALID_CALLBACK_URL_HOST)

MTN rejects the request because the **host** in `X-Callback-Url` does not match the **providerCallbackHost** registered for your API user.

**Cause:** On sandbox, the callback host is set only when you **create the API user** (`POST /v1_0/apiuser`) via the body `{"providerCallbackHost": "your-domain.com"}`. If that was set to a different domain (e.g. `piqdrop.com`) but your API and callback URL use `admin.piqdrop.com`, MTN returns this error.

**Fix:** See **§ 3.1 Callback URL** above: create new Collection (and if needed Disbursement) API users with `providerCallbackHost: "admin.piqdrop.com"`, generate new API keys, and update `MOMO_COLLECTION_USER_ID`, `MOMO_COLLECTION_API_KEY` (and disbursement vars) in `.env`. Keep `MOMO_CALLBACK_URL=https://admin.piqdrop.com/api/momo/callback`.

**Temporary workaround (sandbox only):** Comment out or leave empty `MOMO_CALLBACK_URL` on the server so the backend does not send `X-Callback-Url`. Request to Pay may still be accepted; status updates will come only via **polling** in the app.
