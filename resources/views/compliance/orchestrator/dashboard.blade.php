@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Compliance Orchestrator</h1>
        <p class="text-gray-600 mt-2">Execute compliance forms with centralized workflow management</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Form Selector -->
        <div class="bg-white rounded-lg shadow p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Form</label>
            <select id="formCode" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500">
                <option value="">Select a form...</option>
                @foreach($forms as $form)
                    <option value="{{ $form->form_code }}">{{ $form->form_code }} - {{ $form->form_name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Branch Selector -->
        <div class="bg-white rounded-lg shadow p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
            <select id="branchId" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500">
                <option value="">Select a branch...</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->unit_name ?? $branch->branch_name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Month/Year Selector -->
        <div class="bg-white rounded-lg shadow p-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
            <div class="flex gap-2">
                <select id="month" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" @if($m == now()->month) selected @endif>
                            {{ \Carbon\Carbon::create(2024, $m, 1)->format('F') }}
                        </option>
                    @endfor
                </select>
                <select id="year" class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500">
                    @for($y = 2020; $y <= 2030; $y++)
                        <option value="{{ $y }}" @if($y == now()->year) selected @endif>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    <!-- Execution Mode Selector -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Execution Mode</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition">
                <input type="radio" name="mode" value="preview" class="mr-3" checked>
                <div>
                    <p class="font-medium text-gray-900">Preview</p>
                    <p class="text-sm text-gray-600">View form in browser</p>
                </div>
            </label>
            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition">
                <input type="radio" name="mode" value="pdf" class="mr-3">
                <div>
                    <p class="font-medium text-gray-900">PDF</p>
                    <p class="text-sm text-gray-600">Download as PDF</p>
                </div>
            </label>
            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition">
                <input type="radio" name="mode" value="batch" class="mr-3">
                <div>
                    <p class="font-medium text-gray-900">Batch</p>
                    <p class="text-sm text-gray-600">Store in batch</p>
                </div>
            </label>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-4 mb-8">
        <button id="executeBtn" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
            Execute Form
        </button>
        <button id="clearBtn" class="px-6 py-3 bg-gray-300 text-gray-900 rounded-lg hover:bg-gray-400 transition font-medium">
            Clear
        </button>
    </div>

    <!-- Results Section -->
    <div id="resultsSection" class="hidden bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Execution Results</h2>
        <div id="resultsContent"></div>
    </div>

    <!-- Recent Executions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Executions</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Form</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Mode</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Status</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Time (ms)</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Records</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2 font-mono text-gray-900">{{ $log->form_code }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">
                                    {{ ucfirst($log->execution_mode) }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded text-xs font-medium @if($log->status === 'success') bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-gray-600">{{ $log->execution_time }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $log->records_generated }}</td>
                            <td class="px-4 py-2 text-gray-600">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-4 text-center text-gray-500">No recent executions</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('executeBtn').addEventListener('click', async function() {
    const formCode = document.getElementById('formCode').value;
    const branchId = document.getElementById('branchId').value;
    const month = document.getElementById('month').value;
    const year = document.getElementById('year').value;
    const mode = document.querySelector('input[name="mode"]:checked').value;

    if (!formCode || !branchId) {
        alert('Please select form and branch');
        return;
    }

    this.disabled = true;
    this.textContent = 'Executing...';

    try {
        const response = await fetch('{{ route("compliance.orchestrator.run") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                form_code: formCode,
                branch_id: branchId,
                month: month,
                year: year,
                mode: mode
            })
        });

        if (mode === 'pdf') {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${formCode}.pdf`;
            a.click();
        } else {
            const data = await response.json();
            if (data.status === 'success') {
                displayResults(data, mode);
            } else {
                alert('Error: ' + data.error);
            }
        }
    } catch (error) {
        alert('Execution failed: ' + error.message);
    } finally {
        this.disabled = false;
        this.textContent = 'Execute Form';
    }
});

document.getElementById('clearBtn').addEventListener('click', function() {
    document.getElementById('formCode').value = '';
    document.getElementById('branchId').value = '';
    document.getElementById('resultsSection').classList.add('hidden');
});

function displayResults(data, mode) {
    const section = document.getElementById('resultsSection');
    const content = document.getElementById('resultsContent');

    let html = `
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded">
            <p class="text-green-800"><strong>Status:</strong> ${data.status}</p>
            <p class="text-green-800"><strong>Execution Time:</strong> ${data.execution_time}ms</p>
            <p class="text-green-800"><strong>Records Generated:</strong> ${data.records_generated}</p>
        </div>
    `;

    if (mode === 'preview') {
        html += `
            <div class="border rounded p-4 bg-gray-50 max-h-96 overflow-y-auto">
                ${data.html}
            </div>
        `;
    } else if (mode === 'batch') {
        html += `
            <p class="text-gray-700"><strong>File Path:</strong> ${data.file_path}</p>
            <p class="text-gray-700"><strong>File Size:</strong> ${data.file_size} bytes</p>
        `;
    }

    content.innerHTML = html;
    section.classList.remove('hidden');
}
</script>
@endsection
