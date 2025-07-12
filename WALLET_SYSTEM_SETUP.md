# PiqDrop Wallet System with Stripe Connect

This document describes the wallet system implementation for PiqRider (dropper) app with Stripe Connect integration for secure payouts.

## System Overview

The wallet system allows droppers to:
- View their available and pending balance
- See transaction history
- Request withdrawals to their bank account via Stripe Connect
- Track pending payments that will be released when senders complete delivery

## Architecture

### Frontend (PiqRider App)
- **Wallet Screen**: Main wallet interface showing balance and transactions
- **Withdrawal Screen**: Interface for requesting withdrawals
- **Wallet Service**: API client for wallet operations

### Backend (Laravel)
- **WalletController**: Handles wallet operations and Stripe Connect integration
- **Payment Model**: Tracks all payment types (escrow, release, withdrawal, refund)
- **User Model**: Extended with Stripe Connect account ID

## Setup Instructions

### 1. Stripe Connect Setup

#### Create Stripe Connect Account
1. Go to [Stripe Dashboard](https://dashboard.stripe.com)
2. Navigate to **Connect** → **Settings**
3. Configure your Connect settings:
   - **Application fee**: Set to 0% (or your desired fee)
   - **Payout schedule**: Configure as needed
   - **Supported countries**: Add countries where you operate

#### Get API Keys
1. In Stripe Dashboard, go to **Developers** → **API keys**
2. Copy your **Publishable key** and **Secret key**
3. Add to your Laravel `.env` file:
   ```
   STRIPE_KEY=pk_test_...
   STRIPE_SECRET=sk_test_...
   ```

### 2. Laravel Backend Configuration

#### Install Stripe PHP SDK
```bash
composer require stripe/stripe-php
```

#### Update Configuration
Add Stripe configuration to `config/services.php`:
```php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
],
```

#### Run Migrations
```bash
php artisan migrate
```

This will create:
- `payments` table for tracking all payment types
- `stripe_account_id` column in `users` table

### 3. React Native App Setup

#### Install Dependencies
```bash
npm install @stripe/stripe-react-native
```

#### Configure Stripe
In your app configuration, add Stripe publishable key.

## API Endpoints

### Wallet Endpoints (for Droppers)

#### Get Wallet Balance
```
GET /api/wallet/balance
```
Returns available and pending balance.

#### Get Transactions
```
GET /api/wallet/transactions?page=1&limit=20
```
Returns paginated transaction history.

#### Get Pending Payments
```
GET /api/wallet/pending-payments
```
Returns payments that will be released when delivery is completed.

#### Request Withdrawal
```
POST /api/wallet/withdraw
{
  "amount": 5000,  // Amount in cents
  "currency": "usd"
}
```

#### Get Withdrawal History
```
GET /api/wallet/withdrawals?page=1&limit=20
```

#### Get Stripe Account Status
```
GET /api/wallet/stripe-account
```

#### Setup Stripe Connect Account
```
POST /api/wallet/setup-stripe-account
{
  "email": "user@example.com",
  "country": "US",
  "business_type": "individual",
  "individual": {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "address": {
      "line1": "123 Main St",
      "city": "New York",
      "state": "NY",
      "postal_code": "10001",
      "country": "US"
    },
    "dob": {
      "day": 1,
      "month": 1,
      "year": 1990
    }
  }
}
```

#### Get Minimum Withdrawal Amount
```
GET /api/wallet/minimum-withdrawal
```

### Payment Release Endpoint (for Senders)

#### Release Payment from Escrow
```
POST /api/payments/release/{packageId}
```
Releases payment to dropper when sender completes delivery.

## Database Schema

### Payments Table
```sql
CREATE TABLE payments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    package_id BIGINT NULL,
    user_id BIGINT NOT NULL,
    stripe_payment_intent_id VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'usd',
    status VARCHAR(50) NOT NULL,
    payment_type ENUM('escrow', 'release', 'withdrawal', 'refund') NOT NULL,
    refund_reason TEXT NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Users Table (Extended)
```sql
ALTER TABLE users ADD COLUMN stripe_account_id VARCHAR(255) NULL;
```

## Payment Flow

### 1. Escrow Payment (Sender → Platform)
1. Sender creates package and pays via Stripe
2. Payment is held in escrow (pending status)
3. Payment record created with type 'escrow'

### 2. Payment Release (Platform → Dropper)
1. Dropper accepts and completes delivery
2. Sender confirms completion
3. Payment is released to dropper's wallet
4. Payment record created with type 'release'

### 3. Withdrawal (Dropper → Bank)
1. Dropper requests withdrawal from wallet
2. System creates Stripe Connect payout
3. Payment record created with type 'withdrawal'
4. Funds transferred to dropper's bank account

## Security Considerations

### Stripe Connect Security
- All sensitive operations use Stripe's secure APIs
- No card data stored on your servers
- Stripe handles PCI compliance
- Webhook verification for payment confirmations

### Application Security
- API authentication required for all endpoints
- User authorization checks for all operations
- Input validation and sanitization
- Rate limiting on withdrawal requests

## Testing

### Test Cards
Use Stripe's test cards for development:
- **Success**: `4242424242424242`
- **Decline**: `4000000000000002`
- **Insufficient funds**: `4000000000009995`

### Test Bank Accounts
For Stripe Connect testing:
- **US Bank**: `000123456789`
- **Routing number**: `110000000`

## Production Deployment

### Environment Variables
```bash
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

### Webhook Configuration
1. Set up webhook endpoint in Stripe Dashboard
2. Configure webhook events:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `payout.paid`
   - `payout.failed`

### Monitoring
- Monitor Stripe Dashboard for failed payments
- Set up alerts for high-value transactions
- Track webhook delivery status
- Monitor payout success rates

## Troubleshooting

### Common Issues

#### Payment Not Released
- Check if order status is 'completed'
- Verify escrow payment exists and is succeeded
- Check if payment already released

#### Withdrawal Failed
- Verify Stripe Connect account is active
- Check account verification status
- Ensure sufficient available balance
- Verify bank account details

#### Stripe Connect Account Issues
- Check account requirements in Stripe Dashboard
- Verify business information is complete
- Ensure account is not restricted

### Error Handling
- All API endpoints return consistent error format
- Log all errors for debugging
- Provide user-friendly error messages
- Implement retry logic for transient failures

## Support

For technical support:
- Check Stripe documentation: https://stripe.com/docs
- Review Laravel logs for detailed error information
- Contact development team for application-specific issues

## Changelog

### v1.0.0
- Initial wallet system implementation
- Stripe Connect integration
- Basic withdrawal functionality
- Transaction history tracking 