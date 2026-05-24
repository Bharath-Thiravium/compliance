@extends('super-admin.layouts.app')

@section('title', 'Select Compliance User')

@section('content')
    <div class="ant-card">
        <div class="ant-card-head">🔐 Select Compliance User</div>
        <div class="ant-card-body">
            <p style="margin-bottom: 24px; color: #595959; font-size: 15px;">
                Choose a user role to view their compliance dashboard. You will see the system as that user would see it.
            </p>

            @if ($errors->any())
                <div style="background: #fff2f0; border: 1px solid #ffccc7; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; color: #ff4d4f;">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if (session('error'))
                <div style="background: #fff2f0; border: 1px solid #ffccc7; border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; color: #ff4d4f;">
                    {{ session('error') }}
                </div>
            @endif

            <div class="ant-row">
                @forelse ($users as $user)
                    <div class="ant-col ant-col-4">
                        <div class="compliance-user-card">
                            <div class="card-icon">👤</div>
                            <div class="card-content">
                                <h3>{{ $user['label'] }}</h3>
                                <p class="card-email">{{ $user['email'] }}</p>
                                <p class="card-role">
                                    <span class="ant-tag ant-tag-info">{{ ucfirst(str_replace('_', ' ', $user['role'])) }}</span>
                                </p>
                                <p class="card-tenant">
                                    <strong>Tenant:</strong> {{ $user['tenant'] }}
                                </p>
                            </div>
                            <div class="card-actions">
                                @if ($user['id'])
                                    <a href="{{ route('super-admin.open-compliance-user', $user['id']) }}" class="ant-btn ant-btn-primary w-100">
                                        🏭 Open Dashboard
                                    </a>
                                @else
                                    <button class="ant-btn" style="width: 100%; background: #d9d9d9; color: #595959; border-color: #d9d9d9; cursor: not-allowed;" disabled>
                                        ⚠️ User Not Found
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="ant-col ant-col-12">
                        <div style="text-align: center; padding: 40px; color: #8c8c8c;">
                            <p style="font-size: 16px; margin-bottom: 16px;">No demo users available.</p>
                            <p style="font-size: 14px;">Please seed demo data first.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="ant-row mt-4">
                <div class="ant-col ant-col-12">
                    <a href="{{ route('super-admin.dashboard') }}" class="ant-btn" style="background: #f5f5f5; color: #262626; border-color: #d9d9d9; width: 100%; height: 40px;">
                        ← Back to Super Admin Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .compliance-user-card {
            background: white;
            border: 2px solid #f0f0f0;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 320px;
        }

        .compliance-user-card:hover {
            border-color: #722ed1;
            box-shadow: 0 4px 12px rgba(114, 46, 209, 0.15);
        }

        .card-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .compliance-user-card h3 {
            font-size: 18px;
            font-weight: 600;
            color: #262626;
            margin: 0 0 12px 0;
        }

        .card-email {
            font-size: 13px;
            color: #8c8c8c;
            margin: 0 0 12px 0;
            word-break: break-all;
        }

        .card-role {
            margin: 0 0 12px 0;
        }

        .card-tenant {
            font-size: 13px;
            color: #595959;
            margin: 0 0 16px 0;
        }

        .card-actions {
            margin-top: auto;
        }

        .w-100 {
            width: 100%;
        }

        @media (max-width: 768px) {
            .compliance-user-card {
                min-height: auto;
            }
        }
    </style>
@endsection
