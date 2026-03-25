<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="nav-icon la la-dashboard"></i> <span>{{ trans('backpack::base.dashboard') }}</span></a></li>
<li class='nav-item'><a class='nav-link' href="{{ backpack_url('listing') }}"><i class='nav-icon la la-box'></i> Listings</a></li>
<li class='nav-item'><a class='nav-link' href="{{ backpack_url('match') }}"><i class='nav-icon la la-heart'></i> Matches</a></li>
<li class='nav-item'><a class='nav-link' href="{{ backpack_url('report') }}"><i class='nav-icon la la-flag'></i> Reports</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('review') }}'><i class='nav-icon la la-star'></i> Reviews</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('faq') }}'><i class='nav-icon la la-question-circle'></i> Faqs</a></li>

<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-comments"></i> Messaging</a>
  <ul class="nav-dropdown-items">
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('message-conversations') }}"><i class="nav-icon la la-comments-o"></i> <span>Conversations</span></a></li>
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('message') }}"><i class="nav-icon la la-envelope"></i> <span>All Messages</span></a></li>
  </ul>
</li>

<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-group"></i> Authentication</a>
  <ul class="nav-dropdown-items">
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>Users</span></a></li>
    <!-- <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-group"></i> <span>Roles</span></a></li> -->
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('bulk-email/compose') }}"><i class="nav-icon la la-envelope"></i> <span>Send Bulk Email</span></a></li>
    {{-- <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Permissions</span></a></li> --}}
  </ul>
</li>



