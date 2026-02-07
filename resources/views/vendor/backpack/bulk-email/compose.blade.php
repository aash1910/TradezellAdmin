@extends(backpack_view('blank'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="la la-envelope"></i> Send Bulk Email
                </h3>
                <div class="card-tools">
                    <a href="{{ backpack_url('bulk-email/history') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="la la-history"></i> View History
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form id="bulk-email-form" action="{{ url(config('backpack.base.route_prefix') . '/bulk-email/send') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="subject">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" id="subject" class="form-control" required
                               value="{{ old('subject', $presetSubject ?? '') }}" placeholder="Email subject">
                        @error('subject')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="body">Message <span class="text-danger">*</span></label>
                        <textarea name="body" id="body" class="form-control" rows="10" required
                                  placeholder="Write your message here...">{{ old('body', $presetBody ?? '') }}</textarea>
                        @error('body')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Recipients</label>
                        @php $defaultSendTo = !empty($selectedUsers) ? 'selected' : 'all'; @endphp
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="send_to" id="send_to_all"
                                   value="all" {{ old('send_to', $defaultSendTo) === 'all' ? 'checked' : '' }}>
                            <label class="form-check-label" for="send_to_all">
                                Send to all users with email
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="send_to" id="send_to_selected"
                                   value="selected" {{ old('send_to', $defaultSendTo) === 'selected' ? 'checked' : '' }}>
                            <label class="form-check-label" for="send_to_selected">
                                Send to selected users only
                            </label>
                        </div>
                    </div>

                    <div id="selected-users-section" class="form-group" style="{{ (old('send_to', $defaultSendTo) === 'selected') ? '' : 'display:none;' }}">
                        <label>Selected Users ({{ count($selectedUsers ?? []) }})</label>
                        @if(!empty($selectedUsers))
                            <div class="border rounded p-2 bg-light" style="max-height: 200px; overflow-y: auto;">
                                @foreach($selectedUsers as $u)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="user_ids[]"
                                               value="{{ $u->id }}" id="user_{{ $u->id }}" checked>
                                        <label class="form-check-label" for="user_{{ $u->id }}">
                                            {{ $u->full_name }} ({{ $u->email }})
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-muted">Go to <a href="{{ backpack_url('user') }}{{ !empty($campaignId) ? '?compose_campaign_id=' . $campaignId : '' }}">Users</a> and use "Send Email" to select different users.</small>
                        @else
                            <p class="text-muted mb-0">
                                No users selected. Go to <a href="{{ backpack_url('user') }}{{ !empty($campaignId) ? '?compose_campaign_id=' . $campaignId : '' }}">Users</a>, select users, and click "Send Email to Selected" to return here with your email content preserved.
                            </p>
                        @endif
                    </div>

                    <hr>
                    <button type="submit" id="send-email-btn" class="btn btn-primary">
                        <i class="la la-paper-plane"></i> Send Email
                    </button>
                    <a href="{{ backpack_url('user') }}{{ !empty($campaignId) ? '?compose_campaign_id=' . $campaignId : '' }}" class="btn btn-outline-primary" title="Select users while keeping this email content">
                        <i class="la la-user"></i> Select Users
                    </a>
                    <a href="{{ backpack_url('bulk-email/history') }}" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

@push('after_scripts')
<script>
(function() {
    document.querySelectorAll('input[name="send_to"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var section = document.getElementById('selected-users-section');
            section.style.display = this.value === 'selected' ? 'block' : 'none';
        });
    });

    var form = document.getElementById('bulk-email-form');
    var btn = document.getElementById('send-email-btn');
    if (form && btn) {
        form.addEventListener('submit', function() {
            btn.disabled = true;
            btn.innerHTML = '<i class="la la-spinner la-spin"></i> Sending...';
        });
    }
})();
</script>
@endpush
@endsection
