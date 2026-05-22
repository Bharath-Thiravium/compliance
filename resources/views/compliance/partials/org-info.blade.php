<!-- Organization Information Card -->
@if (isset($subscription))
    <div class="ant-card">
        <div class="ant-card-head">🏢 Organization Information</div>
        <div class="ant-card-body">
            <div class="ant-row">
                <div class="ant-col ant-col-4">
                    <p class="mb-2"><strong>Organization:</strong><br>{{ Auth::user()->tenant->name ?? 'N/A' }}</p>
                    <p class="mb-2">
                        <strong>Subscription:</strong><br>
                        <span class="ant-tag {{ $subscription === 'FULL' ? 'ant-tag-success' : 'ant-tag-default' }}">
                            {{ $subscription }}
                        </span>
                    </p>
                </div>
                <div class="ant-col ant-col-4">
                    @if (isset($branch))
                        <p class="mb-2"><strong>Branch:</strong><br>{{ $branch->branch_name ?? 'N/A' }}</p>
                        <p class="mb-2"><strong>License No:</strong><br>{{ $branch->factory_license_number ?? '-' }}</p>
                    @else
                        <p class="mb-2 text-muted">No branch assigned</p>
                    @endif
                </div>
                <div class="ant-col ant-col-4">
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
