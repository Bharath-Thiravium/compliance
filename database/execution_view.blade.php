@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Compliance Execution Workflow</h2>
    <div class="card">
        <div class="card-body">
            <ul class="nav nav-tabs" id="sectionTabs"></ul>
            <div class="tab-content mt-3">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Period From</label>
                        <input type="date" id="period_from" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>Period To</label>
                        <input type="date" id="period_to" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>Branch</label>
                        <select id="branch_id" class="form-control">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>
                <div id="formsContainer"></div>
                <button id="createBatchBtn" class="btn btn-primary mt-3">Create Batch</button>
                <button id="processBatchBtn" class="btn btn-success mt-3" style="display:none">Process</button>
                <button id="generateReportBtn" class="btn btn-info mt-3" style="display:none">Generate Report</button>
                <a id="downloadReportBtn" class="btn btn-secondary mt-3" style="display:none">Download</a>
            </div>
        </div>
    </div>
</div>
<script>
let currentBatchId = null;
let currentSectionId = null;
fetch('/compliance/sections').then(r => r.json()).then(sections => {
    const tabs = document.getElementById('sectionTabs');
    sections.forEach((s, i) => {
        tabs.innerHTML += `<li class="nav-item"><a class="nav-link ${i===0?'active':''}" href="#" data-id="${s.id}">${s.section_name}</a></li>`;
    });
    tabs.querySelectorAll('a').forEach(a => a.addEventListener('click', e => {
        e.preventDefault();
        currentSectionId = a.dataset.id;
        fetch(`/compliance/forms/${currentSectionId}`).then(r => r.json()).then(forms => {
            document.getElementById('formsContainer').innerHTML = forms.map(f => 
                `<div class="form-check"><input type="checkbox" value="${f.id}" id="f${f.id}">
                <label for="f${f.id}">${f.form_code} - ${f.form_name} 
                <span class="badge bg-${f.subscription_valid?'success':'warning'}">${f.subscription_valid?'Subscribed':'Upload Required'}</span></label></div>`
            ).join('');
        });
    }));
    if(sections[0]) tabs.querySelector('a').click();
});
document.getElementById('createBatchBtn').addEventListener('click', () => {
    const formIds = Array.from(document.querySelectorAll('#formsContainer input:checked')).map(c => c.value);
    fetch('/compliance/batch/create', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
        body: JSON.stringify({section_id: currentSectionId, period_from: document.getElementById('period_from').value,
            period_to: document.getElementById('period_to').value, branch_id: document.getElementById('branch_id').value, form_ids: formIds})
    }).then(r => r.json()).then(d => {
        currentBatchId = d.batch_id;
        document.getElementById('processBatchBtn').style.display = 'inline-block';
    });
});
document.getElementById('processBatchBtn').addEventListener('click', () => {
    fetch(`/compliance/batch/${currentBatchId}/process`, {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}})
        .then(r => r.json()).then(() => document.getElementById('generateReportBtn').style.display = 'inline-block');
});
document.getElementById('generateReportBtn').addEventListener('click', () => {
    fetch(`/compliance/batch/${currentBatchId}/report`, {method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}})
        .then(r => r.json()).then(() => {
            const btn = document.getElementById('downloadReportBtn');
            btn.style.display = 'inline-block';
            btn.href = `/compliance/batch/${currentBatchId}/download`;
        });
});
</script>
@endsection
