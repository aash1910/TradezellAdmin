@extends(backpack_view('blank'))

@section('header')
    <section class="container-fluid">
        <h2>
            <span class="text-capitalize">Campaign Log</span>
        </h2>
    </section>
@endsection

@section('content')
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
                    <a href="{{ url(config('backpack.base.route_prefix') . '/bulk-email/compose') }}" class="btn btn-sm btn-primary">
                        <i class="la la-envelope"></i> Compose New
                    </a>
                </div>
            </div>
            <div class="card-body">
                @php
                    $sentCount = $campaign->logs()->where('status', 'sent')->count();
                    $failedCount = $campaign->logs()->where('status', 'failed')->count();
                @endphp
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
@endsection
