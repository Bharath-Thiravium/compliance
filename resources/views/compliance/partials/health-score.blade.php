<!-- Compliance Health Score Card -->
@if (isset($healthScore))
    <div class="ant-card">
        <div class="ant-card-head {{ $healthScore['status'] === 'Excellent' ? 'success' : ($healthScore['status'] === 'Good' ? 'warning' : 'danger') }}">
            💚 Compliance Health Score
        </div>
        <div class="ant-card-body">
            <div class="ant-row align-items-center">
                <div class="ant-col ant-col-4 text-center">
                    <h1 style="font-size: 48px; margin: 0; color: {{ $healthScore['status'] === 'Excellent' ? '#52c41a' : ($healthScore['status'] === 'Good' ? '#faad14' : '#ff4d4f') }};">
                        {{ $healthScore['percentage'] }}%
                    </h1>
                    <span class="ant-tag {{ $healthScore['status'] === 'Excellent' ? 'ant-tag-success' : ($healthScore['status'] === 'Good' ? 'ant-tag-warning' : 'ant-tag-error') }}" style="font-size: 16px; padding: 6px 16px;">
                        {{ $healthScore['status'] }}
                    </span>
                </div>
                <div class="ant-col ant-col-8">
                    <p class="mb-2"><strong>Score Breakdown:</strong></p>
                    <ul style="list-style: none; padding: 0;">
                        @foreach ($healthScore['breakdown'] as $metric => $score)
                            <li class="mb-2">
                                <span class="ant-tag {{ $score >= 18 ? 'ant-tag-success' : ($score >= 10 ? 'ant-tag-warning' : 'ant-tag-error') }}" style="margin-right: 8px;">
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
