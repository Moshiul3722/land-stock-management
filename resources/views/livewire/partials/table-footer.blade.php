{{-- Entry count + pagination for a Livewire CRUD list. Expects $paginator and $digits. --}}
@if ($paginator->total() > 0)
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $pages = collect(range(max(1, $current - 2), min($last, $current + 2)));
        $info = strtr(__('commontable.dt_info'), [
            '_TOTAL_' => $digits($paginator->total()),
            '_START_' => $digits($paginator->firstItem()),
            '_END_' => $digits($paginator->lastItem()),
        ]);
    @endphp
    <div class="crud-footer">
        <div class="crud-info" aria-live="polite">{{ $info }}</div>
        <nav aria-label="{{ __('commontable.pagination') }}">
            <ul class="pagination">
                <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                    <button type="button" class="page-link" wire:click="gotoPage(1)" @disabled($paginator->onFirstPage())
                        aria-label="{{ __('commontable.dt_first') }}">«</button>
                </li>
                <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                    <button type="button" class="page-link" wire:click="previousPage" @disabled($paginator->onFirstPage())
                        aria-label="{{ __('commontable.dt_previous') }}">‹</button>
                </li>
                @foreach ($pages as $page)
                    <li class="page-item {{ $page === $current ? 'active' : '' }}" wire:key="page-{{ $page }}">
                        <button type="button" class="page-link" wire:click="gotoPage({{ $page }})"
                            @if ($page === $current) aria-current="page" @endif>{{ $digits($page) }}</button>
                    </li>
                @endforeach
                <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                    <button type="button" class="page-link" wire:click="nextPage" @disabled(! $paginator->hasMorePages())
                        aria-label="{{ __('commontable.dt_next') }}">›</button>
                </li>
                <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                    <button type="button" class="page-link" wire:click="gotoPage({{ $last }})" @disabled(! $paginator->hasMorePages())
                        aria-label="{{ __('commontable.dt_last') }}">»</button>
                </li>
            </ul>
        </nav>
    </div>
@endif
