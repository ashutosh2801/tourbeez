<nav class="main-header navbar navbar-expand navbar-dark navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto">
         <li class="nav-item">
            <a href="{{ route('admin.clear.cache') }}" class="btn btn-info btn-sm"> <i class="fas fa-wrench"></i> Clear Cache</a> &nbsp;
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.profile.edit') }}" class="btn btn-success btn-sm {{ Route::is('admin.profile.edit') ? 'active' : '' }}"><i class="nav-icon fas fa-user"></i> {{ Auth::user()->name }} Profile </a> &nbsp;
        </li>
        <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" name="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Log out
                    </button>    
                    {{-- <a :href="route('logout')"
                        onclick="event.preventDefault();
                                    this.closest('form').submit();"> <i class="fas fa-sign-out-alt"></i>
                        {{ __('Log Out') }}
                    </a> --}}
                </form>
        </li>
    </ul>
</nav>