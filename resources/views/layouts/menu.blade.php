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
            @endphp
            <li class="nav-item has-treeview {{ $isModuleActive ? 'menu-open' : '' }}">
                <a href="#" class="nav-link {{ $isModuleActive ? 'active' : '' }}">
                <i class="fas fa-users-cog custom-icon"></i>
                    <p>
                        {{ $module }}
                        <i class="right fas fa-angle-left"></i>
                    </p>
                </a>
                <ul class="nav nav-treeview">
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
                        <li class="nav-item has-treeview {{ $isSubmoduleActive ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $isSubmoduleActive ? 'active' : '' }}">
                            <i class="fas fa-angle-double-right custom-icon"></i>
                                <p class="ml-2">
                                    {{ $submodule }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @foreach ($menus as $menu)  
                                    @if ($menu['is_menu'] == 1)
                                        <li class="nav-item">
                                            <a href="{{ route($menu['route']) }}" wire:navigate class="nav-link {{ request()->routeIs($menu['route']) ? 'active' : '' }}" ml-4>
                                            <i class="fas fa-chevron-circle-right custom-icon"></i>
                                                <p class="ml-3">
                                                    {{ $menu['menu_name'] }}
                                                </p>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endforeach
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
