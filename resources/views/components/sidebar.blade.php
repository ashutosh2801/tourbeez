<nav class="mt-2">
    

    <ul class="nav nav-pills nav-sidebar flex-column aiz-side-nav-list" data-toggle="aiz-side-menu" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>
        @role(['admin'])
            <li class="nav-item">
                <a href="javascript:void(0);" class="nav-link">
                    <i class="nav-icon fas fa-taxi"></i>
                    <p>{{  translate('Inventory') }}
                        <span class="aiz-side-nav-arrow right"></span>
                    </p>
                    
                </a>
                <ul class="aiz-side-nav-list level-2">
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('admin.tour.index') }}"
                            class="aiz-side-nav-link nav-link {{ Route::is('admin.tour.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-th"></i>
                            <p>{{  translate('Tours') }}
                                <span class="badge badge-warning right">{{ $TourCount }}</span>
                            </p>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('admin.addon.index') }}"
                            class="aiz-side-nav-link nav-link {{ Route::is('admin.addon.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-list-alt"></i>
                            <p>{{  translate('Extra ') }}
                                <span class="badge badge-warning right">{{ $AddonCount }}</span>
                            </p>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('admin.category.index') }}"
                            class="aiz-side-nav-link nav-link {{ Route::is('admin.category.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-list-alt"></i>
                            <p>{{  translate('Categories') }}
                                <span class="badge badge-warning right">{{ $CategoryCount }}</span>
                            </p>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('admin.pickups.index') }}"
                            class="aiz-side-nav-link nav-link {{ Route::is('admin.pickups.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-list"></i>
                            <p>{{  translate('Pickups') }}
                                <span class="badge badge-warning right">{{ $PickupCount }}</span>
                            </p>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('admin.tour_type.index') }}"
                            class="aiz-side-nav-link nav-link {{ Route::is('admin.tour_type.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-list"></i>
                            <p>{{  translate('Tour Types') }}
                                <span class="badge badge-warning right">{{ $TourTypeCount }}</span>
                            </p>
                        </a>
                    </li>
                 </ul>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0);" class="nav-link">
                    <i class="nav-icon fas fa-users"></i>
                    <p>{{ translate('Staff')}}
                        <span class="aiz-side-nav-arrow right"></span>
                    </p>
                    
                </a>
                <ul class="aiz-side-nav-list level-2">
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('admin.user.index') }}"
                            class="aiz-side-nav-link nav-link {{ Route::is('admin.user.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user"></i>
                            <p>{{ translate('Staff')}}
                                <span class="badge badge-info right">{{ $userCount }}</span>
                            </p>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('admin.role.index') }}"
                            class="aiz-side-nav-link nav-link {{ Route::is('admin.role.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-user-tag"></i>
                            <p>Role
                                <span class="badge badge-success right">{{ $RoleCount }}</span>
                            </p>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('admin.permission.index') }}"
                            class="aiz-side-nav-link nav-link {{ Route::is('admin.permission.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-hat-cowboy"></i>
                            <p>{{ translate('Permission') }}
                                <span class="badge badge-danger right">{{ $PermissionCount }}</span>
                            </p>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.uploaded-files.index') }}"
                    class="nav-link {{ areActiveRoutes(['uploaded-files.create']) }}">
                    <i class="nav-icon fas fa-file"></i>
                    <p>{{ translate('Media') }}</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="javascript:void(0);" class="nav-link">
                    <i class="nav-icon fas fa-cogs"></i>
                    <p>{{ translate('Settings') }}
                        <span class="aiz-side-nav-arrow right"></span>
                    </p>
                    
                </a>
                <ul class="aiz-side-nav-list level-2">
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('admin.general_settings') }}" class="aiz-side-nav-link">
                            <i class="nav-icon fas fa-cog"></i>
                            <span class="aiz-side-nav-text">{{ translate('General Settings') }}</span>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('admin.email_settings') }}" class="aiz-side-nav-link">
                            <i class="nav-icon fas fa-envelope-open-text"></i>
                            <span class="aiz-side-nav-text">{{ translate('Mail Settings') }}</span>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('admin.email-templates.index') }}" class="aiz-side-nav-link">
                            <i class="nav-icon fas fa-envelope-open-text"></i>
                            <span class="aiz-side-nav-text">{{ translate('Email Templates') }}</span>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('admin.taxes.index') }}"
                            class="aiz-side-nav-link nav-link {{ Route::is('admin.taxes.index') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-list"></i>
                            <p>{{  translate('Taxes and Fees') }}
                                <span class="badge badge-warning right">{{ $TaxesCount }}</span>
                            </p>
                        </a>
                    </li>
                </ul>
            </li>
            
            
        @endrole
        <!-- <li class="nav-item">
            <a href="{{ route('admin.profile.edit') }}"
                class="nav-link {{ Route::is('admin.profile.edit') ? 'active' : '' }}">
                <i class="nav-icon fas fa-id-card"></i>
                <p>Profile</p>
            </a>
        </li> -->

    </ul>
</nav>
