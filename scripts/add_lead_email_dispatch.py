import re

service_path = '/var/www/fastuser/data/www/teams.neuravolt.in/apps/api/src/modules/leads/leads.service.ts'

with open(service_path, 'r') as f:
    s = f.read()

# Add dispatchLeadEmails helper if not present
helper_code = '''  private async dispatchLeadEmails(lead: {
    id: string;
    name: string;
    email?: string;
    phone?: string;
    service?: string;
    notes?: string;
  }) {
    if (!lead.email) return;

    try {
      const transporter = nodemailer.createTransport({
        host: '127.0.0.1',
        port: 25,
        secure: false,
        tls: { rejectUnauthorized: false },
      });

      const serviceName = lead.service || 'Cloud Infrastructure & SRE';

      // 1. Client Confirmation Email
      const clientHtml = `
        <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 32px 24px; background: #FAFAFA; border-radius: 12px; max-width: 560px; margin: 0 auto; border: 1px solid #E2E8F0;">
          <div style="margin-bottom: 24px;">
            <span style="font-size: 22px; font-weight: 800; color: #FF7722; letter-spacing: -0.5px;">NEURA</span><span style="font-size: 22px; font-weight: 800; color: #0F172A; letter-spacing: -0.5px;">VOLT</span>
            <span style="font-size: 13px; color: #64748B; margin-left: 8px;">Cloud Services</span>
          </div>

          <h2 style="color: #0F172A; margin: 0 0 12px; font-size: 22px;">Inquiry Received Successfully</h2>
          <p style="color: #475569; font-size: 15px; line-height: 1.6; margin: 0 0 20px;">
            Hi <strong>${lead.name}</strong>,<br/>
            Thank you for reaching out to <strong>NeuraVolt</strong>. We have received your verified inquiry and our cloud infrastructure team has logged your specifications.
          </p>

          <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 20px; margin-bottom: 24px;">
            <h3 style="margin: 0 0 14px; font-size: 13px; text-transform: uppercase; letter-spacing: 0.06em; color: #64748B;">Request Summary</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
              <tr>
                <td style="padding: 6px 0; color: #64748B; width: 140px;">Selected Service:</td>
                <td style="padding: 6px 0; color: #0F172A; font-weight: 600;">${serviceName}</td>
              </tr>
              <tr>
                <td style="padding: 6px 0; color: #64748B;">Verified Email:</td>
                <td style="padding: 6px 0; color: #0F172A;">${lead.email}</td>
              </tr>
              ${lead.phone ? `<tr><td style="padding: 6px 0; color: #64748B;">Phone:</td><td style="padding: 6px 0; color: #0F172A;">${lead.phone}</td></tr>` : ''}
              <tr>
                <td style="padding: 6px 0; color: #64748B;">Reference ID:</td>
                <td style="padding: 6px 0; color: #64748B; font-family: monospace;">${lead.id}</td>
              </tr>
            </table>
          </div>

          <div style="border-left: 3px solid #FF7722; padding-left: 14px; margin-bottom: 24px;">
            <p style="color: #0F172A; font-size: 14px; font-weight: 600; margin: 0 0 4px;">Next Steps:</p>
            <p style="color: #64748B; font-size: 13px; margin: 0; line-height: 1.5;">
              Our engineers will review your request and reach out with your instance access details and setup instructions shortly.
            </p>
          </div>

          <hr style="border: none; border-top: 1px solid #E2E8F0; margin: 24px 0;" />
          <p style="color: #94A3B8; font-size: 12px; line-height: 1.5; margin: 0;">
            NeuraVolt Autonomous Cloud Ecosystem • Powered by VOLTNOVATE SOLUTIONS LLP<br/>
            Direct Support: <a href="mailto:info@neuravolt.in" style="color: #FF7722;">info@neuravolt.in</a>
          </p>
        </div>
      `;

      // 2. Admin Alert Email
      const adminHtml = `
        <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 24px; background: #0F172A; color: #F8FAFC; border-radius: 10px; max-width: 560px;">
          <h2 style="color: #FF7722; margin-top: 0;">New Verified Lead & Deployment Request</h2>
          <p style="color: #94A3B8; font-size: 14px;">A new verified inquiry was registered on neuravolt.cloud:</p>
          <ul style="line-height: 1.8; font-size: 14px; color: #E2E8F0;">
            <li><strong>Service:</strong> <span style="color: #38BDF8;">${serviceName}</span></li>
            <li><strong>Client Name:</strong> ${lead.name}</li>
            <li><strong>Email:</strong> <a href="mailto:${lead.email}" style="color: #FF7722;">${lead.email}</a></li>
            <li><strong>Phone:</strong> ${lead.phone || 'N/A'}</li>
            <li><strong>Notes:</strong> ${lead.notes || 'None'}</li>
            <li><strong>Lead ID:</strong> <code>${lead.id}</code></li>
          </ul>
        </div>
      `;

      await Promise.all([
        transporter.sendMail({
          from: 'NeuraVolt Cloud <info@neuravolt.in>',
          to: lead.email,
          subject: `Inquiry Confirmation: ${serviceName} - NeuraVolt Cloud`,
          html: clientHtml,
        }),
        transporter.sendMail({
          from: 'NeuraVolt Cloud Ingestion <info@neuravolt.in>',
          to: 'info@neuravolt.in',
          subject: `🚀 New Verified Lead: ${serviceName} (${lead.name})`,
          html: adminHtml,
        }),
      ]);

      console.log(`[Lead Emails] Successfully dispatched confirmation to ${lead.email} and alert to info@neuravolt.in`);
    } catch (err) {
      console.error('[Lead Emails] Failed to dispatch lead emails:', err);
    }
  }
'''

if 'dispatchLeadEmails' not in s:
    # Insert helper before captureLead
    s = s.replace("async captureLead(captureLeadDto: CaptureLeadDto)", helper_code + "\n  async captureLead(captureLeadDto: CaptureLeadDto)")

# Call dispatchLeadEmails inside captureLead for existing and new leads
# For existing lead:
existing_old = '''        return {
          success: true,
          message: 'Inquiry successfully added to existing lead profile.',
          leadId: updated.id,
          isNew: false,
        };'''

existing_new = '''        await this.dispatchLeadEmails({
          id: updated.id,
          name: updated.name,
          email: updated.email || captureLeadDto.email,
          phone: updated.phone || captureLeadDto.phone,
          service: captureLeadDto.service,
          notes: captureLeadDto.notes,
        });

        return {
          success: true,
          message: 'Inquiry successfully added to existing lead profile.',
          leadId: updated.id,
          isNew: false,
        };'''

if existing_old in s and 'await this.dispatchLeadEmails' not in s:
    s = s.replace(existing_old, existing_new)

# For new lead:
new_old = '''    return {
      success: true,
      message: 'Lead successfully captured.',
      leadId: newLead.id,
      isNew: true,
    };'''

new_new = '''    await this.dispatchLeadEmails({
      id: newLead.id,
      name: newLead.name,
      email: newLead.email,
      phone: newLead.phone,
      service: captureLeadDto.service,
      notes: captureLeadDto.notes,
    });

    return {
      success: true,
      message: 'Lead successfully captured.',
      leadId: newLead.id,
      isNew: true,
    };'''

if new_old in s:
    s = s.replace(new_old, new_new)

with open(service_path, 'w') as f:
    f.write(s)

print("Successfully injected lead confirmation and admin alert emails into captureLead!")
