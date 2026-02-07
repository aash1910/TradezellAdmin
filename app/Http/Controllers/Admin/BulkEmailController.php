<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BulkUserEmail;
use App\Models\BulkEmailCampaign;
use App\Models\BulkEmailLog;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Prologue\Alerts\Facades\Alert;

class BulkEmailController extends Controller
{
    const MAX_RECIPIENTS_PER_SEND = 100;

    /**
     * Show the compose form.
     * Supports campaign_id to pre-fill from a previous campaign ("Use Again").
     */
    public function compose(Request $request)
    {
        $userIds = $request->query('user_ids');
        $userIdsArray = $userIds ? array_filter(array_map('intval', explode(',', $userIds))) : [];
        $campaignId = $request->query('campaign_id');

        $presetSubject = '';
        $presetBody = '';
        if ($campaignId && $campaign = BulkEmailCampaign::find($campaignId)) {
            $presetSubject = $campaign->subject;
            $presetBody = $campaign->body;
        }

        $selectedUsers = [];
        if (!empty($userIdsArray)) {
            $selectedUsers = User::whereIn('id', $userIdsArray)
                ->whereNotNull('email')
                ->get();
        }

        return view('vendor.backpack.bulk-email.compose', [
            'selectedUsers' => $selectedUsers,
            'userIds' => $userIdsArray,
            'presetSubject' => $presetSubject,
            'presetBody' => $presetBody,
            'campaignId' => $campaignId,
        ]);
    }

    /**
     * Send bulk emails.
     */
    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'send_to' => 'required|in:all,selected',
        ]);

        if ($validator->fails()) {
            Alert::error('Please fix the validation errors.')->flash();
            return redirect()->back()->withInput()->withErrors($validator);
        }

        $users = collect();

        if ($request->input('send_to') === 'all') {
            $users = User::whereNotNull('email')->get();
        } else {
            $userIds = $request->input('user_ids', []);
            if (empty($userIds)) {
                Alert::error('Please select at least one user.')->flash();
                return redirect()->back()->withInput();
            }
            $users = User::whereIn('id', (array) $userIds)->whereNotNull('email')->get();
        }

        $users = $users->take(self::MAX_RECIPIENTS_PER_SEND);

        if ($users->isEmpty()) {
            Alert::error('No users with valid email addresses found.')->flash();
            return redirect()->back()->withInput();
        }

        $campaign = BulkEmailCampaign::create([
            'subject' => $request->input('subject'),
            'body' => $request->input('body'),
            'recipient_count' => $users->count(),
        ]);

        $sentCount = 0;
        $failedCount = 0;

        foreach ($users as $user) {
            $log = BulkEmailLog::create([
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'email' => $user->email,
                'status' => 'pending',
            ]);

            try {
                Mail::to($user->email)->send(new BulkUserEmail(
                    $request->input('subject'),
                    $request->input('body'),
                    $user
                ));

                $log->update(['status' => 'sent', 'sent_at' => now()]);
                $sentCount++;
            } catch (\Exception $e) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                $failedCount++;
            }
        }

        $message = "Campaign sent. Success: {$sentCount}, Failed: {$failedCount}.";
        if ($failedCount > 0) {
            Alert::warning($message)->flash();
        } else {
            Alert::success($message)->flash();
        }

        return redirect()->route('backpack.bulk-email.log', ['id' => $campaign->id]);
    }

    /**
     * List sent campaigns (history).
     */
    public function history()
    {
        $campaigns = BulkEmailCampaign::withCount(['logs as sent_count' => function ($q) {
            $q->where('status', 'sent');
        }, 'logs as failed_count' => function ($q) {
            $q->where('status', 'failed');
        }])->orderByDesc('created_at')->paginate(20);

        return view('vendor.backpack.bulk-email.history', [
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Show log detail for a campaign.
     */
    public function showLog($id)
    {
        $campaign = BulkEmailCampaign::findOrFail($id);
        $logs = BulkEmailLog::where('campaign_id', $campaign->id)
            ->with('user')
            ->orderBy('id')
            ->get();

        return view('vendor.backpack.bulk-email.log', [
            'campaign' => $campaign,
            'logs' => $logs,
        ]);
    }

    /**
     * Retry sending to a batch of failed recipients. Updates existing logs.
     * Returns JSON: { sent, failed, processed }
     */
    public function retryFailedBatch(Request $request, $id)
    {
        $campaign = BulkEmailCampaign::findOrFail($id);
        $logIds = $request->input('log_ids', []);

        if (empty($logIds)) {
            return response()->json(['error' => 'No log IDs provided'], 400);
        }

        $logs = BulkEmailLog::where('campaign_id', $campaign->id)
            ->whereIn('id', $logIds)
            ->where('status', 'failed')
            ->with('user')
            ->get();

        $sentCount = 0;
        $failedCount = 0;

        foreach ($logs as $log) {
            if (!$log->user || !$log->user->email) {
                $failedCount++;
                continue;
            }

            try {
                sleep(1);
                Mail::to($log->user->email)->send(new BulkUserEmail(
                    $campaign->subject,
                    $campaign->body,
                    $log->user
                ));

                $log->update(['status' => 'sent', 'sent_at' => now(), 'error_message' => null]);
                $sentCount++;
            } catch (\Exception $e) {
                $log->update(['error_message' => $e->getMessage()]);
                $failedCount++;
            }
        }

        return response()->json([
            'sent' => $sentCount,
            'failed' => $failedCount,
            'processed' => $sentCount + $failedCount,
        ]);
    }
}
