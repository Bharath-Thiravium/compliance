@extends('super-admin.layout')

@section('title', 'Select Compliance User')
@section('page-title', 'Select Compliance User')

@section('content')
    <div class="card">
        <div class="card-header">🔐 Select Compliance User</div>
        <div class="card-body">
            <p class="text-muted mb-3" style="font-size:15px;">
                Choose a user role to view their compliance dashboard. You will see the system as that user would see it.
            </p>

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger mb-3">{{ session('error') }}</div>
            @endif

            <div class="grid-row">
                @forelse ($users as $user)
                    <div class="grid-col col-1-3">
                        <div class="user-card">
                            <div class="user-card-icon">👤</div>
                            <div class="user-card-content">
                                <h3>{{ $user['label'] }}</h3>
                                <p class="user-card-email">{{ $user['email'] }}</p>
                                <p class="user-card-role">
                                    <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $user['role'])) }}</span>
                                </p>
                                <p class="user-card-tenant">
                                    <strong>Tenant:</strong> {{ $user['tenant'] }}
                                </p>
                            </div>
                            <div class="user-card-actions">
                                @if ($user['id'])
                                    <a href="{{ route('super-admin.open-compliance-user', $user['id']) }}" class="btn btn-primary w-100">
                                        🏭 Open Dashboard
                                    </a>
                                @else
                                    <button class="btn w-100" disabled>
                                        ⚠️ User Not Found
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="grid-col col-full">
                        <div class="text-center text-muted p-4">
                            <p class="mb-3" style="font-size:16px;">No demo users available.</p>
                            <p>Please seed demo data first.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                <a href="{{ route('super-admin.dashboard') }}" class="btn w-100">
                    ← Back to Super Admin Dashboard
                </a>
            </div>
        </div>
    </div>
@endsection
