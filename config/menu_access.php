<?php

/*
|--------------------------------------------------------------------------
| Which menu permission covers which route
|--------------------------------------------------------------------------
|
| The "Menu Permission" page grants a role the pages listed in cnf_menu. Every
| other route behind the `permission` middleware (saving, deleting, approving,
| printing, detail pages ...) belongs to one of those pages and is listed here.
|
|   open    - any signed-in user (own profile, own notifications, language,
|             look-up lists used by forms)
|   parents - route => menu routes; the role needs at least one of them.
|             A trailing ".*" covers every route with that prefix.
|
| A route that is neither a menu in cnf_menu nor listed here is refused, so a
| new route must be added to one of the two lists (or to cnf_menu).
|
*/

return [

    'open' => [
        'profile.index', 'profile.update', 'profile.password',
        'change.language',
        'notifications.index', 'notifications.read', 'notifications.read_all',
        // Look-up lists for the form drop-downs
        'get.sub_categories', 'products.by_sub_category', 'products.by_category',
        'products.autocomplete_unit', 'lots.autocomplete', 'stock.check_status',
    ],

    'parents' => [
        // Configuration > Role management
        'cnfRoles.store' => ['cnfRoles.create'],
        'cnfRoles.show' => ['cnfRoles.index'],
        'cnfRoles.update' => ['cnfRoles.edit'],
        'cnfRoles.destroy' => ['cnfRoles.edit'],
        'cnfPermissions.*' => ['cnfPermissions.index'],
        'getallmenusbyrole' => ['cnfPermissions.index'],
        'savepermission' => ['cnfPermissions.index'],
        'getAllRoles' => ['cnfPermissions.index', 'users.index', 'users.create', 'users.edit'],
        // Module / submodule / menu set-up pages (not in the sidebar)
        'cnfModules.*' => ['cnfPermissions.index'],
        'cnfSubmodules.*' => ['cnfPermissions.index'],
        'cnfMenus.*' => ['cnfPermissions.index'],
        'years.*' => ['cnfPermissions.index'],

        // Configuration > User management
        'users.store' => ['users.create'],
        'users.update' => ['users.edit'],
        'users.destroy' => ['users.edit'],
        'getallusersbyrole' => ['users.index'],
        'getallusersbyroleblade' => ['users.index'],
        'getallusertypes' => ['users.index', 'users.create', 'users.edit'],
        'departments.store' => ['departments.index'],
        'departments.update' => ['departments.index'],
        'departments.destroy' => ['departments.index'],

        // Product set-up
        'categories.store' => ['categories.create', 'categories.index'],
        'categories.show' => ['categories.create', 'categories.index'],
        'categories.edit' => ['categories.create', 'categories.index'],
        'categories.update' => ['categories.create', 'categories.index'],
        'categories.destroy' => ['categories.create', 'categories.index'],
        'subcategories.store' => ['subcategories.create', 'subcategories.index'],
        'subcategories.edit' => ['subcategories.create', 'subcategories.index'],
        'subcategories.destroy' => ['subcategories.create', 'subcategories.index'],
        'products.store' => ['products.create', 'products.index'],
        'products.show' => ['products.create', 'products.index'],
        'products.edit' => ['products.create', 'products.index'],
        'products.update' => ['products.create', 'products.index'],
        'products.destroy' => ['products.create', 'products.index'],

        // Stock management > Lots
        'lots.store' => ['lots.create', 'lots.index'],
        'lots.show' => ['lots.index', 'lots.create', 'lots.entry.index'],
        'lots.edit' => ['lots.index', 'lots.create'],
        'lots.update' => ['lots.index', 'lots.create'],
        'lots.destroy' => ['lots.index', 'lots.create'],
        'lots.approve' => ['lots.entry.index'],
        'lots.reject' => ['lots.entry.index'],

        // Stock management > Budgets
        'budgets.store' => ['budgets.create', 'budgets.index'],
        'budgets.show' => ['budgets.create', 'budgets.index'],
        'budgets.edit' => ['budgets.create', 'budgets.index'],
        'budgets.update' => ['budgets.create', 'budgets.index'],
        'budgets.destroy' => ['budgets.create', 'budgets.index'],
        'ptbudgets.store' => ['ptbudgets.create', 'ptbudgets.index'],
        'ptbudgets.show' => ['ptbudgets.create', 'ptbudgets.index'],
        'ptbudgets.edit' => ['ptbudgets.create', 'ptbudgets.index'],
        'ptbudgets.update' => ['ptbudgets.create', 'ptbudgets.index'],
        'ptbudgets.destroy' => ['ptbudgets.create', 'ptbudgets.index'],

        // Requisitions: the requester's own list
        'requisitions.store' => ['requisitions.create'],
        'requisitions.create_modal' => ['requisitions.create'],
        'requisitions.show' => ['requisitions.index', 'requisitions.pending'],
        'requisitions.edit' => ['requisitions.index'],
        'requisitions.edit_modal' => ['requisitions.index'],
        'requisitions.update' => ['requisitions.index'],
        'requisitions.destroy' => ['requisitions.index'],
        'requisitions.acknowledge' => ['requisitions.index'],
        'requisitions.get_details' => ['requisitions.index', 'requisitions.pending', 'stock.department_issues', 'stock.issues'],
        // Requisitions: deciding on the requests
        'requisitions.approve' => ['requisitions.pending'],
        'requisitions.reject' => ['requisitions.pending'],
        'requisitions.update_items' => ['requisitions.pending'],
        'requisitions.remove_items' => ['requisitions.pending'],
        'requisitions.split' => ['requisitions.pending'],
        'requisitions.verify_scan' => ['requisitions.pending'],
    ],
];
