import re

service_path = '/var/www/fastuser/data/www/teams.neuravolt.in/apps/api/src/modules/leads/leads.service.ts'
controller_path = '/var/www/fastuser/data/www/teams.neuravolt.in/apps/api/src/modules/leads/leads.controller.ts'

# 1. Update controller
with open(controller_path, 'r') as f:
    ctrl = f.read()

if 'sendOtp' not in ctrl:
    otp_endpoints = """
  /**
   * Send 6-digit OTP verification email
   */
  @Post('send-otp')
  async sendOtp(@Body('email') email: string) {
    return this.leadsService.sendOtp(email);
  }

  /**
   * Verify email OTP
   */
  @Post('verify-otp')
  async verifyOtp(@Body() body: { email: string; otp: string }) {
    return this.leadsService.verifyOtp(body.email, body.otp);
  }
"""
    ctrl = ctrl.replace("@Post('capture')", otp_endpoints + "\n  @Post('capture')")
    with open(controller_path, 'w') as f:
        f.write(ctrl)
    print("Updated leads.controller.ts successfully!")
else:
    print("leads.controller.ts already contains sendOtp")

# 2. Update service
with open(service_path, 'r') as f:
    svc = f.read()

if 'sendOtp' not in svc:
    if "import Redis from 'ioredis';" not in svc:
        svc = "import Redis from 'ioredis';\nimport { Queue } from 'bullmq';\n" + svc

    otp_logic = """
  private redisClient: Redis;
  private notificationQueue: Queue;

  private getRedis(): Redis {
    if (!this.redisClient) {
      this.redisClient = new Redis(process.env.REDIS_URL || 'redis://localhost:6379', {
        maxRetriesPerRequest: null,
      });
    }
    return this.redisClient;
  }

  private getNotificationQueue(): Queue {
    if (!this.notificationQueue) {
      this.notificationQueue = new Queue('notifications-queue', {
        connection: {
          url: process.env.REDIS_URL || 'redis://localhost:6379',
          maxRetriesPerRequest: null,
        },
      });
    }
    return this.notificationQueue;
  }

  async sendOtp(email: string): Promise<{ success: boolean; message: string }> {
    if (!email || !email.includes('@')) {
      throw new BadRequestException('Please provide a valid email address');
    }
    const cleanEmail = email.trim().toLowerCase();
    const redis = this.getRedis();

    const rateKey = `rate:otp:${cleanEmail}`;
    const attempts = await redis.incr(rateKey);
    if (attempts === 1) {
      await redis.expire(rateKey, 600);
    }
    if (attempts > 5) {
      throw new BadRequestException('Too many OTP attempts. Please wait 10 minutes.');
    }

    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    await redis.setex(`otp:${cleanEmail}`, 600, otp);

    try {
      const q = this.getNotificationQueue();
      await q.add('email-job', {
        to: cleanEmail,
        template: 'generic_alert',
        variables: {
          title: 'NeuraVolt Verification Code',
          message: `<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 24px; background: #FAFAFA; border-radius: 12px; max-width: 500px; margin: 0 auto; border: 1px solid #E2E8F0;"><h2 style="color: #0F172A; margin-top: 0;">NeuraVolt Security Verification</h2><p style="color: #475569; font-size: 15px; line-height: 1.5;">Your one-time verification code for NeuraVolt Cloud Services & Guardian Defense is:</p><div style="font-size: 36px; font-weight: 800; letter-spacing: 8px; color: #FF7722; margin: 24px 0; padding: 16px 24px; background: #FFF7ED; border: 1px solid #FFEDD5; border-radius: 10px; text-align: center;">${otp}</div><p style="color: #64748B; font-size: 13px; line-height: 1.5;">This code is valid for 10 minutes. Do not share it with anyone.<br/>If you did not request this verification, please disregard this email.</p></div>`,
        },
        subject: `Your NeuraVolt Verification Code: ${otp}`,
      });
    } catch (err) {
      console.error('Failed to queue OTP notification:', err);
    }

    return {
      success: true,
      message: 'Verification code sent to your email.',
    };
  }

  async verifyOtp(email: string, otp: string): Promise<{ success: boolean; verified: boolean; message: string }> {
    if (!email || !otp) {
      throw new BadRequestException('Email and verification code are required');
    }
    const cleanEmail = email.trim().toLowerCase();
    const cleanOtp = otp.trim();
    const redis = this.getRedis();

    const storedOtp = await redis.get(`otp:${cleanEmail}`);
    if (!storedOtp) {
      return {
        success: false,
        verified: false,
        message: 'Verification code has expired or was not requested. Please click Send OTP.',
      };
    }

    if (storedOtp !== cleanOtp) {
      return {
        success: false,
        verified: false,
        message: 'Invalid verification code. Please check and try again.',
      };
    }

    await redis.setex(`verified:${cleanEmail}`, 1800, '1');
    await redis.del(`otp:${cleanEmail}`);

    return {
      success: true,
      verified: true,
      message: 'Email successfully verified.',
    };
  }
"""
    svc = svc.replace("async captureLead(captureLeadDto: CaptureLeadDto)", otp_logic + "\n  async captureLead(captureLeadDto: CaptureLeadDto)")
    with open(service_path, 'w') as f:
        f.write(svc)
    print("Updated leads.service.ts successfully!")
else:
    print("leads.service.ts already contains sendOtp")
