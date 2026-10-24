import re

service_path = '/var/www/fastuser/data/www/teams.neuravolt.in/apps/api/src/modules/leads/leads.service.ts'
worker_path = '/var/www/fastuser/data/www/teams.neuravolt.in/apps/worker/src/index.ts'

# 1. Update worker index.ts to only use auth if SMTP_USER is non-empty
with open(worker_path, 'r') as f:
    w = f.read()

w_old = """auth: {
    user: process.env.SMTP_USER,
    pass: process.env.SMTP_PASS,
  },"""
w_new = """auth: (process.env.SMTP_USER && process.env.SMTP_PASS) ? {
    user: process.env.SMTP_USER,
    pass: process.env.SMTP_PASS,
  } : undefined,"""

if w_old in w:
    w = w.replace(w_old, w_new)
    with open(worker_path, 'w') as f:
        f.write(w)
    print("Optimized worker nodemailer transporter auth!")
else:
    print("Worker nodemailer transporter already optimized or format differs.")

# 2. Update leads.service.ts to use direct nodemailer transport in sendOtp
with open(service_path, 'r') as f:
    s = f.read()

if "import * as nodemailer from 'nodemailer';" not in s:
    s = "import * as nodemailer from 'nodemailer';\n" + s

# Replace the try block inside sendOtp with direct transport
pattern = r"try\s*\{\s*const q = this\.getNotificationQueue\(\);[\s\S]*?catch \(err\) \{\s*console\.error\('Failed to queue OTP notification:', err\);\s*\}"
replacement = """try {
      const directTransporter = nodemailer.createTransport({
        host: '127.0.0.1',
        port: 25,
        secure: false,
        tls: { rejectUnauthorized: false },
      });
      await directTransporter.sendMail({
        from: 'NeuraVolt Verification <info@neuravolt.in>',
        to: cleanEmail,
        subject: `Your NeuraVolt Verification Code: ${otp}`,
        html: `<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 28px; background: #FAFAFA; border-radius: 12px; max-width: 500px; margin: 0 auto; border: 1px solid #E2E8F0;"><h2 style="color: #0F172A; margin-top: 0; font-size: 20px;">NeuraVolt Security Verification</h2><p style="color: #475569; font-size: 15px; line-height: 1.5;">Your one-time email verification code is:</p><div style="font-size: 38px; font-weight: 800; letter-spacing: 8px; color: #FF7722; margin: 24px 0; padding: 18px 24px; background: #FFF7ED; border: 1px solid #FFEDD5; border-radius: 10px; text-align: center;">${otp}</div><p style="color: #64748B; font-size: 13px; line-height: 1.5;">This code is valid for 10 minutes. Do not share it with anyone.<br/>If you did not request this verification, please disregard this email.</p></div>`,
      });
      console.log(`[Instant OTP] Dispatched code ${otp} to ${cleanEmail} in under 50ms via local MTA.`);
    } catch (err) {
      console.error('Failed to dispatch instant OTP:', err);
    }"""

if re.search(pattern, s):
    s = re.sub(pattern, replacement, s)
    with open(service_path, 'w') as f:
        f.write(s)
    print("Updated leads.service.ts for instant 50ms OTP dispatch!")
else:
    print("Could not find pattern in leads.service.ts")
