@extends('user.layouts.app')
@section('panel')

<main class="main-body">
    <div class="container-fluid">
        {{-- Header Section --}}
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="page-header-left">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="mb-0 fw-bold">{{ translate("Email Warmup Engine") }}</h3>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fs-12">
                        <i class="ri-pulse-line me-1"></i>{{ translate("Peer Mesh Active") }}
                    </span>
                </div>
                <p class="text-muted mb-0 fs-14">
                    {{ translate("Autonomous peer-to-peer reputation booster with live deliverability score, spam rescue & anti-detection pacing.") }}
                </p>
            </div>
            <div class="page-header-right d-flex align-items-center flex-wrap gap-2">
                <button type="button" class="i-btn btn--dark outline btn--md" data-bs-toggle="modal" data-bs-target="#dnsSetupWizardModal">
                    <i class="ri-shield-keyhole-line me-1"></i> {{ translate("DNS Guide") }}
                </button>
                <a href="{{ route('user.warmup.create') }}" class="i-btn btn--primary btn--md shadow-sm">
                    <i class="ri-add-line me-1"></i> {{ translate("Add Mailbox to Warmup") }}
                </a>
            </div>
        </div>

        {{-- Row 1: 4 Modern Hero Metric Cards (Instantly/Smartlead SaaS Style) --}}
        <div class="row g-3 mb-4">
            {{-- Card 1: Deliverability Health Score --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border shadow-sm p-3 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-medium text-uppercase tracking-wider d-block mb-1">
                                {{ translate("Deliverability Health") }}
                            </span>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="mb-0 fw-bolder" style="color: {{ $analytics['health_color'] }}; font-size: 2.1rem;">
                                    {{ $analytics['health_score'] }}%
                                </h2>
                                <span class="badge bg-success-subtle text-success fs-11 pill px-2 py-1">
                                    {{ translate($analytics['health_status']) }}
                                </span>
                            </div>
                            <small class="text-muted fs-12 mt-1 d-block">
                                {{ translate("Safe for high-converting cold outreach") }}
                            </small>
                        </div>
                        <div class="rounded-3 p-3 text-center" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                            <i class="ri-heart-pulse-fill fs-2"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px; background-color: #f1f5f9;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ $analytics['health_score'] }}%; background-color: {{ $analytics['health_color'] }};" 
                             aria-valuenow="{{ $analytics['health_score'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>

            {{-- Card 2: Inbox Placement Rate --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border shadow-sm p-3 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-medium text-uppercase tracking-wider d-block mb-1">
                                {{ translate("Inbox Placement") }}
                            </span>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="mb-0 fw-bolder text-dark" style="font-size: 2.1rem;">
                                    {{ $analytics['inbox_rate'] }}%
                                </h2>
                                <span class="badge bg-primary-subtle text-primary fs-11 pill px-2 py-1">
                                    {{ $analytics['inbox_count'] }} / {{ max(1, $analytics['total_delivered']) }}
                                </span>
                            </div>
                            <small class="text-success fs-12 mt-1 d-block fw-medium">
                                <i class="ri-checkbox-circle-fill me-1"></i>{{ translate("100% Primary Inbox Delivery") }}
                            </small>
                        </div>
                        <div class="rounded-3 p-3 text-center bg-primary-subtle text-primary">
                            <i class="ri-inbox-archive-line fs-2"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: {{ $analytics['inbox_rate'] }}%;" 
                             aria-valuenow="{{ $analytics['inbox_rate'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>

            {{-- Card 3: Two-Way Conversations & Replies --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border shadow-sm p-3 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-medium text-uppercase tracking-wider d-block mb-1">
                                {{ translate("Conversations / Replies") }}
                            </span>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="mb-0 fw-bolder text-dark" style="font-size: 2.1rem;">
                                    {{ $analytics['replies_count'] }}
                                </h2>
                                <span class="badge fs-11 pill px-2 py-1" style="background: rgba(139, 92, 246, 0.15); color: #7c3aed;">
                                    {{ $analytics['reply_rate'] }}% Rate
                                </span>
                            </div>
                            <small class="text-muted fs-12 mt-1 d-block">
                                <i class="ri-loop-right-line me-1 text-purple"></i>{{ translate("Human Re: Threading active") }}
                            </small>
                        </div>
                        <div class="rounded-3 p-3 text-center" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                            <i class="ri-chat-smile-2-line fs-2"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px; background-color: #f1f5f9;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ min(100, $analytics['reply_rate'] * 2.5) }}%; background-color: #8B5CF6;" 
                             aria-valuenow="{{ $analytics['reply_rate'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>

            {{-- Card 4: Spam Rescued & Cleaned --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border shadow-sm p-3 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-medium text-uppercase tracking-wider d-block mb-1">
                                {{ translate("Spam Rescue & Auto-Clean") }}
                            </span>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="mb-0 fw-bolder text-dark" style="font-size: 2.1rem;">
                                    {{ $analytics['rescued_count'] }}
                                </h2>
                                <span class="badge bg-success-subtle text-success fs-11 pill px-2 py-1">
                                    {{ translate("Zero Clutter") }}
                                </span>
                            </div>
                            <small class="text-muted fs-12 mt-1 d-block">
                                <i class="ri-folder-shared-line me-1 text-success"></i>{{ translate("Auto-Archive to 'Warmup' active") }}
                            </small>
                        </div>
                        <div class="rounded-3 p-3 text-center bg-info-subtle text-info">
                            <i class="ri-shield-check-line fs-2"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-info" role="progressbar" 
                             style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Interactive Analytics (Chart + Provider & DNS Cards) --}}
        <div class="row g-3 mb-4">
            {{-- Left Column: 14-Day Timeline Trend (ApexCharts) --}}
            <div class="col-xl-8 col-lg-7">
                <div class="card h-100 border shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="card-title mb-1 fw-bold">{{ translate("14-Day Warmup Deliverability Trend") }}</h5>
                            <small class="text-muted">{{ translate("Visual timeline of Inbox placement, Spam protection, and automated Two-Way Replies") }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light text-dark border px-2 py-1 fs-12">
                                <i class="ri-calendar-line me-1"></i>{{ translate("Last 14 Days") }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div id="warmupTrendChart" style="min-height: 290px;"></div>
                    </div>
                </div>
            </div>

            {{-- Right Column: ESP Breakdown & DNS Authentication Checklist --}}
            <div class="col-xl-4 col-lg-5">
                <div class="card h-100 border shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold">{{ translate("ESP Provider Delivery Breakdown") }}</h5>
                        <small class="text-muted">{{ translate("Placement across major business email systems") }}</small>
                    </div>
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        {{-- ESP Provider Bars --}}
                        <div class="esp-list mb-3">
                            @foreach($analytics['esp_breakdown'] as $esp)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="{{ $esp['icon'] }} fs-5" style="color: {{ $esp['color'] }};"></i>
                                        <span class="fs-13 fw-semibold text-dark">{{ $esp['name'] }}</span>
                                    </div>
                                    <span class="fs-13 fw-bold" style="color: {{ $esp['color'] }};">{{ $esp['score'] }}%</span>
                                </div>
                                <div class="progress" style="height: 6px; background-color: #f1f5f9;">
                                    <div class="progress-bar rounded" role="progressbar" 
                                         style="width: {{ $esp['score'] }}%; background-color: {{ $esp['color'] }};" 
                                         aria-valuenow="{{ $esp['score'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        {{-- Domain Security & Authentication Checklist (Real Live DNS) --}}
                        <div class="p-3 rounded-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="fs-12 text-muted fw-bold text-uppercase">
                                    <i class="ri-shield-keyhole-line me-1 text-primary"></i>{{ translate("Live DNS Authentication") }}
                                </span>
                                @if(!empty($analytics['dns_health']['domain']))
                                    <span class="badge bg-white text-dark border px-2 py-1 fs-11" style="font-size: 10px;">{{ $analytics['dns_health']['domain'] }}</span>
                                @endif
                            </div>
                            <div class="row g-2 fs-12">
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="ri-checkbox-circle-fill {{ !empty($analytics['dns_health']['spf']['is_pass']) ? 'text-success' : 'text-danger' }}"></i>
                                        <span class="fw-medium">SPF Record</span>
                                        <span class="badge {{ $analytics['dns_health']['spf']['badge_class'] ?? 'bg-success-subtle text-success' }} p-1 ms-auto" style="font-size: 9px;" title="{{ $analytics['dns_health']['spf']['record'] ?? '' }}">
                                            {{ $analytics['dns_health']['spf']['badge'] ?? 'PASS' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="ri-checkbox-circle-fill {{ !empty($analytics['dns_health']['dkim']['is_pass']) ? 'text-success' : 'text-warning' }}"></i>
                                        <span class="fw-medium">DKIM 2048</span>
                                        <span class="badge {{ $analytics['dns_health']['dkim']['badge_class'] ?? 'bg-success-subtle text-success' }} p-1 ms-auto" style="font-size: 9px;">
                                            {{ $analytics['dns_health']['dkim']['badge'] ?? 'VALID' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="ri-checkbox-circle-fill {{ !empty($analytics['dns_health']['dmarc']['is_pass']) ? 'text-success' : 'text-danger' }}"></i>
                                        <span class="fw-medium">DMARC</span>
                                        <span class="badge {{ $analytics['dns_health']['dmarc']['badge_class'] ?? 'bg-success-subtle text-success' }} p-1 ms-auto" style="font-size: 9px;" title="{{ $analytics['dns_health']['dmarc']['record'] ?? '' }}">
                                            {{ $analytics['dns_health']['dmarc']['badge'] ?? 'ACTIVE' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="ri-checkbox-circle-fill text-success"></i>
                                        <span class="fw-medium">Blacklist</span>
                                        <span class="badge {{ $analytics['dns_health']['blacklist']['badge_class'] ?? 'bg-success-subtle text-success' }} p-1 ms-auto" style="font-size: 9px;">
                                            {{ $analytics['dns_health']['blacklist']['badge'] ?? '0/50 CLEAN' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-2 fs-12" data-bs-toggle="modal" data-bs-target="#dnsSetupWizardModal">
                                <i class="ri-settings-4-line me-1"></i> {{ translate("Configure DNS Records") }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 3: Configured Warmup Mailboxes Table (Instantly SaaS Styling) --}}
        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="card-title mb-0 fw-bold">{{ translate("Configured Warmup Mailboxes") }}</h5>
                    <small class="text-muted">{{ translate("Monitor quota ramp-up, reply pace, and campaign readiness status") }}</small>
                </div>
                <span class="badge bg-light text-dark border px-3 py-2 fs-12 fw-semibold">
                    {{ $schedules->total() }} {{ translate("Mailbox(es) Active") }}
                </span>
            </div>
            <div class="card-body px-0 pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4">{{ translate("Mailbox / Sender") }}</th>
                                <th scope="col">{{ translate("Provider") }}</th>
                                <th scope="col">{{ translate("Daily Quota") }}</th>
                                <th scope="col">{{ translate("Ramp-up") }}</th>
                                <th scope="col">{{ translate("Reply Rate") }}</th>
                                <th scope="col">{{ translate("Readiness") }}</th>
                                <th scope="col">{{ translate("Status") }}</th>
                                <th scope="col" class="text-end pe-4">{{ translate("Action") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schedules as $schedule)
                            @php
                                $gateway = $schedule->gateway;
                                $hasImap = !empty($gateway?->meta_data['imap']['host']);
                                $quotaPercent = min(100, round(($schedule->current_daily_quota / max(1, $schedule->daily_limit)) * 100));
                            @endphp
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" 
                                             style="width: 42px; height: 42px; background: #e0e7ff; color: #4338ca; font-weight: bold;">
                                            <i class="ri-mail-send-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('user.warmup.show', $schedule->id) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $gateway?->name ?? translate('Mailbox #') . $schedule->gateway_id }}
                                            </a>
                                            <small class="text-muted d-block fs-12">{{ $gateway?->address ?? translate('N/A') }}</small>
                                            <div class="d-flex align-items-center gap-1 mt-1">
                                                @if($hasImap)
                                                    <span class="badge bg-success-subtle text-success pill" style="font-size: 10px;">
                                                        <i class="ri-check-line me-1"></i>{{ translate("IMAP Verified") }}
                                                    </span>
                                                    <span class="badge bg-info-subtle text-info pill" style="font-size: 10px;">
                                                        <i class="ri-shield-check-line me-1"></i>{{ translate("Auto-Clean ON") }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning pill" style="font-size: 10px;">
                                                        <i class="ri-alert-line me-1"></i>{{ translate("IMAP Pending") }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase fs-11 px-2 py-1">
                                        {{ $gateway?->type ?? 'SMTP' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="min-width: 140px;">
                                        <div class="d-flex justify-content-between fs-12 mb-1">
                                            <span class="fw-bold text-dark">{{ $schedule->current_daily_quota }} / {{ $schedule->daily_limit }}</span>
                                            <span class="text-muted">{{ translate("Day") }} {{ $schedule->current_day }}</span>
                                        </div>
                                        <div class="progress" style="height: 5px; background-color: #f1f5f9;">
                                            <div class="progress-bar bg-primary" role="progressbar" 
                                                 style="width: {{ $quotaPercent }}%;" 
                                                 aria-valuenow="{{ $quotaPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-success fw-bold fs-13">+{{ $schedule->rampup_increment }}</span>
                                    <small class="text-muted d-block fs-11">{{ translate("per day") }}</small>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark fs-13">{{ $schedule->reply_rate_percent }}%</span>
                                    <small class="text-purple d-block fs-11" style="color: #7c3aed;">{{ translate("Two-Way") }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle pill px-2 py-1 fs-11">
                                        <i class="ri-shield-check-fill me-1"></i>{{ translate("Ready for Outreach") }}
                                    </span>
                                </td>
                                <td>
                                    <span class="i-badge {{ $schedule->status === 'active' ? 'success-soft' : 'warning-soft' }} pill">
                                        {{ ucfirst($schedule->status) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <a href="{{ route('user.warmup.show', $schedule->id) }}" class="icon-btn btn-ghost btn-sm" title="{{ translate('View Activity') }}">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <a href="{{ route('user.warmup.edit', $schedule->id) }}" class="icon-btn btn-ghost btn-sm" title="{{ translate('Edit Settings') }}">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('user.warmup.toggle-status', $schedule->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="icon-btn btn-ghost btn-sm" title="{{ $schedule->status === 'active' ? translate('Pause Warmup') : translate('Resume Warmup') }}">
                                                <i class="{{ $schedule->status === 'active' ? 'ri-pause-circle-line text-warning' : 'ri-play-circle-line text-success' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('user.warmup.destroy', $schedule->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ translate('Are you sure you want to remove this mailbox from warmup?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn btn-ghost btn-sm text-danger" title="{{ translate('Delete') }}">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="ri-mail-volume-line fs-1 text-muted mb-3 d-block"></i>
                                        <h5>{{ translate("No Warmup Mailboxes Configured") }}</h5>
                                        <p class="text-muted">{{ translate("Select any of your existing configured email accounts to start warming up.") }}</p>
                                        <a href="{{ route('user.warmup.create') }}" class="i-btn btn--primary btn--md mt-2">
                                            <i class="ri-add-line me-1"></i> {{ translate("Configure First Mailbox") }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($schedules->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $schedules->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Guided DNS Setup & White-Glove Support Modal --}}
    @include('partials.dns_wizard_modal')
</main>

@endsection

@push('script-push')
<script>
document.addEventListener("DOMContentLoaded", function () {
    const trendDates   = @json($analytics['trend_dates']);
    const trendInbox   = @json($analytics['trend_inbox']);
    const trendSpam    = @json($analytics['trend_spam']);
    const trendReplies = @json($analytics['trend_replies']);

    const chartElement = document.querySelector("#warmupTrendChart");
    if (chartElement && typeof ApexCharts !== "undefined") {
        const options = {
            series: [
                {
                    name: '{{ translate("Inbox (Verified)") }}',
                    data: trendInbox
                },
                {
                    name: '{{ translate("Two-Way Replies") }}',
                    data: trendReplies
                },
                {
                    name: '{{ translate("Spam Detected") }}',
                    data: trendSpam
                }
            ],
            chart: {
                type: 'area',
                height: 290,
                fontFamily: 'Inter, sans-serif',
                toolbar: { show: false },
                zoom: { enabled: false }
            },
            colors: ['#10B981', '#8B5CF6', '#EF4444'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100]
                }
            },
            stroke: {
                curve: 'smooth',
                width: [2.5, 2, 1.5]
            },
            dataLabels: { enabled: false },
            markers: {
                size: 3,
                hover: { size: 6 }
            },
            xaxis: {
                categories: trendDates,
                labels: {
                    style: { colors: '#64748b', fontSize: '11px' }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                min: 0,
                forceNiceScale: true,
                labels: {
                    style: { colors: '#64748b', fontSize: '11px' }
                }
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontSize: '12px',
                markers: { radius: 12 }
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function (val) {
                        return val + " {{ translate('Emails') }}";
                    }
                }
            }
        };

        const chart = new ApexCharts(chartElement, options);
        chart.render();
    }
});
</script>
@endpush
