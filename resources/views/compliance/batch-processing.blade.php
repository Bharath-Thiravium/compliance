@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Generating Compliance Forms...</h1>
        <p class="text-gray-600 mt-2">
            Period: <strong>{{ \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1)->format('F Y') }}</strong>
        </p>
    </div>

    <!-- Progress Summary -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="grid grid-cols-4 gap-4">
            <div>
                <p class="text-gray-600 text-sm">Total Forms</p>
                <p class="text-2xl font-bold text-gray-900" id="total-forms">{{ $forms->count() }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Generated</p>
                <p class="text-2xl font-bold text-green-600" id="generated-count">0</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Processing</p>
                <p class="text-2xl font-bold text-blue-600" id="processing-count">0</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Pending</p>
                <p class="text-2xl font-bold text-gray-600" id="pending-count">{{ $forms->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Forms List -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Form Generation Status</h2>
        
        <div class="space-y-3" id="forms-container">
            @foreach($forms as $form)
                <div class="form-row border border-gray-200 rounded-lg p-4 flex items-center justify-between" data-form-code="{{ $form->form_code }}">
                    <div class="flex items-center gap-4 flex-1">
                        <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center status-icon">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $form->form_code }}</p>
                            <p class="text-sm text-gray-600">{{ $form->section ?? '-' }}</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <span class="status-badge inline-block px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                            Pending
                        </span>
                        <button class="preview-btn hidden px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition" 
                                data-form-code="{{ $form->form_code }}" 
                                data-batch-id="{{ $batch->id }}">
                            Preview
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Completion Message -->
    <div id="completion-message" class="hidden mt-8 bg-green-50 border border-green-200 rounded-lg p-6">
        <p class="text-green-800 font-semibold text-lg">✓ All forms have been generated successfully!</p>
        <p class="text-green-700 mt-2">You can now preview, download, or audit the generated forms.</p>
        <div class="flex gap-4 mt-4">
            <a href="{{ route('compliance.batch.review', $batch->id) }}" class="px-6 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">
                Back to Batch
            </a>
            <a href="{{ route('compliance.dashboard') }}" class="px-6 py-2 border border-green-600 text-green-600 rounded-lg font-semibold hover:bg-green-50 transition">
                Dashboard
            </a>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="preview-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 p-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900" id="preview-title">Form Preview</h3>
            <button onclick="closePreview()" class="text-gray-600 hover:text-gray-900">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="preview-content" class="p-6">
            <!-- Preview content will be loaded here -->
        </div>
    </div>
</div>

<script>
const batchId = {{ $batch->id }};
const processUrl = `/compliance/batch/${batchId}/process-next`;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
let isProcessing = false;

function processNext() {
    if (isProcessing) return;
    isProcessing = true;

    fetch(processUrl, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        isProcessing = false;

        if (data.status === 'processing') {
            updateUI(data.forms);
            updateCounts(data.generated, 0, data.total - data.generated);
            processNext(); // continue chain
        } else if (data.status === 'complete') {
            updateUI(data.forms);
            updateCounts(data.generated, 0, 0);
            showCompletionMessage();
        } else if (data.status === 'error') {
            console.error('Processing error:', data.message);
            setTimeout(processNext, 2000); // retry on error
        }
    })
    .catch(err => {
        isProcessing = false;
        console.error('Fetch error:', err);
        setTimeout(processNext, 2000);
    });
}

function updateUI(forms) {
    forms.forEach(form => {
        const row = document.querySelector(`[data-form-code="${form.form_code}"]`);
        if (!row) return;

        const statusBadge = row.querySelector('.status-badge');
        const previewBtn  = row.querySelector('.preview-btn');

        if (form.status === 'generated') {
            statusBadge.textContent = '✔ Generated';
            statusBadge.className = 'status-badge inline-block px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800';
            previewBtn.classList.remove('hidden');
        } else if (form.status === 'processing') {
            statusBadge.textContent = '⏳ Processing...';
            statusBadge.className = 'status-badge inline-block px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800';
            previewBtn.classList.add('hidden');
        } else if (form.status === 'failed') {
            statusBadge.textContent = '✗ Failed';
            statusBadge.className = 'status-badge inline-block px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800';
            previewBtn.classList.add('hidden');
        } else {
            statusBadge.textContent = 'Pending';
            statusBadge.className = 'status-badge inline-block px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800';
            previewBtn.classList.add('hidden');
        }
    });
}

function updateCounts(generated, processing, pending) {
    document.getElementById('generated-count').textContent = generated;
    document.getElementById('processing-count').textContent = processing;
    document.getElementById('pending-count').textContent = pending;
}

function showCompletionMessage() {
    document.getElementById('completion-message').classList.remove('hidden');
}

function openPreview(formCode) {
    const modal = document.getElementById('preview-modal');
    const title = document.getElementById('preview-title');
    const content = document.getElementById('preview-content');
    
    title.textContent = `Preview - ${formCode}`;
    content.innerHTML = '<p class="text-gray-600">Loading preview...</p>';
    modal.classList.remove('hidden');

    fetch(`/compliance/batch/${batchId}/preview/${formCode}`)
        .then(response => response.text())
        .then(html => {
            content.innerHTML = html;
        })
        .catch(error => {
            content.innerHTML = `<p class="text-red-600">Error loading preview: ${error.message}</p>`;
        });
}

function closePreview() {
    document.getElementById('preview-modal').classList.add('hidden');
}

// Attach preview button listeners
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.preview-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            openPreview(this.dataset.formCode);
        });
    });

    // Start processing chain
    processNext();
});

// Close modal when clicking outside
document.getElementById('preview-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePreview();
    }
});
</script>

@endsection
