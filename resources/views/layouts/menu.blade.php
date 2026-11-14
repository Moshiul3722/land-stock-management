    <ul class="nav">
        <li class="nav-item">
        <a href="{{ route('home') }}" wire:navigate class="nav-link dashboard {{ request()->routeIs('home') ? 'active' : '' }}">
        <i class="fas fa-home custom-icon"></i>
                <p>{{ app()->getLocale() === 'en' ? 'Dashboard' : 'ড্যাশবোর্ড' }}</p>
            </a>
        </li>

        @foreach ($menuItems as $module => $submodules)
            @php
                $isModuleActive = false;
                foreach ($submodules as $submodule => $menus) {
                    foreach ($menus as $menu) {
                        if (request()->routeIs($menu['route'])) {
                            $isModuleActive = true;
                            break 2;
                        }
                    }
                }
                // The requisition module has no heading of its own: its groups ("Requisition
                // Management", "My Products") stand at the top level, with their pages right under them.
                $moduleRoutes = array_column(array_merge(...array_values($submodules)), 'route');
                $promote = (bool) array_intersect($moduleRoutes, ['requisitions.index', 'requisitions.pending']);
            @endphp
            @if (! $promote)
            <li class="nav-item has-treeview {{ $isModuleActive ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isModuleActive ? 'active' : '' }}">
                <i class="fas fa-users-cog custom-icon"></i>
                    <p>
                        {{ $module }}
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
            @endif
                    @foreach ($submodules as $submodule => $menus)
                        @php
                            $isSubmoduleActive = false;
                            foreach ($menus as $menu) {
                                if (request()->routeIs($menu['route'])) {
                                    $isSubmoduleActive = true;
                                    break;
                                }
                            }
                        @endphp
                        @php
                            // Groups listed here open their page directly instead of unfolding a
                            // one-item list (the route is the group's only sidebar entry).
                            $visibleMenus = array_values(array_filter($menus, fn ($menu) => $menu['is_menu'] == 1));
                            $directLink = count($visibleMenus) === 1 && in_array($visibleMenus[0]['route'], ['categories.create', 'product-units.index', 'products.create'], true)
                                ? $visibleMenus[0]
                                : null;
                        @endphp
                        @php
                            // Groups listed here have no heading of their own: their pages are listed
                            // straight under the module (e.g. Reports > Stock report, not Reports > Reports > ...).
                            $flatten = (bool) array_intersect(array_column($visibleMenus, 'route'), ['reports.stock.current', 'reports.consumption']);
                        @endphp
                        {{-- A group whose pages are all hidden from the sidebar gets no heading --}}
                        @if (empty($visibleMenus))
                            @continue
                        @endif
                        @if ($flatten)
                            @foreach ($visibleMenus as $menu)
                                <li class="nav-item">
                                    <a href="{{ route($menu['route']) }}" wire:navigate class="nav-link {{ request()->routeIs($menu['route']) ? 'active' : '' }}">
                                        <i class="fas fa-chevron-circle-right custom-icon"></i>
                                        <p class="ml-2">{{ $menu['menu_name'] }}</p>
                                    </a>
                                </li>
                            @endforeach
                            @continue
                        @endif
                        @if ($directLink)
                            <li class="nav-item">
                                <a href="{{ route($directLink['route']) }}" wire:navigate class="nav-link {{ $isSubmoduleActive ? 'active' : '' }}">
                                    <i class="fas fa-angle-double-right custom-icon"></i>
                                    <p class="ml-2">{{ $submodule }}</p>
                                </a>
                            </li>
                            @continue
                        @endif
                        <li class="nav-item has-treeview {{ $isSubmoduleActive ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $isSubmoduleActive ? 'active' : '' }}">
                            {{-- A promoted group looks like a module; its pages like a module's first level --}}
                            <i class="fas {{ $promote ? 'fa-users-cog' : 'fa-angle-double-right' }} custom-icon"></i>
                                <p class="{{ $promote ? '' : 'ml-2' }}">
                                    {{ $submodule }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @foreach ($menus as $menu)  
                                    @if ($menu['is_menu'] == 1)
                                        <li class="nav-item">
                                            <a href="{{ route($menu['route']) }}" wire:navigate class="nav-link {{ request()->routeIs($menu['route']) ? 'active' : '' }}" ml-4>
                                            <i class="fas {{ $promote ? 'fa-angle-double-right' : 'fa-chevron-circle-right' }} custom-icon"></i>
                                                <p class="{{ $promote ? 'ml-2' : 'ml-3' }}">
                                                    {{ $menu['menu_name'] }}
                                                </p>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
            @if (! $promote)
                </ul>
            </li>
            @endif
        @endforeach

        {{-- Own profile (name, designation, signature, password): for every user --}}
        <li class="nav-item">
            <a href="{{ route('profile.index') }}" wire:navigate class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fas fa-user-circle custom-icon"></i>
                <p>{{ app()->getLocale() === 'en' ? 'Profile' : 'প্রোফাইল' }}</p>
            </a>
        </li>
    </ul>

</li>



{{-- <li class="nav-item">
    <a href="{{ route('studentsApplicationForms.index') }}" class="nav-link {{ Request::is('studentsApplicationForms*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Students Application Forms</p>
    </a>
</li>


<li class="nav-item">
    <a href="{{ route('studentClasses.index') }}" class="nav-link {{ Request::is('studentClasses*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Student Classes</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('instituteShifts.index') }}" class="nav-link {{ Request::is('instituteShifts*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Institute Shifts</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('studentsApplicationForms.index') }}" class="nav-link {{ Request::is('studentsApplicationForms*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Students Application Forms</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('scholarshipSubCategories.index') }}" class="nav-link {{ Request::is('scholarshipSubCategories*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Scholarship Sub Categories</p>
    </a>
</li> --}}
