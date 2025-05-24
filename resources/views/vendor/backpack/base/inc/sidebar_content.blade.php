<li class="nav-item"><a class="nav-link" href="{{ backpack_url('dashboard') }}"><i class="nav-icon la la-dashboard"></i> <span>{{ trans('backpack::base.dashboard') }}</span></a></li>
<li class='nav-item'><a class='nav-link' href="{{ backpack_url('package') }}"><i class='nav-icon la la-truck'></i> Packages</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('order') }}'><i class='nav-icon la la-shopping-cart'></i> Orders</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('review') }}'><i class='nav-icon la la-star'></i> Reviews</a></li>
<li class='nav-item'><a class='nav-link' href='{{ backpack_url('faq') }}'><i class='nav-icon la la-question-circle'></i> Faqs</a></li>

<li class="nav-item nav-dropdown">
  <a class="nav-link nav-dropdown-toggle" href="#"><i class="nav-icon la la-group"></i> Authentication</a>
  <ul class="nav-dropdown-items">
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('user') }}"><i class="nav-icon la la-user"></i> <span>Users</span></a></li>
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('role') }}"><i class="nav-icon la la-group"></i> <span>Roles</span></a></li>
    <li class="nav-item"><a class="nav-link" href="{{ backpack_url('permission') }}"><i class="nav-icon la la-key"></i> <span>Permissions</span></a></li>
  </ul>
</li>



