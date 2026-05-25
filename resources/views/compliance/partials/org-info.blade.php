<!-- Organization Information Card -->
@if (isset($subscription))
    <div class="card mb-3">
        <div class="card-header">🏢 Organization Information</div>
        <div class="card-body">
            <div class="grid-row">
                <div class="grid-col col-1-3">
                    <p class="mb-2"><strong>Organization:</strong><br>{{ Auth::user()->tenant->name ?? 'N/A' }}</p>
                    <p class="mb-2">
                        <strong>Subscription:</strong><br>
                        <span class="badge {{ $subscription === 'FULL' ? 'badge-success' : 'badge-default' }}">
                            {{ $subscription }}
                        </span>
                    </p>
                </div>
                <div class="grid-col col-1-3">
                    @if (isset($branch))
                        <p class="mb-2"><strong>Branch:</strong><br>{{ $branch->branch_name ?? 'N/A' }}</p>
                        <p class="mb-2"><strong>License No:</strong><br>{{ $branch->factory_license_number ?? '-' }}</p>
                    @else
                        <p class="mb-2 text-muted">No branch assigned</p>
                    @endif
                </div>
                <div class="grid-col col-1-3">
                    @if (isset($branch))
                        <p class="mb-2"><strong>PF Code:</strong><br>{{ $branch->pf_code ?? '-' }}</p>
                        <p class="mb-2"><strong>ESI Code:</strong><br>{{ $branch->esi_code ?? '-' }}</p>
                    @endif
                    <p class="mb-2"><strong>Logged in as:</strong><br>{{ $user->name ?? Auth::user()->name }}</p>
                </div>
            </div>
        </div>
    </div>
@endif
