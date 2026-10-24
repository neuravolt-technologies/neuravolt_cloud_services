service_path = '/var/www/fastuser/data/www/teams.neuravolt.in/apps/api/src/modules/leads/leads.service.ts'

with open(service_path, 'r') as f:
    s = f.read()

# 1. Update clientHtml header with official Cloud Services logo
old_client_header = '''          <div style="margin-bottom: 24px;">
            <span style="font-size: 22px; font-weight: 800; color: #FF7722; letter-spacing: -0.5px;">NEURA</span><span style="font-size: 22px; font-weight: 800; color: #0F172A; letter-spacing: -0.5px;">VOLT</span>
            <span style="font-size: 13px; color: #64748B; margin-left: 8px;">Cloud Services</span>
          </div>'''

new_client_header = '''          <div style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #E2E8F0;">
            <a href="https://neuravolt.cloud" target="_blank" style="text-decoration: none; display: inline-block;">
              <img src="https://neuravolt.cloud/assets/logos/neuravolt_cloud_logo.png" alt="NeuraVolt Cloud Services" width="180" style="width: 180px; max-width: 100%; height: auto; display: block; border: 0;" />
            </a>
          </div>'''

if old_client_header in s:
    s = s.replace(old_client_header, new_client_header)
    print("Replaced client email text with NeuraVolt Cloud Services logo!")
else:
    print("Old client header not found exactly, checking alternatives...")

# 2. Update adminHtml header with dark logo
old_admin_header = '''      const adminHtml = `
        <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 24px; background: #0F172A; color: #F8FAFC; border-radius: 10px; max-width: 560px;">
          <h2 style="color: #FF7722; margin-top: 0;">New Verified Lead & Deployment Request</h2>'''

new_admin_header = '''      const adminHtml = `
        <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 24px; background: #0F172A; color: #F8FAFC; border-radius: 10px; max-width: 560px;">
          <div style="margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid #334155;">
            <a href="https://neuravolt.cloud" target="_blank" style="text-decoration: none; display: inline-block;">
              <img src="https://neuravolt.cloud/assets/logos/neuravolt_cloud_logo_dark.png" alt="NeuraVolt Cloud Services" width="180" style="width: 180px; max-width: 100%; height: auto; display: block; border: 0;" />
            </a>
          </div>
          <h2 style="color: #FF7722; margin-top: 0; font-size: 20px;">New Verified Lead & Deployment Request</h2>'''

if old_admin_header in s:
    s = s.replace(old_admin_header, new_admin_header)
    print("Added dark Cloud Services logo to admin email template!")

# 3. Update OTP email with logo
old_otp_html = '''html: `<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 28px; background: #FAFAFA; border-radius: 12px; max-width: 500px; margin: 0 auto; border: 1px solid #E2E8F0;"><h2 style="color: #0F172A; margin-top: 0; font-size: 20px;">NeuraVolt Security Verification</h2>'''

new_otp_html = '''html: `<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 28px; background: #FAFAFA; border-radius: 12px; max-width: 500px; margin: 0 auto; border: 1px solid #E2E8F0;"><div style="margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid #E2E8F0;"><a href="https://neuravolt.cloud" target="_blank" style="text-decoration: none; display: inline-block;"><img src="https://neuravolt.cloud/assets/logos/neuravolt_cloud_logo.png" alt="NeuraVolt Cloud Services" width="170" style="width: 170px; max-width: 100%; height: auto; display: block; border: 0;" /></a></div><h2 style="color: #0F172A; margin-top: 0; font-size: 20px;">Security Verification</h2>'''

if old_otp_html in s:
    s = s.replace(old_otp_html, new_otp_html)
    print("Added Cloud Services logo to OTP verification email!")

with open(service_path, 'w') as f:
    f.write(s)

print("Saved updated leads.service.ts successfully!")
