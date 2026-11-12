<?php

namespace App\Livewire\Concerns;

use App\Services\MenuPermissionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;

/**
 * Shared behaviour of the Livewire CRUD list screens: search box, page size, permission
 * check for every action, localized digits and top-right notifications.
 *
 * The using component adds `use WithPagination;` and defines:
 *   protected function permissionRoutes(): array   // menu routes that grant access
 */
trait ManagesCrudTable
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'per', except: 10)]
    public int $perPage = 10;

    /** @var array<int, int> */
    protected array $perPageOptions = [10, 25, 50, 100];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        if (! in_array((int) $this->perPage, $this->perPageOptions, true)) {
            $this->perPage = 10;
        }
        $this->resetPage();
    }

    /** Livewire requests bypass the `permission` route middleware, so every action re-checks. */
    protected function authorizeAccess(): void
    {
        $service = app(MenuPermissionService::class);
        $user = Auth::user();

        foreach ($this->permissionRoutes() as $route) {
            if ($service->allows($user, $route)) {
                return;
            }
        }

        abort(403);
    }

    protected function notify(string $type, string $message): void
    {
        $this->dispatch('app-toast', type: $type, message: $message);
    }

    protected function localDigits($value): string
    {
        $value = (string) $value;

        return app()->getLocale() === 'bn'
            ? strtr($value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : $value;
    }

    /** Variables every CRUD list view needs. */
    protected function tableViewData(): array
    {
        return [
            'perPageOptions' => $this->perPageOptions,
            'digits' => fn ($value) => $this->localDigits($value),
        ];
    }
}
