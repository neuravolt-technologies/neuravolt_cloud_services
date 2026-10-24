path = '/var/www/fastuser/data/www/teams.neuravolt.in/apps/api/src/main.ts'
with open(path, 'r') as f:
    code = f.read()

old_cors = """  // Enable CORS for frontend requests
  app.enableCors({
    origin: process.env.ALLOWED_ORIGINS
      ? process.env.ALLOWED_ORIGINS.split(',').map((o) => o.trim())
      : ['http://localhost:3000', 'http://localhost:3003'],
    credentials: true,
  });"""

new_cors = """  // Enable CORS for frontend and external lead capture requests
  app.enableCors({
    origin: true,
    credentials: true,
    methods: ['GET', 'HEAD', 'PUT', 'PATCH', 'POST', 'DELETE', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'x-api-key', 'X-Requested-With', 'Accept'],
  });"""

if old_cors in code:
    code = code.replace(old_cors, new_cors)
    with open(path, 'w') as f:
        f.write(code)
    print("Fixed CORS in main.ts successfully")
else:
    print("old_cors not found")
