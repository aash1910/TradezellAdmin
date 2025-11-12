<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class WalletConnectController extends Controller
{
    /**
     * Handle refresh URL for Stripe Connect onboarding
     * This is called when the user needs to refresh their session
     */
    public function refresh(Request $request)
    {
        try {
            Log::info('Stripe Connect refresh called', [
                'user_id' => Auth::id(),
                'query_params' => $request->all()
            ]);

            // Get the account ID from the request
            $accountId = $request->get('account_id');
            
            if (!$accountId) {
                return $this->showError('Missing account ID');
            }

            // Create a new account link for the user to continue onboarding
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            
            $accountLink = \Stripe\AccountLink::create([
                'account' => $accountId,
                'refresh_url' => config('app.url') . '/wallet/connect/refresh',
                'return_url' => config('app.url') . '/wallet/connect/return',
                'type' => 'account_onboarding',
            ]);

            // Redirect to the new account link
            return redirect($accountLink->url);

        } catch (\Exception $e) {
            Log::error('Stripe Connect refresh error: ' . $e->getMessage());
            return $this->showError('Failed to refresh Stripe Connect session');
        }
    }

    /**
     * Handle return URL for Stripe Connect onboarding
     * This is called when the user completes or cancels the onboarding
     */
    public function return(Request $request)
    {
        try {
            Log::info('Stripe Connect return called', [
                'user_id' => Auth::id(),
                'query_params' => $request->all()
            ]);

            $accountId = $request->get('account_id');
            $status = $request->get('status', 'unknown');

            if (!$accountId) {
                return $this->showError('Missing account ID');
            }

            // Check the account status
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            
            try {
                $account = \Stripe\Account::retrieve($accountId);
                
                if ($account->charges_enabled && $account->payouts_enabled) {
                    return $this->showSuccess('Account setup completed successfully! You can now withdraw funds.');
                } elseif ($account->requirements->currently_due && count($account->requirements->currently_due) > 0) {
                    return $this->showWarning('Account setup incomplete. Please complete all required fields.');
                } else {
                    return $this->showWarning('Account setup in progress. Please wait for verification.');
                }
            } catch (\Stripe\Exception\ApiErrorException $e) {
                Log::error('Stripe account retrieval error: ' . $e->getMessage());
                return $this->showError('Failed to verify account status');
            }

        } catch (\Exception $e) {
            Log::error('Stripe Connect return error: ' . $e->getMessage());
            return $this->showError('Failed to process return from Stripe Connect');
        }
    }

    /**
     * Show success page
     */
    private function showSuccess($message)
    {
        return view('wallet.connect.success', [
            'title' => 'Setup Complete',
            'message' => $message,
            'icon' => '✅',
            'color' => 'green'
        ]);
    }

    /**
     * Show warning page
     */
    private function showWarning($message)
    {
        return view('wallet.connect.warning', [
            'title' => 'Setup Incomplete',
            'message' => $message,
            'icon' => '⚠️',
            'color' => 'orange'
        ]);
    }

    /**
     * Show error page
     */
    private function showError($message)
    {
        return view('wallet.connect.error', [
            'title' => 'Setup Failed',
            'message' => $message,
            'icon' => '❌',
            'color' => 'red'
        ]);
    }

    /**
     * Delete a Stripe Connect account
     * 
     * This method deletes a Stripe Connect account using the account ID.
     * 
     * @access public
     * @author Ashraful Islam
     * @param  \Illuminate\Http\Request $request The incoming request instance containing 'account_id' as query parameter
     * @return \Illuminate\Http\JsonResponse A JSON response indicating success or failure of the account deletion
     */
    public function deleteAccount(Request $request)
    {
        try {
            $request->validate([
                'account_id' => 'required|string'
            ]);

            $accountId = $request->query('account_id');

            Log::info('Deleting Stripe Connect account', [
                'user_id' => Auth::id(),
                'account_id' => $accountId
            ]);

            // Initialize Stripe client with API key from config
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            // Delete the Stripe Connect account
            $deleted = $stripe->accounts->delete($accountId, []);

            Log::info('Stripe Connect account deleted successfully', [
                'user_id' => Auth::id(),
                'account_id' => $accountId,
                'deleted' => $deleted->deleted ?? false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stripe Connect account deleted successfully',
                'account_id' => $accountId,
                'deleted' => $deleted->deleted ?? false
            ], 200);

        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe API error while deleting account: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'account_id' => $request->query('account_id'),
                'error_code' => $e->getStripeCode()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Stripe Connect account: ' . $e->getMessage()
            ], 400);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error deleting Stripe Connect account: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'account_id' => $request->query('account_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the account: ' . $e->getMessage()
            ], 500);
        }
    }
} 