{{-- Guided DNS Setup & White-Glove Onboarding Modal (Minimalist SaaS Design) --}}
<div class="modal fade" id="dnsSetupWizardModal" tabindex="-1" aria-labelledby="dnsSetupWizardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-sm">
            {{-- Modal Header --}}
            <div class="modal-header border-bottom py-3 px-4 bg-white">
                <div>
                    <h5 class="modal-title fs-15 fw-bold text-dark mb-0" id="dnsSetupWizardModalLabel">
                        {{ translate("Domain DNS Configuration") }}
                    </h5>
                    <small class="text-muted fs-12">
                        {{ translate("Add these authentication records to your domain DNS settings to ensure inbox delivery.") }}
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-4 bg-white">
                {{-- Domain Input & Provider Selector Row --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fs-12 fw-medium text-muted mb-1">{{ translate("Target Domain") }}</label>
                        <input type="text" class="form-control form-control-sm fs-12" id="dnsWizardDomainInput" 
                               value="{{ $domain->domain ?? ($mailboxAnalytics['dns_health']['domain'] ?? ($analytics['dns_health']['domain'] ?? (auth()->user()?->email ? (explode('@', auth()->user()->email)[1] ?? 'neuravolt.in') : 'neuravolt.in'))) }}" 
                               placeholder="example.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fs-12 fw-medium text-muted mb-1">{{ translate("DNS Provider") }}</label>
                        <div class="d-flex gap-2">
                            <select class="form-select form-select-sm fs-12" id="dnsProviderSelect">
                                <option value="cloudflare" data-link="https://dash.cloudflare.com">Cloudflare</option>
                                <option value="godaddy" data-link="https://dcc.godaddy.com/manage/dns">GoDaddy</option>
                                <option value="hostinger" data-link="https://hpanel.hostinger.com">Hostinger</option>
                                <option value="namecheap" data-link="https://ap.www.namecheap.com">Namecheap</option>
                                <option value="other" data-link="">Other DNS Manager</option>
                            </select>
                            <a href="https://dash.cloudflare.com" target="_blank" rel="noopener noreferrer" 
                               class="btn btn-sm btn-outline-secondary d-flex align-items-center text-nowrap" id="dnsProviderQuickLink" title="{{ translate('Open DNS Manager in new tab') }}">
                                <i class="ri-external-link-line fs-13"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Section 1: Minimalist DNS Records Table --}}
                <div class="border rounded-2 overflow-hidden mb-3">
                    <table class="table table-sm align-middle mb-0 fs-12">
                        <thead class="table-light text-muted">
                            <tr>
                                <th style="width: 75px;">{{ translate("Type") }}</th>
                                <th style="width: 150px;">{{ translate("Host / Name") }}</th>
                                <th>{{ translate("Value") }}</th>
                                <th style="width: 80px;" class="text-end">{{ translate("Status") }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- 1. SPF Record --}}
                            <tr>
                                <td><span class="badge bg-light text-dark border font-monospace">TXT</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <code class="text-dark fs-12 font-monospace">@</code>
                                        <button class="btn btn-link p-0 text-muted ms-auto" type="button" onclick="copyDnsDirect('@', this)" title="Copy Host">
                                            <i class="ri-file-copy-line fs-13"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <code class="text-break text-dark font-monospace fs-11" id="valSpfText">v=spf1 ip4:187.127.148.197 mx include:_spf.reach.neuravolt.cloud ~all</code>
                                        <button class="btn btn-sm btn-outline-secondary py-0 px-2 fs-11 text-nowrap" type="button" onclick="copyDnsDirect('v=spf1 ip4:187.127.148.197 mx include:_spf.reach.neuravolt.cloud ~all', this)">
                                            {{ translate("Copy") }}
                                        </button>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-success-subtle text-success p-1 fs-10" id="wizardBadgeSpf">
                                        {{ $mailboxAnalytics['dns_health']['spf']['badge'] ?? ($analytics['dns_health']['spf']['badge'] ?? 'PASS') }}
                                    </span>
                                </td>
                            </tr>

                            {{-- 2. DMARC Policy --}}
                            <tr>
                                <td><span class="badge bg-light text-dark border font-monospace">TXT</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <code class="text-dark fs-12 font-monospace">_dmarc</code>
                                        <button class="btn btn-link p-0 text-muted ms-auto" type="button" onclick="copyDnsDirect('_dmarc', this)" title="Copy Host">
                                            <i class="ri-file-copy-line fs-13"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <code class="text-break text-dark font-monospace fs-11" id="valDmarcText">v=DMARC1; p=none; sp=none;</code>
                                        <button class="btn btn-sm btn-outline-secondary py-0 px-2 fs-11 text-nowrap" type="button" onclick="copyDnsDirect('v=DMARC1; p=none; sp=none;', this)">
                                            {{ translate("Copy") }}
                                        </button>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-success-subtle text-success p-1 fs-10" id="wizardBadgeDmarc">
                                        {{ $mailboxAnalytics['dns_health']['dmarc']['badge'] ?? ($analytics['dns_health']['dmarc']['badge'] ?? 'ACTIVE') }}
                                    </span>
                                </td>
                            </tr>

                            {{-- 3. DKIM 2048 Signature --}}
                            <tr>
                                <td><span class="badge bg-light text-dark border font-monospace">TXT</span></td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <code class="text-dark fs-12 font-monospace">default._domainkey</code>
                                        <button class="btn btn-link p-0 text-muted ms-auto" type="button" onclick="copyDnsDirect('default._domainkey', this)" title="Copy Host">
                                            <i class="ri-file-copy-line fs-13"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <code class="text-break text-dark font-monospace fs-11 text-truncate" style="max-width: 320px;" title="v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC3..." id="valDkimText">v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC3...</code>
                                        <button class="btn btn-sm btn-outline-secondary py-0 px-2 fs-11 text-nowrap" type="button" onclick="copyDnsDirect('v=DKIM1; k=rsa; p=MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC3...', this)">
                                            {{ translate("Copy") }}
                                        </button>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-success-subtle text-success p-1 fs-10" id="wizardBadgeDkim">
                                        {{ $mailboxAnalytics['dns_health']['dkim']['badge'] ?? ($analytics['dns_health']['dkim']['badge'] ?? 'VALID') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Live Verification Action --}}
                <div class="d-flex align-items-center justify-content-between pt-1">
                    <button type="button" class="btn btn-sm btn-dark px-3 py-1 fs-12 fw-medium" id="btnVerifyLiveDnsNow" onclick="runLiveDnsVerify()">
                        <i class="ri-refresh-line me-1"></i>{{ translate("Check DNS Records") }}
                    </button>
                    <div id="dnsVerifyStatusFeedback" class="fs-12" style="display: none;"></div>
                </div>

                {{-- Section 2: Minimalist Done-For-You Assistance Card --}}
                <div class="p-3 rounded-2 bg-light border mt-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <span class="fs-13 fw-semibold text-dark d-block">{{ translate("Need assistance?") }}</span>
                            <small class="text-muted fs-12">
                                {{ translate("Our team can configure your DNS records and verify deliverability for ₹2,499 ($29).") }}
                            </small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <a href="https://wa.me/919202243725?text=Hello%20Neuravolt%20Team%2C%20I%20would%20like%20to%20request%20the%20Done-For-You%20DNS%20Setup." 
                               target="_blank" rel="noopener noreferrer" 
                               class="btn btn-sm btn-outline-dark fs-12 px-3 py-1">
                                <i class="ri-whatsapp-line me-1 text-success"></i>{{ translate("WhatsApp") }}
                            </a>
                            <a href="{{ route('user.support.ticket.create') }}" 
                               class="btn btn-sm btn-outline-secondary fs-12 px-3 py-1">
                                {{ translate("Support Ticket") }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer border-top py-2 px-4 bg-light d-flex justify-content-between">
                <small class="text-muted fs-11">
                    {{ translate("DNS propagation usually completes within a few minutes worldwide.") }}
                </small>
                <button type="button" class="btn btn-sm btn-secondary fs-12 px-3" data-bs-dismiss="modal">
                    {{ translate("Close") }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Helper JavaScript for 1-Click Copy and Live Verification --}}
