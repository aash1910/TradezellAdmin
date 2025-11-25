# App Store Submission Guide for PiqDrop & PiqRider

## 🎯 Overview
This guide will help you successfully publish both PiqDrop (Sender) and PiqRider (Delivery) apps to the Apple App Store.

---

## ⚠️ CRITICAL: Switch to Production Stripe Keys

**BEFORE SUBMITTING**, you MUST update both apps to use Stripe LIVE/PRODUCTION keys:

### PiqDrop App
Update `/Volumes/ExAsh/Sites/PiqDrop/app.json`:
```json
"stripePublishableKey": "pk_live_YOUR_PRODUCTION_KEY"
```

### PiqRider App
Update `/Volumes/ExAsh/Sites/PiqRider/app.json`:
```json
"stripePublishableKey": "pk_test_51RaMqGBQsHpfCUCm..." → "pk_live_YOUR_PRODUCTION_KEY"
```

### Backend (Laravel)
Update `/Volumes/ExAsh/Sites/PiqDropAdmin/.env`:
```
STRIPE_KEY=sk_live_YOUR_SECRET_KEY
STRIPE_PUBLIC_KEY=pk_live_YOUR_PRODUCTION_KEY
```

---

## 📱 App 1: PiqDrop (Sender App)

### App Information

**App Name:** PiqDrop Sender 

**Subtitle (30 chars max):** 
```
Fast & Reliable Delivery
```

**Copyright:**
```
2025 PiqDrop
```

**Category:**
- **Primary:** Business or Lifestyle
- **Secondary:** Productivity or Travel

**Description (4000 chars max):**
```
PiqDrop - Your Trusted Package Delivery Solution

Send packages quickly and securely with PiqDrop! Whether you're sending a gift to a loved one, important documents, or everyday items, PiqDrop connects you with reliable local couriers who ensure your packages arrive safely and on time.

KEY FEATURES:

Easy Package Sending
- Create delivery requests in seconds
- Add package photos and details
- Set pickup and drop-off locations with precision
- Stay connected with your rider
- Chat with your rider anytime

Secure Payments
- Pay safely with Stripe integration
- Transparent pricing with no hidden fees
- Instant payment confirmation

Direct Messaging
- Chat directly with your assigned rider
- Get instant support from PiqDrop team
- Real-time updates and delivery coordination

User-Friendly Experience
- Simple and intuitive interface
- Save favorite addresses for quick access
- View complete delivery history
- Rate and review your delivery experience

Smart Notifications
- Instant updates on delivery status
- Pickup and drop-off confirmations
- Rider assignment alerts
- Delivery completion notifications

Wide Coverage
- Local delivery services
- Flexible scheduling options
- Multiple package types supported
- Same-day delivery available

PERFECT FOR:
- Personal package delivery
- Business document courier
- Gift deliveries
- E-commerce fulfillment
- Emergency deliveries
- Returned items

WHY CHOOSE PIQDROP?

- Trusted Couriers - Riders are verified
- Competitive Pricing - Set your own price for delivery service
- Fast Delivery - Quick pickup and drop-off
- Handling with care
- Support when you need it

HOW IT WORKS:

1. Enter pickup and delivery locations
2. Add package details and photos
3. Set your price based on package type
4. Confirm and pay securely
5. Contact rider directly for delivery updates
6. Receive delivery confirmation

Download PiqDrop today and experience hassle-free package delivery at your fingertips!

Privacy Policy: https://piqdrop.com/privacy-policy
```

**Keywords (100 chars max):**
```
delivery,courier,package,shipping,local delivery,same day,tracking,express,send package,logistics
```

**Promotional Text (170 chars max - optional but recommended):**
```
NEW: Direct messaging with riders! Set your own delivery price and communicate directly with riders. Download now and get your first delivery quote instantly!
```

