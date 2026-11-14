<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ url('/home') }}" wire:navigate class="brand-link">
        <img src="{{ asset('theme/assets/img/gov.png') }}"
             alt="{{ config('app.name') }} Logo"
             class="brand-image">
        <span class="brand-text font-weight-bold small-size">Inventory Management System</span>
    </a>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="true">
                @include('layouts.menu')
            </ul>
        </nav>
    </div>
</aside>

<style>
    .brand-link {
        padding: 0.42rem 1rem; /* Reduce padding */
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    .brand-image {
        max-height: auto; /* Adjust image size as necessary */
    }
    .small-size {
        font-size: 0.6rem; /* Adjust font size as necessary */
    }
    .sidebar-divider {
        border-top: 1px solid #4f5962;
        margin: auto;

    }
</style>
