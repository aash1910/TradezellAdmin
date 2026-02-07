@extends(backpack_view('blank'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="la la-history"></i> Bulk Email History
                </h3>
                <div class="card-tools">
                    <a href="{{ url(config('backpack.base.route_prefix') . '/bulk-email/compose') }}" class="btn btn-sm btn-primary">
                        <i class="la la-envelope"></i> Compose New
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Recipients</th>
                            <th>Sent</th>
                            <th>Failed</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $campaign)
                        <tr>
                            <td>{{ $campaign->id }}</td>
                            <td>{{ Str::limit($campaign->subject, 40) }}</td>
                            <td>{{ $campaign->recipient_count }}</td>
                            <td><span class="badge badge-success">{{ $campaign->sent_count ?? $campaign->logs()->where('status', 'sent')->count() }}</span></td>
                            <td><span class="badge badge-danger">{{ $campaign->failed_count ?? $campaign->logs()->where('status', 'failed')->count() }}</span></td>
                            <td>{{ $campaign->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ url(config('backpack.base.route_prefix') . '/bulk-email/campaign/' . $campaign->id . '/log') }}" class="btn btn-sm btn-outline-primary" title="View recipients and status">
                                    <i class="la la-list"></i> View Log
                                </a>
                                <a href="{{ url(config('backpack.base.route_prefix') . '/bulk-email/compose?campaign_id=' . $campaign->id) }}" class="btn btn-sm btn-outline-success" title="Send same email again">
                                    <i class="la la-reply"></i> Use Again
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No campaigns yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($campaigns->hasPages())
            <div class="card-footer">
                {{ $campaigns->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
