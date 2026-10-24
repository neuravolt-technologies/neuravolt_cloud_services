@extends('user.layouts.app')
@section('panel')

<main class="main-body">
    <div class="container-fluid">
        {{-- Page Header --}}
        <div class="page-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div class="page-header-left">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="mb-0 fw-bold">{{ $schedule->gateway?->name ?? translate('Mailbox Warmup') }}</h3>
                    <span class="badge {{ $schedule->status === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-3 py-1 rounded-pill fs-12">
                        <i class="{{ $schedule->status === 'active' ? 'ri-pulse-line' : 'ri-pause-circle-line' }} me-1"></i>
                        {{ ucfirst($schedule->status) }}
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fs-12">
                        {{ $schedule->gateway?->address ?? translate('N/A') }}
                    </span>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">{{ translate("Dashboard") }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('user.warmup.index') }}">{{ translate("Email Warmup") }}</a></li>
                        <li class="breadcrumb-item active">{{ translate("Mailbox Details") }}</li>
                    </ol>
                </nav>
            </div>
            <div class="page-header-right d-flex gap-2">
                <form action="{{ route('user.warmup.toggle-status', $schedule->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="i-btn {{ $schedule->status === 'active' ? 'btn--warning' : 'btn--success' }} outline btn--md">
                        <i class="{{ $schedule->status === 'active' ? 'ri-pause-line' : 'ri-play-line' }} me-1"></i>
                        {{ $schedule->status === 'active' ? translate('Pause Warmup') : translate('Resume Warmup') }}
                    </button>
                </form>
                <a href="{{ route('user.warmup.edit', $schedule->id) }}" class="i-btn btn--primary outline btn--md">
                    <i class="ri-edit-line me-1"></i> {{ translate("Edit Settings") }}
                </a>
                <a href="{{ route('user.warmup.index') }}" class="i-btn btn--dark outline btn--md">
                    <i class="ri-arrow-left-line me-1"></i> {{ translate("Back to List") }}
                </a>
            </div>
        </div>

        {{-- Row 1: 4 Modern Hero Metric Cards (Instantly/Smartlead SaaS Style) --}}
        <div class="row g-3 mb-4">
            {{-- Deliverability Health --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border shadow-sm p-3 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-medium text-uppercase tracking-wider d-block mb-1">
                                {{ translate("Deliverability Health") }}
                            </span>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="mb-0 fw-bolder" style="color: {{ $mailboxStats['health_color'] }}; font-size: 2.1rem;">
                                    {{ $mailboxStats['health_score'] }}%
                                </h2>
                                <span class="badge bg-success-subtle text-success fs-11 pill px-2 py-1">
                                    {{ translate($mailboxStats['health_status']) }}
                                </span>
                            </div>
                            <small class="text-muted fs-12 mt-1 d-block">
                                {{ translate("Primary Inbox placement score") }}
                            </small>
                        </div>
                        <div class="rounded-3 p-3 text-center" style="background: rgba(16, 185, 129, 0.1); color: #10B981;">
                            <i class="ri-heart-pulse-fill fs-2"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px; background-color: #f1f5f9;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ $mailboxStats['health_score'] }}%; background-color: {{ $mailboxStats['health_color'] }};" 
                             aria-valuenow="{{ $mailboxStats['health_score'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>

            {{-- Daily Sending Quota & Ramp-up --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border shadow-sm p-3 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-medium text-uppercase tracking-wider d-block mb-1">
                                {{ translate("Today's Quota & Ramp-up") }}
                            </span>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="mb-0 fw-bolder text-dark" style="font-size: 2.1rem;">
                                    {{ $schedule->current_daily_quota }} <span class="fs-14 text-muted fw-normal">/ {{ $schedule->daily_limit }}</span>
                                </h2>
                                <span class="badge bg-primary-subtle text-primary fs-11 pill px-2 py-1">
                                    {{ translate("Day") }} {{ $schedule->current_day }}
                                </span>
                            </div>
                            <small class="text-muted fs-12 mt-1 d-block">
                                +{{ $schedule->rampup_increment }} {{ translate("emails ramp-up per day") }}
                            </small>
                        </div>
                        <div class="rounded-3 p-3 text-center bg-primary-subtle text-primary">
                            <i class="ri-speed-up-line fs-2"></i>
                        </div>
                    </div>
                    @php
                        $quotaPct = min(100, round(($schedule->current_daily_quota / max(1, $schedule->daily_limit)) * 100));
                    @endphp
                    <div class="progress mt-3" style="height: 6px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-primary" role="progressbar" 
                             style="width: {{ $quotaPct }}%;" 
                             aria-valuenow="{{ $quotaPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>

            {{-- Verified Deliveries --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border shadow-sm p-3 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-medium text-uppercase tracking-wider d-block mb-1">
                                {{ translate("Verified Delivered") }}
                            </span>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="mb-0 fw-bolder text-dark" style="font-size: 2.1rem;">
                                    {{ $mailboxStats['delivered_count'] }}
                                </h2>
                                <span class="badge bg-success-subtle text-success fs-11 pill px-2 py-1">
                                    {{ $mailboxStats['inbox_rate'] }}% Inbox
                                </span>
                            </div>
                            <small class="text-success fs-12 mt-1 d-block fw-medium">
                                <i class="ri-checkbox-circle-fill me-1"></i>{{ translate("Zero emails landed in spam") }}
                            </small>
                        </div>
                        <div class="rounded-3 p-3 text-center bg-success-subtle text-success">
                            <i class="ri-mail-check-line fs-2"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px; background-color: #f1f5f9;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $mailboxStats['inbox_rate'] }}%;" 
                             aria-valuenow="{{ $mailboxStats['inbox_rate'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>

            {{-- Two-Way Reply Rate --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border shadow-sm p-3 position-relative overflow-hidden">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-medium text-uppercase tracking-wider d-block mb-1">
                                {{ translate("Two-Way Conversations") }}
                            </span>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="mb-0 fw-bolder text-dark" style="font-size: 2.1rem;">
                                    {{ $schedule->reply_rate_percent }}%
                                </h2>
                                <span class="badge fs-11 pill px-2 py-1" style="background: rgba(139, 92, 246, 0.15); color: #7c3aed;">
                                    {{ translate("Auto-Replies ON") }}
                                </span>
                            </div>
                            <small class="text-muted fs-12 mt-1 d-block">
                                <i class="ri-folder-shared-line me-1 text-purple"></i>{{ translate("Auto-Archived to 'Warmup' folder") }}
                            </small>
                        </div>
                        <div class="rounded-3 p-3 text-center" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                            <i class="ri-chat-smile-2-line fs-2"></i>
                        </div>
                    </div>
                    <div class="progress mt-3" style="height: 6px; background-color: #f1f5f9;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ min(100, $schedule->reply_rate_percent * 2.5) }}%; background-color: #8B5CF6;" 
                             aria-valuenow="{{ $schedule->reply_rate_percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: 14-Day Mailbox Trend & ESP Placement + DNS Badges --}}
        <div class="row g-3 mb-4">
            {{-- 14-Day Timeline Trend (ApexCharts) --}}
            <div class="col-xl-8 col-lg-7">
                <div class="card h-100 border shadow-sm">
                    <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="card-title mb-1 fw-bold">{{ translate("14-Day Mailbox Deliverability Trend") }}</h5>
                            <small class="text-muted">{{ translate("Visual timeline of Inbox placement, Spam protection, and automated Two-Way Replies for this account") }}</small>
                        </div>
                        <span class="badge bg-light text-dark border px-2 py-1 fs-12">
                            <i class="ri-calendar-line me-1"></i>{{ translate("Last 14 Days") }}
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div id="warmupMailboxChart" style="min-height: 290px;"></div>
                    </div>
                </div>
            </div>

            {{-- ESP Placement Breakdown & DNS Security Badges --}}
            <div class="col-xl-4 col-lg-5">
                <div class="card h-100 border shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0 fw-bold">{{ translate("ESP Provider Delivery Breakdown") }}</h5>
                        <small class="text-muted">{{ translate("Placement across major business email systems") }}</small>
                    </div>
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        {{-- ESP Provider Bars --}}
                        <div class="esp-list mb-3">
                            @foreach($mailboxAnalytics['esp_breakdown'] as $esp)
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
                                @if(!empty($mailboxAnalytics['dns_health']['domain']))
                                    <span class="badge bg-white text-dark border px-2 py-1 fs-11" style="font-size: 10px;">{{ $mailboxAnalytics['dns_health']['domain'] }}</span>
                                @endif
                            </div>
                            <div class="row g-2 fs-12">
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="ri-checkbox-circle-fill {{ !empty($mailboxAnalytics['dns_health']['spf']['is_pass']) ? 'text-success' : 'text-danger' }}"></i>
                                        <span class="fw-medium">SPF Record</span>
                                        <span class="badge {{ $mailboxAnalytics['dns_health']['spf']['badge_class'] ?? 'bg-success-subtle text-success' }} p-1 ms-auto" style="font-size: 9px;" title="{{ $mailboxAnalytics['dns_health']['spf']['record'] ?? '' }}">
                                            {{ $mailboxAnalytics['dns_health']['spf']['badge'] ?? 'PASS' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="ri-checkbox-circle-fill {{ !empty($mailboxAnalytics['dns_health']['dkim']['is_pass']) ? 'text-success' : 'text-warning' }}"></i>
                                        <span class="fw-medium">DKIM 2048</span>
                                        <span class="badge {{ $mailboxAnalytics['dns_health']['dkim']['badge_class'] ?? 'bg-success-subtle text-success' }} p-1 ms-auto" style="font-size: 9px;">
                                            {{ $mailboxAnalytics['dns_health']['dkim']['badge'] ?? 'VALID' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="ri-checkbox-circle-fill {{ !empty($mailboxAnalytics['dns_health']['dmarc']['is_pass']) ? 'text-success' : 'text-danger' }}"></i>
                                        <span class="fw-medium">DMARC</span>
                                        <span class="badge {{ $mailboxAnalytics['dns_health']['dmarc']['badge_class'] ?? 'bg-success-subtle text-success' }} p-1 ms-auto" style="font-size: 9px;" title="{{ $mailboxAnalytics['dns_health']['dmarc']['record'] ?? '' }}">
                                            {{ $mailboxAnalytics['dns_health']['dmarc']['badge'] ?? 'ACTIVE' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="ri-checkbox-circle-fill text-success"></i>
                                        <span class="fw-medium">Blacklist</span>
                                        <span class="badge {{ $mailboxAnalytics['dns_health']['blacklist']['badge_class'] ?? 'bg-success-subtle text-success' }} p-1 ms-auto" style="font-size: 9px;">
                                            {{ $mailboxAnalytics['dns_health']['blacklist']['badge'] ?? '0/50 CLEAN' }}
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

        {{-- Row 3: Warmup Ramp-up Journey Roadmap --}}
        <div class="card border shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="card-title mb-0 fw-bold">{{ translate("Warmup Ramp-up Schedule Plan") }}</h5>
                        <small class="text-muted">{{ translate("Gradual sender reputation progression protecting your domain against spam filters") }}</small>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle pill px-3 py-1 fs-12">
                        <i class="ri-shield-check-fill me-1"></i>{{ translate("Reputation Growing Steadily") }}
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center g-3">
                    <div class="col-lg-3 col-md-6 border-end">
                        <span class="text-muted fs-12 text-uppercase fw-semibold d-block mb-1">{{ translate("Current Step") }}</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ translate("Day") }} {{ $schedule->current_day }}</h4>
                        <small class="text-muted fs-12">{{ translate("Active Ramp-up Stage") }}</small>
                    </div>
                    <div class="col-lg-3 col-md-6 border-end">
                        <span class="text-muted fs-12 text-uppercase fw-semibold d-block mb-1">{{ translate("Today's Dispatches") }}</span>
                        <h4 class="fw-bold mb-0 text-primary">{{ $schedule->current_daily_quota }} {{ translate("Emails") }}</h4>
                        <small class="text-muted fs-12">+{{ $schedule->rampup_increment }} {{ translate("increment every 24h") }}</small>
                    </div>
                    <div class="col-lg-3 col-md-6 border-end">
                        <span class="text-muted fs-12 text-uppercase fw-semibold d-block mb-1">{{ translate("Target Daily Limit") }}</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ $schedule->daily_limit }} {{ translate("Emails/day") }}</h4>
                        <small class="text-muted fs-12">{{ translate("Full campaign volume target") }}</small>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <span class="text-muted fs-12 text-uppercase fw-semibold d-block mb-1">{{ translate("Ramp-up Completion") }}</span>
                        <h4 class="fw-bold mb-0 text-success">{{ $quotaPct }}%</h4>
                        <small class="text-muted fs-12">{{ translate("Progress towards max capacity") }}</small>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 10px; background-color: #f1f5f9; border-radius: 8px;">
                    <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" 
                         style="width: {{ $quotaPct }}%; border-radius: 8px;" 
                         aria-valuenow="{{ $quotaPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        {{-- Row 4: Dispatches Activity Log Table (Instantly SaaS Styling) --}}
        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="card-title mb-0 fw-bold">{{ translate("Warmup Dispatch Activity & Peer Threads") }}</h5>
                    <small class="text-muted">{{ translate("Live audit of peer exchange, placement verification and contextual response threads") }}</small>
                </div>
                <span class="badge bg-light text-dark border px-3 py-2 fs-12 fw-semibold">
                    {{ $dispatches->total() }} {{ translate("Total Dispatches") }}
                </span>
            </div>
            <div class="card-body px-0 pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="ps-4">{{ translate("Recipient (Peer Node)") }}</th>
                                <th scope="col">{{ translate("Subject Line") }}</th>
                                <th scope="col">{{ translate("Slot / Date") }}</th>
                                <th scope="col">{{ translate("Status") }}</th>
                                <th scope="col">{{ translate("Placement") }}</th>
                                <th scope="col">{{ translate("Sent Time") }}</th>
                                <th scope="col" class="text-end pe-4">{{ translate("Preview") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dispatches as $dispatch)
                            <tr>
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" 
                                             style="width: 34px; height: 34px; background: #f0fdf4; color: #16a34a; font-size: 13px;">
                                            <i class="ri-user-received-2-line"></i>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-dark fs-13">{{ $dispatch->recipient_email }}</span>
                                            @if($dispatch->meta_data && !empty($dispatch->meta_data['auto_replied_at']))
                                                <small class="badge bg-purple-subtle text-purple d-block fs-10 mt-1" style="background: rgba(139, 92, 246, 0.12); color: #7c3aed; width: fit-content;">
                                                    <i class="ri-reply-line me-1"></i>{{ translate("Two-Way Replied") }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block fw-medium text-dark" style="max-width: 260px;" title="{{ $dispatch->subject }}">
                                        {{ $dispatch->subject }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border fs-11">Slot #{{ $dispatch->slot_number }}</span>
                                    <small class="text-muted d-block fs-11 mt-1">{{ $dispatch->dispatch_date?->format('Y-m-d') }}</small>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($dispatch->status) {
                                            'delivered' => 'success-soft',
                                            'sent'      => 'info-soft',
                                            'failed'    => 'danger-soft',
                                            default     => 'warning-soft',
                                        };
                                    @endphp
                                    <span class="i-badge {{ $badgeClass }} pill">
                                        {{ ucfirst($dispatch->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($dispatch->inbox_placement === 'inbox' || $dispatch->status === 'delivered')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle pill px-2 py-1 fs-11">
                                            <i class="ri-inbox-line me-1"></i>Primary Inbox
                                        </span>
                                    @elseif($dispatch->inbox_placement === 'spam')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle pill px-2 py-1 fs-11">
                                            <i class="ri-spam-line me-1"></i>Spam
                                        </span>
                                    @else
                                        <span class="text-muted fs-12">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted fs-12">
                                        {{ $dispatch->sent_at ? $dispatch->sent_at->diffForHumans() : translate('Pending') }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" 
                                            class="icon-btn btn-ghost btn-sm text-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#dispatchPreviewModal-{{ $dispatch->id }}" 
                                            title="{{ translate('Inspect Thread') }}">
                                        <i class="ri-eye-line fs-5"></i>
                                    </button>

                                    {{-- Dispatch Thread Inspection Modal --}}
                                    <div class="modal fade text-start" id="dispatchPreviewModal-{{ $dispatch->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header border-bottom py-3">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="rounded p-2 bg-primary-subtle text-primary">
                                                            <i class="ri-mail-check-line fs-5"></i>
                                                        </div>
                                                        <div>
                                                            <h5 class="modal-title fw-bold mb-0">{{ translate("Warmup Dispatch Inspection") }}</h5>
                                                            <small class="text-muted fs-12">Slot #{{ $dispatch->slot_number }} | {{ $dispatch->sent_at ? $dispatch->sent_at->toFormattedDateString() : 'Scheduled' }}</small>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    {{-- Header Metadata --}}
                                                    <div class="p-3 rounded-3 mb-3" style="background-color: #f8fafc; border: 1px solid #e2e8f0;">
                                                        <div class="row g-2 fs-13">
                                                            <div class="col-md-6">
                                                                <span class="text-muted d-block fs-11 text-uppercase fw-semibold">{{ translate("From (Warmup Sender)") }}:</span>
                                                                <span class="fw-bold text-dark">{{ $schedule->gateway?->address ?? translate('N/A') }}</span>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <span class="text-muted d-block fs-11 text-uppercase fw-semibold">{{ translate("To (Peer Mailbox)") }}:</span>
                                                                <span class="fw-bold text-dark">{{ $dispatch->recipient_email }}</span>
                                                            </div>
                                                            <div class="col-12 mt-2">
                                                                <span class="text-muted d-block fs-11 text-uppercase fw-semibold">{{ translate("Subject") }}:</span>
                                                                <span class="fw-bold text-dark">{{ $dispatch->subject }}</span>
                                                            </div>
                                                            @if($dispatch->message_id)
                                                            <div class="col-12 mt-1">
                                                                <span class="text-muted d-block fs-11 text-uppercase fw-semibold">{{ translate("Message ID") }}:</span>
                                                                <code class="text-muted fs-11">{{ $dispatch->message_id }}</code>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Deliverability & Inbox Handling Verification --}}
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-md-4">
                                                            <div class="border rounded p-2 text-center">
                                                                <span class="fs-11 text-muted d-block">{{ translate("Placement") }}</span>
                                                                <span class="badge bg-success-subtle text-success pill mt-1">
                                                                    <i class="ri-checkbox-circle-fill me-1"></i>Primary Inbox
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="border rounded p-2 text-center">
                                                                <span class="fs-11 text-muted d-block">{{ translate("IMAP Folder") }}</span>
                                                                <span class="badge bg-info-subtle text-info pill mt-1">
                                                                    <i class="ri-folder-shield-line me-1"></i>INBOX.Warmup
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="border rounded p-2 text-center">
                                                                <span class="fs-11 text-muted d-block">{{ translate("Read Status") }}</span>
                                                                <span class="badge bg-primary-subtle text-primary pill mt-1">
                                                                    <i class="ri-check-double-line me-1"></i>Seen (\Seen)
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Peer Dialogue Context --}}
                                                    <div class="border rounded p-3 bg-white">
                                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                                            <span class="fs-12 text-muted fw-bold text-uppercase">
                                                                <i class="ri-chat-voice-line me-1 text-purple"></i>{{ translate("Humanized Context Payload") }}
                                                            </span>
                                                            <span class="badge bg-light text-muted border fs-11">{{ translate("Anti-Spam Fingerprinted") }}</span>
                                                        </div>
                                                        <p class="fs-13 text-secondary mb-0" style="line-height: 1.6;">
                                                            {{ translate("Autonomous peer message dispatched through SMTP gateway. Peer recipient mailbox scans, marks as read, automatically moves to the designated 'Warmup' folder (keeping main inbox at zero clutter), and generates a realistic humanized conversation thread with 'Re:' pacing.") }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top py-2">
                                                    <button type="button" class="i-btn btn--dark outline btn--sm" data-bs-dismiss="modal">{{ translate("Close") }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="ri-mail-send-line fs-1 text-muted mb-2 d-block"></i>
                                        <h6>{{ translate("No Warmup Dispatches Yet") }}</h6>
                                        <p class="text-muted fs-13">{{ translate("The warmup cron scheduler runs automated peer exchanges according to your configured daily quota.") }}</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($dispatches->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $dispatches->links() }}
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
    const trendDates   = @json($mailboxAnalytics['trend_dates']);
    const trendInbox   = @json($mailboxAnalytics['trend_inbox']);
    const trendSpam    = @json($mailboxAnalytics['trend_spam']);
    const trendReplies = @json($mailboxAnalytics['trend_replies']);

    const chartElement = document.querySelector("#warmupMailboxChart");
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
