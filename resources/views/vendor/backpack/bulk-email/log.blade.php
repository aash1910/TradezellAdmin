@extends(backpack_view('blank'))

@section('header')
    <section class="container-fluid">
        <h2>
            <span class="text-capitalize">Campaign Log</span>
        </h2>
    </section>
@endsection

@section('content')
@php
    $sentCount = $campaign->logs()->where('status', 'sent')->count();
    $failedCount = $campaign->logs()->where('status', 'failed')->count();
    $failedLogIds = $logs->where('status', 'failed')->pluck('id')->values()->toArray();
@endphp
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="la la-list"></i> {{ $campaign->subject ?: 'Campaign #' . $campaign->id }}
                </h3>
                <div class="card-tools">
                    <a href="{{ url(config('backpack.base.route_prefix') . '/bulk-email/history') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="la la-arrow-left"></i> Back to History
                    </a>
                    <a href="{{ url(config('backpack.base.route_prefix') . '/bulk-email/compose?campaign_id=' . $campaign->id) }}" class="btn btn-sm btn-success" title="Send same email again">
                        <i class="la la-reply"></i> Use Again
                    </a>
                    @if($failedCount > 0)
                    <button type="button" class="btn btn-sm btn-warning resend-failed-btn" title="Resend to failed recipients only"
                            data-campaign-id="{{ $campaign->id }}"
                            data-failed-log-ids="{{ json_encode($failedLogIds) }}"
                            data-failed-count="{{ $failedCount }}">
                        <i class="la la-refresh"></i> Resend to Failed ({{ $failedCount }})
                    </button>
                    @endif
                    <a href="{{ url(config('backpack.base.route_prefix') . '/bulk-email/compose') }}" class="btn btn-sm btn-primary">
                        <i class="la la-envelope"></i> Compose New
                    </a>
                </div>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Subject:</strong> {{ $campaign->subject ?: '(no subject)' }}<br>
                    <strong>Sent:</strong> {{ $sentCount }} &nbsp;
                    <strong>Failed:</strong> {{ $failedCount }} &nbsp;
                    <strong>Total:</strong> {{ $campaign->recipient_count ?? 0 }}
                </p>
                @if($campaign->body)
                <div class="mb-4 p-3 bg-light rounded border">
                    <strong>Email Content:</strong>
                    <div class="mt-2" style="white-space: pre-wrap;">{!! nl2br(e($campaign->body)) !!}</div>
                </div>
                @endif
                <h6 class="mb-2">Recipients</h6>
                <table class="table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Error</th>
                            <th>Sent At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->user ? $log->user->full_name : '-' }}</td>
                            <td>{{ $log->email }}</td>
                            <td>
                                @if($log->status === 'sent')
                                    <span class="badge badge-success">Sent</span>
                                @elseif($log->status === 'failed')
                                    <span class="badge badge-danger">Failed</span>
                                @else
                                    <span class="badge badge-secondary">Pending</span>
                                @endif
                            </td>
                            <td><small class="text-danger">{{ $log->error_message }}</small></td>
                            <td>{{ $log->sent_at ? $log->sent_at->format('M d, H:i') : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No log entries for this campaign. This can happen if the campaign was created before logs were saved, or if no emails were sent. Try sending a new email and check the log for that campaign.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Resend Progress Modal (custom overlay to avoid Bootstrap backdrop z-index issues) -->
<div id="resendProgressModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:99999; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div style="background:white; border-radius:8px; padding:24px; min-width:400px; max-width:90%; box-shadow:0 4px 20px rgba(0,0,0,0.3);">
        <h5 id="resendModalTitle" style="margin-bottom:16px;">Resending to Failed Recipients</h5>
        <div class="progress mb-3">
            <div class="progress-bar progress-bar-striped progress-bar-animated" id="resendProgressBar" role="progressbar" style="width: 0%"></div>
        </div>
        <p class="mb-3" id="resendStatusText">
            <strong>Sent:</strong> <span id="resendSentCount">0</span> &nbsp;
            <strong>Failed:</strong> <span id="resendFailedCount">0</span> &nbsp;
            <strong>Left:</strong> <span id="resendLeftCount">0</span>
        </p>
        <div style="text-align:right;">
            <button type="button" class="btn btn-primary" disabled id="resendCloseBtn">Please wait...</button>
        </div>
    </div>
</div>

@push('after_scripts')
<script>
(function() {
    var BATCH_SIZE = 5;

    document.querySelector('.resend-failed-btn')?.addEventListener('click', function() {
        var btn = this;
        var campaignId = btn.dataset.campaignId;
        var failedLogIds = JSON.parse(btn.dataset.failedLogIds || '[]');
        var failedCount = parseInt(btn.dataset.failedCount || '0', 10);

        if (failedLogIds.length === 0) return;

        if (!confirm('Send email again to ' + failedCount + ' failed users?')) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="la la-spinner la-spin"></i> Sending...';

        var total = failedLogIds.length;
        var processed = 0;
        var totalSent = 0;
        var totalFailed = 0;

        document.getElementById('resendProgressModal').style.display = 'flex';
        document.getElementById('resendSentCount').textContent = '0';
        document.getElementById('resendFailedCount').textContent = '0';
        document.getElementById('resendLeftCount').textContent = total;
        document.getElementById('resendProgressBar').style.width = '0%';
        document.getElementById('resendCloseBtn').disabled = true;
        document.getElementById('resendCloseBtn').textContent = 'Please wait...';

        function updateModal() {
            document.getElementById('resendSentCount').textContent = totalSent;
            document.getElementById('resendFailedCount').textContent = totalFailed;
            document.getElementById('resendLeftCount').textContent = total - processed;
            document.getElementById('resendProgressBar').style.width = Math.round((processed / total) * 100) + '%';
        }

        function processNextBatch() {
            var batch = failedLogIds.splice(0, BATCH_SIZE);
            if (batch.length === 0) {
                document.getElementById('resendProgressBar').classList.remove('progress-bar-animated');
                document.getElementById('resendProgressBar').style.width = '100%';
                document.getElementById('resendProgressBar').classList.add('bg-success');
                document.getElementById('resendStatusText').innerHTML = '<strong>Complete!</strong> Sent: ' + totalSent + ', Failed: ' + totalFailed;
                document.getElementById('resendCloseBtn').disabled = false;
                document.getElementById('resendCloseBtn').textContent = 'Close & Refresh';
                document.getElementById('resendCloseBtn').onclick = function() {
                    document.getElementById('resendProgressModal').style.display = 'none';
                    window.location.reload();
                };
                btn.disabled = false;
                btn.innerHTML = '<i class="la la-refresh"></i> Resend to Failed';
                document.getElementById('resendModalTitle').textContent = 'Resend Complete';
                return;
            }

            $.ajax({
                url: '{{ url(config("backpack.base.route_prefix")) }}/bulk-email/campaign/' + campaignId + '/retry-failed-batch',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    log_ids: batch
                },
                success: function(res) {
                    totalSent += res.sent || 0;
                    totalFailed += res.failed || 0;
                    processed += res.processed || batch.length;
                    updateModal();
                    processNextBatch();
                },
                error: function() {
                    totalFailed += batch.length;
                    processed += batch.length;
                    updateModal();
                    processNextBatch();
                }
            });
        }

        processNextBatch();
    });
})();
</script>
@endpush
@endsection
