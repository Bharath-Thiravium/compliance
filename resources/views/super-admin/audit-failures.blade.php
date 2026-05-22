@extends('super-admin.layouts.app')

@section('title', 'Audit Failures - Super Admin')

@section('content')
    <div class="page-header">
        <a href="{{ route('super-admin.dashboard') }}" class="ant-btn">← Back to Dashboard</a>
    </div>

    <div class="ant-card">
        <div class="ant-card-head danger">⚠️ Users with Low Audit Scores</div>
        <div class="ant-card-body">
            <div class="ant-row">
                <div class="ant-col ant-col-6">
                    <div class="stat-card">
                        <h3 style="color: #ff4d4f;">{{ $stats['users_with_low_score'] ?? 0 }}</h3>
                        <p>Users with Score < 100</p>
                    </div>
                </div>
                <div class="ant-col ant-col-6">
                    <div class="stat-card">
                        <h3 style="color: #ff4d4f;">{{ $stats['total_low_score_records'] ?? 0 }}</h3>
                        <p>Total Low Score Records</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ant-card section-spacing">
        <div class="ant-card-head danger">📋 Audit Failure Summary</div>
        <div class="ant-card-body">
            @if($userFailures->count() > 0)
                <div class="mobile-table-wrap">
                    <table class="ant-table">
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>User</th>
                                <th>Section</th>
                                <th>Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userFailures as $index => $failure)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $failure['user_name'] }}</strong>
                                        <br><small style="color: #8c8c8c;">{{ $failure['user_email'] }}</small>
                                    </td>
                                    <td>{{ $failure['section_name'] }}</td>
                                    <td>{{ $failure['created_at'] ? \Carbon\Carbon::parse($failure['created_at'])->diffForHumans() : 'N/A' }}</td>
                                    <td>
                                        <button class="ant-btn ant-btn-sm" onclick="showDetailedReport({{ json_encode($failure) }})">Detailed Report</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <div class="sa-pagination">
                        {{ $userFailures->links() }}
                    </div>
                </div>
            @else
                <p class="text-muted text-center">No users with audit scores below 100 found.</p>
            @endif
        </div>
    </div>

    <!-- Detailed Report Modal -->
    <div id="detailedReportModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Detailed Report</h2>
                <button onclick="closeDetailedReport()" class="modal-close-btn">&times;</button>
            </div>

            <div class="modal-section">
                <div class="modal-item">
                    <strong>User Name:</strong>
                    <span id="reportUserName"></span>
                </div>
                <div class="modal-item">
                    <strong>Email:</strong>
                    <span id="reportUserEmail"></span>
                </div>
                <div class="modal-item">
                    <strong>Audit Score:</strong>
                    <span id="reportAuditScore" style="color: #ff4d4f; font-weight: 600;"></span>
                </div>
                <div class="modal-item">
                    <strong>Section:</strong>
                    <span id="reportSection"></span>
                </div>
                <div class="modal-item">
                    <strong>Reported At:</strong>
                    <span id="reportTime"></span>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <span class="modal-section-title">Issue Sections / Forms:</span>
                <div id="reportIssuesList" style="background: #fafafa; padding: 12px; border-radius: 4px; border-left: 3px solid #ff4d4f;"></div>
            </div>

            <div style="margin-top: 20px; text-align: right;">
                <button onclick="closeDetailedReport()" class="ant-btn" style="background: #722ed1; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer;">Close</button>
            </div>
        </div>
    </div>

    <script>
        function showDetailedReport(failure) {
            document.getElementById('reportUserName').textContent = failure.user_name || 'N/A';
            document.getElementById('reportUserEmail').textContent = failure.user_email || 'N/A';
            document.getElementById('reportAuditScore').textContent = failure.audit_score || 'N/A';
            document.getElementById('reportSection').textContent = failure.section_name || 'N/A';
            
            const createdAt = new Date(failure.created_at);
            document.getElementById('reportTime').textContent = createdAt.toLocaleString() || 'N/A';

            let issuesHtml = '';
            if (failure.issues && failure.issues.length > 0) {
                failure.issues.forEach((issue, idx) => {
                    issuesHtml += `<div style="margin-bottom: 12px; padding: 8px; background: white; border-radius: 4px;">
                        <strong>${issue.form_code}</strong> (Score: ${issue.audit_score})`;
                    
                    if (issue.violations && issue.violations.length > 0) {
                        issuesHtml += '<ul style="margin: 8px 0 0 0; padding-left: 20px;">';
                        issue.violations.forEach(violation => {
                            issuesHtml += `<li style="color: #595959; font-size: 12px;">${violation}</li>`;
                        });
                        issuesHtml += '</ul>';
                    }
                    
                    issuesHtml += `<small style="color: #8c8c8c; display: block; margin-top: 4px;">Batch ID: ${issue.batch_id}</small>
                    </div>`;
                });
            } else {
                issuesHtml = '<p style="color: #8c8c8c; margin: 0;">No issues found.</p>';
            }
            
            document.getElementById('reportIssuesList').innerHTML = issuesHtml;
            document.getElementById('detailedReportModal').classList.add('show');
        }

        function closeDetailedReport() {
            document.getElementById('detailedReportModal').classList.remove('show');
        }

        document.getElementById('detailedReportModal').addEventListener('click', function(e) {
            if (e.target === this) closeDetailedReport();
        });
    </script>
@endsection
