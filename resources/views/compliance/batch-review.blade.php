@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Batch Review</h1>
        <p class="text-gray-600 mt-2">
            Period: <strong>{{ \Carbon\Carbon::create($batch->period_year, $batch->period_month, 1)->format('F Y') }}</strong>
        </p>
    </div>

    <!-- Batch Info Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-gray-600 text-sm">Batch ID</p>
                <p class="text-lg font-semibold">{{ $batch->id }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Status</p>
                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                    {{ ucfirst($batch->status) }}
                </span>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Forms to Generate</p>
                <p class="text-lg font-semibold">{{ $form_count }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm">Data Status</p>
                @if($all_data_exists)
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                        Complete
                    </span>
                @else
                    <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                        Missing
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Forms to be Generated -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Forms to be Generated</h2>
        
        @if($forms->isEmpty())
            <p class="text-gray-600">No forms detected for this period.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($forms as $form)
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex items-start justify-between">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $form->form_code }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $form->section }}</p>
                            </div>
                            <span class="inline-block px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">
                                {{ ucfirst($form->status) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Data Availability Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Data Availability</h2>
        
        @if($all_data_exists)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <p class="text-green-800 font-semibold">✓ All required data is available</p>
                <p class="text-green-700 text-sm mt-1">You can proceed with batch processing.</p>
            </div>
        @else
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <p class="text-red-800 font-semibold">⚠ Some required data is missing</p>
                <p class="text-red-700 text-sm mt-1">Please fill the missing data before proceeding.</p>
            </div>
        @endif

        <!-- Data Summary Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 px-4 font-semibold text-gray-900">Data Type</th>
                        <th class="text-center py-2 px-4 font-semibold text-gray-900">Count</th>
                        <th class="text-center py-2 px-4 font-semibold text-gray-900">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-gray-100">
                        <td class="py-3 px-4">Employees</td>
                        <td class="text-center py-3 px-4">{{ $data_summary['employees'] }}</td>
                        <td class="text-center py-3 px-4">
                            @if($data_summary['employees'] > 0)
                                <span class="text-green-600 font-semibold">✓</span>
                            @else
                                <span class="text-red-600 font-semibold">✗</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-3 px-4">Attendance Records</td>
                        <td class="text-center py-3 px-4">{{ $data_summary['attendance_records'] }}</td>
                        <td class="text-center py-3 px-4">
                            @if($data_summary['attendance_records'] > 0)
                                <span class="text-green-600 font-semibold">✓</span>
                            @else
                                <span class="text-red-600 font-semibold">✗</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-3 px-4">Payroll Entries</td>
                        <td class="text-center py-3 px-4">{{ $data_summary['payroll_entries'] }}</td>
                        <td class="text-center py-3 px-4">
                            @if($data_summary['payroll_entries'] > 0)
                                <span class="text-green-600 font-semibold">✓</span>
                            @else
                                <span class="text-red-600 font-semibold">✗</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-3 px-4">Contract Labour</td>
                        <td class="text-center py-3 px-4">{{ $data_summary['contract_labour'] }}</td>
                        <td class="text-center py-3 px-4">
                            @if($data_summary['contract_labour'] > 0)
                                <span class="text-green-600 font-semibold">✓</span>
                            @else
                                <span class="text-gray-400 font-semibold">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-3 px-4">Bonus Records</td>
                        <td class="text-center py-3 px-4">{{ $data_summary['bonus_records'] }}</td>
                        <td class="text-center py-3 px-4">
                            @if($data_summary['bonus_records'] > 0)
                                <span class="text-green-600 font-semibold">✓</span>
                            @else
                                <span class="text-gray-400 font-semibold">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="border-b border-gray-100">
                        <td class="py-3 px-4">Incidents</td>
                        <td class="text-center py-3 px-4">{{ $data_summary['incidents'] }}</td>
                        <td class="text-center py-3 px-4">
                            @if($data_summary['incidents'] > 0)
                                <span class="text-green-600 font-semibold">✓</span>
                            @else
                                <span class="text-gray-400 font-semibold">-</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="py-3 px-4">Hazard Register</td>
                        <td class="text-center py-3 px-4">{{ $data_summary['hazard_register'] }}</td>
                        <td class="text-center py-3 px-4">
                            @if($data_summary['hazard_register'] > 0)
                                <span class="text-green-600 font-semibold">✓</span>
                            @else
                                <span class="text-gray-400 font-semibold">-</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Missing Data Notice -->
    @if(!$all_data_exists && !empty($missing_data))
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
            <h3 class="text-lg font-bold text-yellow-900 mb-3">Missing Data</h3>
            <p class="text-yellow-800 mb-4">The following data is missing and needs to be filled before proceeding:</p>
            <ul class="list-disc list-inside space-y-2">
                @foreach($missing_data as $item)
                    <li class="text-yellow-800">{{ ucfirst(str_replace('_', ' ', $item)) }}</li>
                @endforeach
            </ul>
            <p class="text-yellow-800 mt-4 text-sm">
                You can fill this data using:
            </p>
            <ul class="list-disc list-inside space-y-1 mt-2 text-sm text-yellow-800">
                <li>Manual data entry form</li>
                <li>CSV file upload</li>
                <li>PDF document upload</li>
            </ul>
        </div>
    @endif

    <!-- Action Buttons -->
    <div class="flex gap-4 justify-end">
        <a href="{{ route('compliance.dashboard') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50 transition">
            Cancel
        </a>
        
        @if($can_proceed)
            <form action="{{ route('compliance.batch.process', $batch->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition">
                    Proceed to Processing
                </button>
            </form>
        @else
            <button disabled class="px-6 py-2 bg-gray-400 text-white rounded-lg font-semibold cursor-not-allowed">
                Proceed to Processing (Data Missing)
            </button>
        @endif
    </div>
</div>
@endsection
