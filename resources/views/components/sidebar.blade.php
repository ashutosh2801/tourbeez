<nav class="mt-2">
    

    <ul class="nav nav-pills nav-sidebar flex-column aiz-side-nav-list" data-toggle="aiz-side-menu" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Dashboard</p>
            </a>
        </li>
        @can('show_tours')       
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
                @can('show_addons')  
                <li class="aiz-side-nav-item">
                    <a href="{{ route('admin.addon.index') }}"
                        class="aiz-side-nav-link nav-link {{ Route::is('admin.addon.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-list-alt"></i>
                        <p>{{  translate('Extra ') }}
                            <span class="badge badge-warning right">{{ $AddonCount }}</span>
                        </p>
                    </a>
                </li>
                @endcan
                @can('show_categories')  
                <li class="aiz-side-nav-item">
                    <a href="{{ route('admin.category.index') }}"
                        class="aiz-side-nav-link nav-link {{ Route::is('admin.category.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-list-alt"></i>
                        <p>{{  translate('Categories') }}
                            <span class="badge badge-warning right">{{ $CategoryCount }}</span>
                        </p>
                    </a>
                </li>
                @endcan
                @can('show_pickups') 
                <li class="aiz-side-nav-item">
                    <a href="{{ route('admin.pickups.index') }}"
                        class="aiz-side-nav-link nav-link {{ Route::is('admin.pickups.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-list"></i>
                        <p>{{  translate('Pickups') }}
                            <span class="badge badge-warning right">{{ $PickupCount }}</span>
                        </p>
                    </a>
                </li>
                @endcan
                @can('show_tourtypes') 
                <li class="aiz-side-nav-item">
                    <a href="{{ route('admin.tour_type.index') }}"
                        class="aiz-side-nav-link nav-link {{ Route::is('admin.tour_type.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-list"></i>
                        <p>{{  translate('Tour Types') }}
                            <span class="badge badge-warning right">{{ $TourTypeCount }}</span>
                        </p>
                    </a>
                </li>
                @endcan
                @can('show_attributes') 
                <li class="aiz-side-nav-item">
                    <a href="javascript:void(0);" class="aiz-side-nav-link nav-link">
                        <i class="nav-icon fas fa-taxi"></i>
                        <p>{{  translate('Attributes') }}
                            <span class="aiz-side-nav-arrow right"></span>
                        </p>
                        
                    </a>
                    <ul class="aiz-side-nav-list level-3">
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('admin.countries.index') }}"
                                class="aiz-side-nav-link nav-link {{ Route::is('admin.countries.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-list"></i>
                                <p>{{  translate('Countries') }}
                                    <span class="badge badge-warning right">{{ $CountryCount }}</span>
                                </p>
                            </a>
                        </li>
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('admin.states.index') }}"
                                class="aiz-side-nav-link nav-link {{ Route::is('admin.states.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-list"></i>
                                <p>{{  translate('States') }}
                                    <span class="badge badge-warning right">{{ $StateCount }}</span>
                                </p>
                            </a>
                        </li>
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('admin.cities.index') }}"
                                class="aiz-side-nav-link nav-link {{ Route::is('admin.cities.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-list"></i>
                                <p>{{  translate('Cities') }}
                                    <span class="badge badge-warning right">{{ $CityCount }}</span>
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endcan
            </ul>
        </li>
        @endcan
        @can('show_orders') 
        <li class="nav-item">
            <a href="{{ route('admin.orders.index') }}"
                class="nav-link {{ areActiveRoutes(['uploaded-files.create']) }}">
                <i class="nav-icon fas fa-file"></i>
                <p>{{ translate('Orders') }}</p>
            </a>
        </li>
        @endcan 

        @can('show_orders') 


        <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link">
                <i class="nav-icon fas fa-briefcase"></i>
                <p>{{ translate('Manifest') }}
                    <span class="aiz-side-nav-arrow right"></span>
                </p>
                
            </a>
            <ul class="aiz-side-nav-list level-2">
                <li class="nav-item">
                    <a href="{{ route('admin.orders.manifest') }}"
                        class="aiz-side-nav-link nav-link {{ Route::is('admin.orders.manifest') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tasks"></i>
                        <p>{{ translate('Order Manifest') }}</p>
                    </a>
                </li>

                <li class="aiz-side-nav-list">
                    <a href="{{ route('admin.orders.tour.manifest') }}"
                        class="aiz-side-nav-link nav-link {{ Route::is('admin.orders.tour.manifest') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tasks"></i>
                        <p>{{ translate('Tour Manifest') }}</p>
                    </a>
                </li>
            </ul>
            </li>
        @endcan
        @can('show_customers') 
        <li class="nav-item">
            <a href="{{ route('admin.customers.index') }}"
                class="nav-link {{ areActiveRoutes(['customers.index']) }}">
                <i class="nav-icon fas fa-users"></i>
                <p>{{ translate('Customers') }}</p>
            </a>
        </li>
        @endcan   
        @can('show_users')  
        <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link">
                <i class="nav-icon fas fa-users"></i>
                <p>{{ translate('Staff')}}
                    <span class="aiz-side-nav-arrow right"></span>
                </p>                    
            </a>
            <ul class="aiz-side-nav-list level-2">
                @can('show_users')
                <li class="aiz-side-nav-item">
                    <a href="{{ route('admin.user.index') }}"
                        class="aiz-side-nav-link nav-link {{ Route::is('admin.user.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user"></i>
                        <p>{{ translate('Staff')}}
                            <span class="badge badge-info right">{{ $userCount }}</span>
                        </p>
                    </a>
                </li>
                @endcan   
                @role('Super Admin')
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
                @endrole
            </ul>
        </li>
        @endcan   
        @can('show_medias')
            <li class="nav-item">
                <a href="{{ route('admin.uploaded-files.index') }}"
                    class="nav-link {{ areActiveRoutes(['uploaded-files.create']) }}">
                    <i class="nav-icon fas fa-file"></i>
                    <p>{{ translate('Media') }}</p>
                </a>
            </li>
        @endcan   
        @can('general_settings')
        <li class="nav-item">
            <a href="javascript:void(0);" class="nav-link">
                <i class="nav-icon fas fa-cogs"></i>
                <p>{{ translate('Settings') }}
                    <span class="aiz-side-nav-arrow right"></span>
                </p>
                
            </a>
            <ul class="aiz-side-nav-list level-2">
                @can('general_settings')
                <li class="aiz-side-nav-item">
                    <a href="{{ route('admin.general_settings') }}" class="aiz-side-nav-link">
                        <i class="nav-icon fas fa-cog"></i>
                        <span class="aiz-side-nav-text">{{ translate('General Settings') }}</span>
                    </a>
                </li>
                @endcan   
                @can('general_settings')
                <li class="aiz-side-nav-item">
                    <a href="{{ route('admin.payment_method_settings') }}" class="aiz-side-nav-link">
                        <i class="nav-icon fas fa-cog"></i>
                        <span class="aiz-side-nav-text">{{ translate('Payment Settings') }}</span>
                    </a>
                </li>
                @endcan 
                @can('general_settings')
                <li class="aiz-side-nav-item">
                    <a href="{{ route('admin.third_party_settings') }}" class="aiz-side-nav-link">
                        <i class="nav-icon fas fa-cog"></i>
                        <span class="aiz-side-nav-text">{{ translate('Third Party Settings') }}</span>
                    </a>
                </li>
                @endcan
                @can('smtp_settings')
                <li class="aiz-side-nav-item">
                    <a href="{{ route('admin.email_settings') }}" class="aiz-side-nav-link">
                        <i class="nav-icon fas fa-envelope-open-text"></i>
                        <span class="aiz-side-nav-text">{{ translate('Mail Settings') }}</span>
                    </a>
                </li>
                @endcan   
                @can('email_templates')
                <li class="aiz-side-nav-item">
                    <a href="{{ route('admin.email-templates.index') }}" class="aiz-side-nav-link">
                        <i class="nav-icon fas fa-envelope-open-text"></i>
                        <span class="aiz-side-nav-text">{{ translate('Email Templates') }}</span>
                    </a>
                </li>
                @endcan   
                @can('general_settings')
                <li class="aiz-side-nav-item">
                    <a href="{{ route('admin.taxes.index') }}"
                        class="aiz-side-nav-link nav-link {{ Route::is('admin.taxes.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-list"></i>
                        <p>{{  translate('Taxes and Fees') }}
                            <span class="badge badge-warning right">{{ $TaxesCount }}</span>
                        </p>
                    </a>
                </li>
                @endcan   
            </ul>
        </li>
        @endcan   
        @can('activity_logs')
        <li class="nav-item">
            <a href="{{ route('admin.activity.logs') }}" class="nav-link {{ Route::is('admin.activity.logs') ? 'active' : '' }}">
                <i class="nav-icon fas fa-cog"></i>
                <p>Activity Logs</p>
            </a>
        </li>    
        @endcan
    </ul>
</nav>
