@if ($crud->get('list.bulkActions'))
	<a href="javascript:void(0)" onclick="sendBulkEmailToSelected(this)" class="btn btn-sm btn-primary bulk-button"><i class="la la-envelope"></i> Send Email to Selected</a>
@endif

@push('after_scripts')
<script>
	if (typeof sendBulkEmailToSelected != 'function') {
	  function sendBulkEmailToSelected(button) {
	      if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0) {
	      	new Noty({
	          type: "warning",
	          text: "<strong>No entries selected</strong><br>Please select one or more users to send email to."
	        }).show();
	      	return;
	      }
	      var baseUrl = "{{ url(config('backpack.base.route_prefix') . '/bulk-email/compose') }}";
	      var params = new URLSearchParams(window.location.search);
	      var campaignId = params.get('compose_campaign_id');
	      var url = baseUrl + "?user_ids=" + crud.checkedItems.join(',');
	      if (campaignId) url += "&campaign_id=" + campaignId;
	      window.location.href = url;
	  }
	}
</script>
@endpush