**Support URL:**
```
https://piqdrop.com/support
```
(Or use support@piqdrop.com if you don't have a support page)


**Privacy Policy URL (REQUIRED):**
```
https://piqdrop.com/privacy-policy
```

### App Review Information

**Sign-In Information:**
- **Sign-in required:** Yes
- **User name:** [DEMO_USERNAME]
- **Password:** [DEMO_PASSWORD]

**Contact Information:**
- **First name:** Ashraful
- **Last name:** Islam
- **Phone number:** +46-700671992
- **Email:** support@piqdrop.com
- **Notes:** 
```
Demo account for PiqDrop Sender app review.
Account has sample delivery requests and test data.
All features are functional including:
- Package creation and photo upload
- Direct messaging with riders
- Payment processing (test mode)
- Delivery tracking and updates

For any issues during review, contact support@piqdrop.com
```

---

## 🏍️ App 2: PiqDrop Rider (Delivery App)

### App Information

**App Name:** PiqDrop Rider

**Subtitle (30 chars max):**
```
Earn Money Delivering Packages
```

**Category:**
- **Primary:** Lifestyle
- **Secondary:** Productivity

**Description (4000 chars max):**
```
PiqDrop Rider - Deliver & Earn on Your Schedule

Join PiqDrop's growing network of delivery couriers and start earning money on your own terms! Whether you want full-time income or flexible side earnings, PiqDrop Rider gives you the freedom to work when and where you want.

KEY FEATURES:

Earn More, Your Way
• Competitive earnings per delivery
• Daily or weekly payouts via Stripe
• Transparent earnings breakdown

Smart Job Management
• See available deliveries near you
• Accept jobs that fit your schedule
• Optimized route suggestions
• Multiple delivery support
• Real-time earnings tracker

Easy Navigation
• View pickup and drop-off locations on map
• One-tap navigation via Google Maps
• Distance and time estimates
• Clear address details for each delivery

Secure & Fast Payments
• Instant payment processing via Stripe
• Direct bank account withdrawals
• Detailed earning reports
• Secure banking integration

Professional Tools
• In-app messaging with sender
• Customer rating system

Order Manager
• View all available delivery requests
• Accept or decline orders based on your availability
• Update delivery status at each step
• Access complete order details and package information
• View delivery history and completed orders

PERFECT FOR:
• Part-time workers
• Students
• Freelancers
• Independent contractors
• Anyone seeking flexible income
• Gig economy workers

WHY DELIVER WITH PIQDROP?
• Flexible Hours - Work when you want
• Fair Pay - Competitive rates
• Quick Payouts - Get paid fast
• No Shifts - Be your own boss
• Easy to Start - Simple onboarding
• Support Team - We've got your back

HOW IT WORKS:

1. Sign up and complete verification
2. Go online when ready to work
3. Accept delivery requests near you
4. Pick up package from sender
5. Deliver to recipient
6. Get paid instantly

REQUIREMENTS:
• Government-issued ID (passport, national ID, etc.)
• Reliable transportation (own vehicle, bike, or public transit/flights etc.)
• Smartphone
• 18+ years old

EARNINGS POTENTIAL:
• Competitive pay per delivery
• Transparent earnings breakdown
• Daily or weekly payouts via Stripe
• Detailed earning reports

JOIN THOUSANDS OF SUCCESSFUL RIDERS

Start earning today with PiqDrop Rider - your gateway to flexible income and financial freedom!

Privacy Policy: https://piqdrop.com/privacy-policy
```

**Keywords (100 chars max):**
```
delivery driver,courier job,earn money,gig economy,flexible work,side hustle,driver app,delivery
```

**Promotional Text (170 chars max):**
```
💸 Earn money delivering packages! Flexible schedule, instant payouts. Join our driver network today and start making money on your own terms!
```

**Support URL:**
```
https://piqdrop.com/support
```
(Or use support@piqdrop.com for both apps)

**Marketing URL (optional):**
```
https://piqdrop.com
```

**Privacy Policy URL (REQUIRED):**
```
https://piqdrop.com/privacy-policy
```

---

## 📸 App Store Screenshots Requirements

### iPhone Screenshots (REQUIRED - in order of priority)

**Sizes needed:**
1. **6.7" (iPhone 15 Pro Max, 14 Pro Max, 13 Pro Max, 12 Pro Max)** - 1290 x 2796 pixels (REQUIRED)
2. **6.5" (iPhone 11 Pro Max, XS Max)** - 1242 x 2688 pixels (REQUIRED)
3. **5.5" (iPhone 8 Plus)** - 1242 x 2208 pixels (Optional)

**Minimum: 3 screenshots, Maximum: 10 screenshots**

### Recommended Screenshots for PiqDrop:
1. **Home/Map Screen** - "Send Packages Anywhere, Anytime"
2. **Create Delivery** - "Simple 3-Step Delivery Process"
3. **Delivery Details** - "View Package Status"
4. **Payment** - "Secure Payment with Stripe"
5. **Delivery History** - "View All Your Deliveries"

### Recommended Screenshots for PiqDrop Rider:
1. **Available Jobs** - "Find Deliveries Near You"
2. **Job Details** - "Accept Jobs That Fit Your Schedule"
3. **Navigation** - "Easy Turn-by-Turn Navigation"
4. **Earnings Dashboard** - "Track Your Earnings in Real-Time"
5. **Payout** - "Get Paid Instantly"

### App Preview Video (Optional but Highly Recommended)
- Max 30 seconds
- Same sizes as screenshots
- Shows key features in action

---

## 📋 App Review Information (For Both Apps)

### Contact Information
**First Name:** [Your First Name]
**Last Name:** [Your Last Name]
**Phone Number:** [Your Phone with country code]
**Email:** [Your email for App Review team]

### Demo Account (REQUIRED for apps with login)

**For PiqDrop (Sender App):**
```
Username/Email: demo@piqdrop.com
Password: DemoPass123!
```

**For PiqDrop Rider (Delivery App):**
```
Username/Email: demorider@piqdrop.com
Password: RiderDemo123!
```

⚠️ **CRITICAL:** Create these demo accounts in your admin panel BEFORE submitting!

### Review Notes (What to include):

**For PiqDrop:**
```
TEST ACCOUNT CREDENTIALS:
Email: demo@piqdrop.com
Password: DemoPass123!

TESTING INSTRUCTIONS:
1. Login with demo credentials

2. To test delivery creation:
   - Tap "Home" tab on home screen
   - Enter pickup address: [Provide a valid test address]
   - Enter delivery/Drop-off address: [Provide a valid test address]
   - Add package details and photos
   - Proceed to payment

3. Payment Testing (STRIPE TEST MODE):
   - **IMPORTANT**: App is configured with Stripe TEST MODE for review
   - **No real charges will be made**
   - Test card number: 4242 4242 4242 4242
   - Any future expiry date (e.g., 12/25)
   - Any 3-digit CVC (e.g., 123)
   - This will simulate a successful payment without charging any real money

4. Location Permissions:
   - App requires location for pickup/delivery addresses
   - Location is used ONLY when app is active (foreground)
   - No background location tracking
   - Please allow location access when prompted

5. Authentication:
   - Google Sign-In is available as an alternative login method
   - Test with any Google account or use demo credentials

Note: This is a delivery service app. The test environment simulates the full delivery flow.
Payment processing uses Stripe's test environment - no real transactions will occur during review.
```

**For PiqDrop Rider:**
```
TEST ACCOUNT CREDENTIALS:
Email: demorider@piqdrop.com
Password: RiderDemo123!

TESTING INSTRUCTIONS:
1. Login with demo credentials
2. The demo account is pre-verified and approved
3. To test delivery acceptance:
   - Tap "Home" tab on home screen
   - Available deliveries/packages will appear
   - Tap any delivery/package to view details
   - Accept delivery to start

4. Earnings & Payout Testing:
   - View Wallet in "Profile" tab
   - Test payout with Stripe test mode
   - Test bank account: Use Stripe test details

5. Location Permissions:
   - App requires location to show nearby deliveries
   - Location is used ONLY when app is active (foreground)
   - No background location tracking
   - Please allow location access when prompted

6. Authentication:
   - Google Sign-In is available as an alternative login method
   - Test with any Google account or use demo credentials

Note: This is for delivery couriers. Demo account has pre-loaded test deliveries.
```

---

## 🔐 Compliance & Privacy

### App Privacy Details (You'll answer these in App Store Connect)

**Both Apps Collect:**
- ✓ Name, Email, Phone Number (Account Creation)
- ✓ Precise Location (Pickup/Delivery addresses - foreground only, NO background tracking)
- ✓ Payment Information (Stripe handles this)
- ✓ Photos (Package documentation)

**Data Usage:**
- ✓ App Functionality
- ✓ Third-Party Authentication (Google Sign-In)

**Data Linked to You:**
- Contact Info, Location (when using app), Financial Info, Photos, User Content

**Data Sharing:**
- ✓ Third-party payment processor (Stripe)
- ✓ Google Sign-In for authentication

**Data NOT Collected:**
- ✗ Background location
- ✗ Browsing/Search history
- ✗ Health data
- ✗ Tracking across other apps/websites

### Export Compliance
**Does your app use encryption?**
```
Answer: YES

Follow-up: Is it exempt from regulations?
Answer: YES - Uses standard encryption (HTTPS, etc.)

You may need to set: ITSAppUsesNonExemptEncryption = NO (already set in your app.json)
```

---

## 🚀 Pre-Submission Checklist

### Technical Requirements
- [x] App version: 1.0.19 (in app.json)
- [ ] **CRITICAL: Switch Stripe to PRODUCTION keys**
- [ ] Test app thoroughly on real device
- [ ] Verify all features work without crashes
- [ ] Test payment flow completely
- [ ] Ensure demo accounts are created and working
- [ ] Check location permissions work properly (foreground only)
- [ ] Verify Google Sign-In authentication works

### Content Requirements
- [ ] Create demo accounts in admin panel
- [ ] Prepare 3-10 screenshots per app (6.7" required)
- [ ] Optional: Create app preview videos
- [ ] Prepare app icon (1024x1024px)
- [ ] Write privacy policy and host it online
- [ ] Write terms of service (optional but recommended)

### App Store Connect Setup
- [ ] Complete app information
- [ ] Add screenshots
- [ ] Set pricing (Free)
- [ ] Configure in-app purchases (if any)
- [ ] Answer privacy questionnaire
- [ ] Add review notes with demo credentials
- [ ] Set release options (Manual or Automatic)

---

## 📤 Build & Submit Process

### Step 1: Update to Production Stripe Keys
```bash
# Update both app.json files with production Stripe keys
# Update backend .env with production Stripe keys
```

### Step 2: Build Production Builds
```bash
# For PiqDrop
cd /Volumes/ExAsh/Sites/PiqDrop
eas build --platform ios --profile production

# For PiqRider
cd /Volumes/ExAsh/Sites/PiqRider
eas build --platform ios --profile production
```

### Step 3: Submit to App Store
```bash
# For PiqDrop
cd /Volumes/ExAsh/Sites/PiqDrop
eas submit --platform ios --latest

# For PiqRider
cd /Volumes/ExAsh/Sites/PiqRider
eas submit --platform ios --latest
```

### Step 4: Complete App Store Connect

1. **Go to App Store Connect** (https://appstoreconnect.apple.com)

2. **For Each App:**
   - Click on the app
   - Click "Prepare for Submission"
   - Fill in all information from this guide
   - Upload screenshots
   - Add demo account credentials in "Review Information"
   - Save

3. **Submit for Review**
   - Click "Add for Review"
   - Answer export compliance questions
   - Answer content rights questions
   - Click "Submit for Review"

---

## ⏱️ Timeline & Expectations

**Review Time:** Usually 24-48 hours (can be up to 7 days)

**Common Rejection Reasons & How to Avoid:**

1. **Guideline 2.1 - App Completeness**
   - ✓ Provide working demo accounts
   - ✓ Ensure all features work
   - ✓ No broken links or crashes

2. **Guideline 4.3 - Spam/Duplicates**
   - ✓ Make apps distinct (one for senders, one for riders)
   - ✓ Clear different purposes in descriptions

3. **Guideline 5.1.1 - Privacy**
   - ✓ Include privacy policy URL
   - ✓ Explain location usage clearly
   - ✓ Complete privacy details in App Store Connect

4. **Guideline 3.1.1 - In-App Purchase**
   - ✓ Use Stripe for physical goods/services (✓ allowed)
   - ✓ Not for digital content

5. **Guideline 2.3.3 - Screenshots**
   - ✓ Show actual app interface
   - ✓ No misleading images

---

## 🔄 If Rejected

1. **Read the rejection message carefully**
2. **Fix the specific issues mentioned**
3. **Update build if code changes needed**
4. **Update metadata if content issues**
5. **Reply to App Review in Resolution Center**
6. **Resubmit**

---

## 📞 Support Resources

**App Store Connect:** https://appstoreconnect.apple.com
**Developer Account:** https://developer.apple.com/account
**App Review Guidelines:** https://developer.apple.com/app-store/review/guidelines/
**EAS Documentation:** https://docs.expo.dev/submit/introduction/

**Contact App Review (if needed):**
- Via Resolution Center in App Store Connect
- Phone: Available in your account region

---

## ✅ Final Pre-Launch Checklist

### Before Clicking "Submit for Review"

- [ ] ✅ Stripe PRODUCTION keys installed
- [ ] ✅ Backend uses PRODUCTION Stripe keys
- [ ] ✅ Demo accounts created and tested
- [ ] ✅ All screenshots uploaded
- [ ] ✅ App descriptions are complete
- [ ] ✅ Privacy policy URL is live and working
- [ ] ✅ Support email is monitored
- [ ] ✅ Contact information is current
- [ ] ✅ Review notes include clear testing instructions
- [ ] ✅ Tested the exact build being submitted
- [ ] ✅ No crashes or major bugs
- [ ] ✅ Export compliance answered correctly
- [ ] ✅ Age rating set appropriately
- [ ] ✅ Pricing/availability set correctly

---

## 🎉 Post-Approval

1. **App goes live automatically or manually** (based on your selection)
2. **Monitor crash reports** in App Store Connect
3. **Respond to user reviews**
4. **Plan for updates** (fix bugs, add features)
5. **Monitor analytics**

---

**Good luck with your submission! 🚀**

*Created: October 9, 2025*

