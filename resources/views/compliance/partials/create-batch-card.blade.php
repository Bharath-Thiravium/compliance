<!-- Create Compliance Batch Card -->
<div class="card">
    <div class="card-header">📋 Create Compliance Batch</div>
    <div class="card-body">
        @if(($subscription ?? 'MINIMAL') === 'MINIMAL')
            <div class="alert alert-warning mb-3 text-sm">
                <strong>⚠️ MINIMAL Plan:</strong> You will provide all data manually — employees, attendance, and payroll.
            </div>
        @endif

        <form id="batchForm">
            @csrf
            <div class="grid-row">
                <div class="grid-col col-1-2">
                    <div class="form-group">
                        <label for="period_month" class="form-label">Month</label>
                        <select class="form-select" id="period_month" name="period_month" required>
                            <option value="">-- Select Month --</option>
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                </div>
                <div class="grid-col col-1-2">
                    <div class="form-group">
                        <label for="period_year" class="form-label">Year</label>
                        <select class="form-select" id="period_year" name="period_year" required>
                            <option value="">-- Select Year --</option>
                            @for ($year = date('Y') - 2; $year <= date('Y') + 3; $year++)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="createBatchBtn">
                <span id="submitSpinner" class="spinner hidden"></span>
                {{ ($subscription ?? 'MINIMAL') === 'FULL' ? 'Create Batch & Auto-Generate' : 'Create Batch & Enter Data' }}
            </button>
        </form>
    </div>
</div>
