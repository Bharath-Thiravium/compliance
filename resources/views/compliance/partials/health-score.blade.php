<!-- Compliance Health Score Card -->
@if (isset($healthScore))
    <div class="card">
        <div class="card-header {{ $healthScore['status'] === 'Excellent' ? 'success' : ($healthScore['status'] === 'Good' ? 'warning' : 'danger') }}">
            💚 Compliance Health Score
        </div>
        <div class="card-body">
            <div style="display:flex; align-items:center; gap:24px; flex-wrap:wrap;">
                <div style="text-align:center; flex-shrink:0;">
                    <h1 style="font-size:48px; margin:0; color:{{ $healthScore['status'] === 'Excellent' ? 'var(--color-success)' : ($healthScore['status'] === 'Good' ? 'var(--color-warning)' : 'var(--color-danger)') }};">
                        {{ $healthScore['percentage'] }}%
                    </h1>
                    <span class="badge {{ $healthScore['status'] === 'Excellent' ? 'badge-success' : ($healthScore['status'] === 'Good' ? 'badge-warning' : 'badge-danger') }}" style="font-size:16px; padding:6px 16px;">
                        {{ $healthScore['status'] }}
                    </span>
                </div>
                <div style="flex:1;">
                    <p style="margin-bottom:8px;"><strong>Score Breakdown:</strong></p>
                    <ul style="list-style:none; padding:0; margin:0;">
                        @foreach ($healthScore['breakdown'] as $metric => $score)
                            <li style="margin-bottom:8px;">
                                <span class="badge {{ $score >= 18 ? 'badge-success' : ($score >= 10 ? 'badge-warning' : 'badge-danger') }}" style="margin-right:8px;">
                                    {{ $score }}%
                                </span>
                                {{ $metric }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif
