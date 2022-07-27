<!-- {{ $menuActive = strtolower($menuActive) }} -->

<ul class="nav">
  <li class="nav-item {{($menuActive == 'dashboard')?'active':'non-active' }} ">
    <a class="nav-link" href="{{route('viewDashboard')}}">
      <i class="material-icons">dashboard</i>
      <p> Dashboard </p>
    </a>
  </li>
  <!-- <li class="nav-item">
    <a class="nav-link" href="#" data-toggle="modal" data-target="#myModal10">
        <i class="material-icons">power_settings_new</i>
        <p> Logout </p>
      </button>
    </a>
  </li> -->
</ul>