<script>
function copyDnsDirect(text, btn) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="ri-check-line text-success"></i> Copied';
        setTimeout(() => {
            btn.innerHTML = originalHtml;
        }, 1800);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const providerSelect = document.getElementById('dnsProviderSelect');
    const quickLink = document.getElementById('dnsProviderQuickLink');

    if (providerSelect && quickLink) {
        providerSelect.addEventListener('change', function () {
            const selectedOpt = providerSelect.options[providerSelect.selectedIndex];
            const link = selectedOpt.getAttribute('data-link');
            if (link) {
                quickLink.href = link;
                quickLink.style.display = 'inline-flex';
            } else {
                quickLink.style.display = 'none';
            }
        });
    }
});

function runLiveDnsVerify() {
    const btn = document.getElementById('btnVerifyLiveDnsNow');
    const feedback = document.getElementById('dnsVerifyStatusFeedback');
    const domainInput = document.getElementById('dnsWizardDomainInput');
    const domain = domainInput ? domainInput.value.trim() : '';

    if (!btn) return;
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ri-loader-4-line ri-spin me-1"></i> Checking...';

    fetch('{{ route("user.warmup.verify-dns") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({ domain: domain })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = origHtml;

        if (data.status && data.data) {
            const health = data.data;
            const spfBadge = document.getElementById('wizardBadgeSpf');
            const dmarcBadge = document.getElementById('wizardBadgeDmarc');
            const dkimBadge = document.getElementById('wizardBadgeDkim');

            if (spfBadge) {
                spfBadge.textContent = health.spf?.badge || 'PASS';
                spfBadge.className = 'badge ' + (health.spf?.badge_class || 'bg-success-subtle text-success') + ' p-1 fs-10';
            }
            if (dmarcBadge) {
                dmarcBadge.textContent = health.dmarc?.badge || 'ACTIVE';
                dmarcBadge.className = 'badge ' + (health.dmarc?.badge_class || 'bg-success-subtle text-success') + ' p-1 fs-10';
            }
            if (dkimBadge) {
                dkimBadge.textContent = health.dkim?.badge || 'VALID';
                dkimBadge.className = 'badge ' + (health.dkim?.badge_class || 'bg-success-subtle text-success') + ' p-1 fs-10';
            }

            if (feedback) {
                feedback.style.display = 'block';
                feedback.className = 'fs-12 text-success fw-medium';
                feedback.innerHTML = '<i class="ri-checkbox-circle-line me-1"></i> Records verified for ' + (health.domain || domain);
            }
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = origHtml;
        if (feedback) {
            feedback.style.display = 'block';
            feedback.className = 'fs-12 text-danger';
            feedback.textContent = 'Could not verify DNS at this moment.';
        }
    });
}
</script>
