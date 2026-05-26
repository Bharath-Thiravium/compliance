<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🔬 Production Diagnostics — Compliance Engine</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #0f1117; color: #e2e8f0; min-height: 100vh; }

  .header { background: linear-gradient(135deg, #1a1f2e 0%, #0f1117 100%); border-bottom: 1px solid #2d3748; padding: 24px 32px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
  .header h1 { font-size: 22px; font-weight: 700; color: #fff; }
  .header .meta { font-size: 13px; color: #718096; }

  .score-bar { background: #1a1f2e; border-bottom: 1px solid #2d3748; padding: 20px 32px; display: flex; align-items: center; gap: 32px; flex-wrap: wrap; }
  .score-circle { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; border: 4px solid; flex-shrink: 0; }
  .score-circle.green  { border-color: #48bb78; color: #48bb78; }
  .score-circle.yellow { border-color: #ecc94b; color: #ecc94b; }
  .score-circle.red    { border-color: #fc8181; color: #fc8181; }

  .score-stats { display: flex; gap: 24px; flex-wrap: wrap; }
  .stat-pill { padding: 6px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; }
  .stat-pill.pass    { background: #1c4532; color: #68d391; }
  .stat-pill.warning { background: #744210; color: #f6e05e; }
  .stat-pill.error   { background: #742a2a; color: #fc8181; }
  .stat-pill.info    { background: #1a365d; color: #90cdf4; }

  .container { max-width: 1200px; margin: 0 auto; padding: 32px; }

  .check-card { background: #1a1f2e; border: 1px solid #2d3748; border-radius: 10px; margin-bottom: 16px; overflow: hidden; }
  .check-header { padding: 14px 20px; display: flex; align-items: center; gap: 12px; cursor: pointer; user-select: none; }
  .check-header:hover { background: rgba(255,255,255,0.03); }
  .status-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
  .status-dot.pass    { background: #48bb78; box-shadow: 0 0 6px #48bb78; }
  .status-dot.warning { background: #ecc94b; box-shadow: 0 0 6px #ecc94b; }
  .status-dot.error   { background: #fc8181; box-shadow: 0 0 6px #fc8181; }
  .check-name { font-weight: 600; font-size: 15px; flex: 1; }
  .check-badge { font-size: 11px; padding: 2px 10px; border-radius: 10px; font-weight: 600; }
  .check-badge.pass    { background: #1c4532; color: #68d391; }
  .check-badge.warning { background: #744210; color: #f6e05e; }
  .check-badge.error   { background: #742a2a; color: #fc8181; }
  .chevron { color: #718096; font-size: 12px; transition: transform 0.2s; }
  .chevron.open { transform: rotate(180deg); }

  .check-body { padding: 0 20px 16px; display: none; }
  .check-body.open { display: block; }

  .section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #718096; margin: 14px 0 6px; }

  .issue-list { list-style: none; }
  .issue-list li { padding: 6px 10px; border-radius: 6px; font-size: 13px; margin-bottom: 4px; display: flex; align-items: flex-start; gap: 8px; }
  .issue-list li.error   { background: #2d1515; color: #fc8181; border-left: 3px solid #fc8181; }
  .issue-list li.warning { background: #2d2415; color: #f6e05e; border-left: 3px solid #ecc94b; }
  .issue-list li.info    { background: #151d2d; color: #90cdf4; border-left: 3px solid #4299e1; }
  .issue-list li .icon   { flex-shrink: 0; margin-top: 1px; }

  .info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 8px; }
  .info-item { background: #0f1117; border: 1px solid #2d3748; border-radius: 6px; padding: 8px 12px; font-size: 12px; }
  .info-item .key   { color: #718096; margin-bottom: 2px; }
  .info-item .value { color: #e2e8f0; font-weight: 500; word-break: break-all; }

  .log-lines { background: #0a0d14; border: 1px solid #2d3748; border-radius: 6px; padding: 12px; font-family: 'Courier New', monospace; font-size: 11px; color: #a0aec0; max-height: 300px; overflow-y: auto; white-space: pre-wrap; word-break: break-all; }

  .refresh-btn { background: #2b6cb0; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
  .refresh-btn:hover { background: #2c5282; }

  .exec-time { font-size: 12px; color: #718096; }
</style>
</head>
<body>

<div class="header">
  <div>
    <h1>🔬 Production Diagnostics</h1>
    <div class="meta">Compliance Engine &nbsp;·&nbsp; {{ $report['timestamp'] }} &nbsp;·&nbsp; <span class="exec-time">{{ $report['execution_time_ms'] }}ms</span></div>
  </div>
  <a href="{{ request()->fullUrl() }}" class="refresh-btn">↻ Re-run</a>
</div>

@php
  $score   = $report['health_score'];
  $summary = $report['summary'];
  $scoreClass = $score >= 80 ? 'green' : ($score >= 50 ? 'yellow' : 'red');
@endphp

<div class="score-bar">
  <div class="score-circle {{ $scoreClass }}">{{ $score }}%</div>
  <div>
    <div style="font-size:18px; font-weight:700; color:#fff; margin-bottom:10px;">Health Score</div>
    <div class="score-stats">
      <span class="stat-pill pass">✅ {{ $summary['passed'] }} passed</span>
      <span class="stat-pill warning">⚠️ {{ $summary['warnings'] }} warnings</span>
      <span class="stat-pill error">❌ {{ $summary['errors'] }} errors</span>
      <span class="stat-pill info">📋 {{ $summary['total'] }} total checks</span>
    </div>
  </div>
</div>

<div class="container">

  @foreach($report['checks'] as $checkName => $check)
    @php $status = $check['status']; @endphp
    <div class="check-card">
      <div class="check-header" onclick="toggle(this)">
        <div class="status-dot {{ $status }}"></div>
        <div class="check-name">{{ $checkName }}</div>
        <span class="check-badge {{ $status }}">
          @if($status === 'pass') PASS @elseif($status === 'warning') WARNING @else ERROR @endif
        </span>
        <span class="chevron">▼</span>
      </div>

      <div class="check-body {{ $status !== 'pass' ? 'open' : '' }}">

        @if(count($check['errors']) > 0)
          <div class="section-label">❌ Errors ({{ count($check['errors']) }})</div>
          <ul class="issue-list">
            @foreach($check['errors'] as $err)
              <li class="error"><span class="icon">✗</span><span>{{ $err }}</span></li>
            @endforeach
          </ul>
        @endif

        @if(count($check['warnings']) > 0)
          <div class="section-label">⚠️ Warnings ({{ count($check['warnings']) }})</div>
          <ul class="issue-list">
            @foreach($check['warnings'] as $w)
              <li class="warning"><span class="icon">!</span><span>{{ $w }}</span></li>
            @endforeach
          </ul>
        @endif

        @if(count($check['info']) > 0)
          <div class="section-label">ℹ️ Info</div>

          @if(isset($check['info']['last_20_log_lines']) && is_array($check['info']['last_20_log_lines']))
            <div class="log-lines">{{ implode("\n", $check['info']['last_20_log_lines']) }}</div>
            @php $displayInfo = array_filter($check['info'], fn($k) => $k !== 'last_20_log_lines', ARRAY_FILTER_USE_KEY); @endphp
          @else
            @php $displayInfo = $check['info']; @endphp
          @endif

          @if(count($displayInfo) > 0)
            <div class="info-grid" style="margin-top:8px;">
              @foreach($displayInfo as $key => $val)
                <div class="info-item">
                  <div class="key">{{ $key }}</div>
                  <div class="value">{{ is_array($val) ? json_encode($val) : $val }}</div>
                </div>
              @endforeach
            </div>
          @endif
        @endif

        @if(count($check['errors']) === 0 && count($check['warnings']) === 0 && count($check['info']) === 0)
          <p style="color:#48bb78; font-size:13px; padding-top:4px;">All checks passed.</p>
        @endif

      </div>
    </div>
  @endforeach

</div>

<script>
function toggle(header) {
  const body    = header.nextElementSibling;
  const chevron = header.querySelector('.chevron');
  const open    = body.classList.toggle('open');
  chevron.classList.toggle('open', open);
}
</script>
</body>
</html>
