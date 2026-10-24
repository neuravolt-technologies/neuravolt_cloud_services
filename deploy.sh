#!/usr/bin/env bash
set -e

# ========================================================
# Automated Deployment Script for neuravolt_cloud_services
# Target Server: FastPanel / Ubuntu Linux VPS
# ========================================================

DEPLOY_BRANCH="${DEPLOY_BRANCH:-main}"
SERVICE_NAME="neuravolt-cloud-website-service"
PORT="${PORT:-3080}"
REPO_URL="https://github.com/neuravolt-technologies/neuravolt_cloud_services.git"

echo "=========================================="
echo "Starting Deployment for branch: ${DEPLOY_BRANCH}"
echo "Service Name: ${SERVICE_NAME}"
echo "Assigned Port: ${PORT}"
echo "Timestamp: $(date '+%Y-%m-%d %H:%M:%S')"
echo "=========================================="

# 1. Ensure git repo and sync latest changes
echo "[1/5] Syncing latest code from branch ${DEPLOY_BRANCH}..."
git config --global --add safe.directory "$(pwd)" || true

if [ ! -d ".git" ]; then
    echo "Initializing git repository..."
    git init
    git remote add origin "${REPO_URL}" 2>/dev/null || git remote set-url origin "${REPO_URL}"
fi

git fetch origin "${DEPLOY_BRANCH}"
git reset --hard "origin/${DEPLOY_BRANCH}"
git checkout -B "${DEPLOY_BRANCH}" "origin/${DEPLOY_BRANCH}" || true

# 2. Install dependencies
echo "[2/5] Installing dependencies..."
if [ -f "package-lock.json" ]; then
    npm ci --prefer-offline --no-audit --legacy-peer-deps || npm install --legacy-peer-deps --no-audit
elif [ -f "pnpm-lock.yaml" ]; then
    pnpm install --frozen-lockfile || pnpm install
elif [ -f "package.json" ]; then
    npm install --legacy-peer-deps --no-audit
fi

# Sub-directories if applicable
if [ -d "backend" ] && [ -f "backend/package.json" ]; then
    echo "Installing backend dependencies..."
    (cd backend && npm install --legacy-peer-deps --no-audit)
fi
if [ -d "app" ] && [ -f "app/package.json" ]; then
    echo "Installing app dependencies..."
    (cd app && npm install --legacy-peer-deps --no-audit)
fi

# 3. Production Build
echo "[3/5] Running production build..."
if [ -f "package.json" ] && grep -q '"build":' package.json; then
    npm run build
fi
if [ -d "backend" ] && [ -f "backend/package.json" ]; then
    echo "Building backend..."
    (cd backend && npx prisma generate && npm run build)
fi
if [ -d "app" ] && [ -f "app/package.json" ]; then
    echo "Building app..."
    (cd app && npm run build)
fi

# 4. PM2 Process Management
echo "[4/5] Managing PM2 service: ${SERVICE_NAME} on Port ${PORT}..."
# Determine entry point
if [ -f "backend/dist/index.js" ]; then
    ENTRY="backend/dist/index.js"
elif [ -f "dist/main.js" ]; then
    ENTRY="dist/main.js"
elif [ -f "dist/index.js" ]; then
    ENTRY="dist/index.js"
elif [ -d "app" ] && [ -f "app/package.json" ]; then
    ENTRY="npm --name ${SERVICE_NAME} -- start --prefix app -- -p ${PORT}"
else
    ENTRY="dist/main.js"
fi

if command -v pm2 >/dev/null 2>&1; then
    if pm2 describe "${SERVICE_NAME}" > /dev/null 2>&1; then
        echo "Restarting existing PM2 service ${SERVICE_NAME}..."
        PORT=${PORT} pm2 restart "${SERVICE_NAME}" --update-env
    else
        echo "Starting PM2 service ${SERVICE_NAME} on port ${PORT}..."
        if [[ "$ENTRY" == npm* ]]; then
            PORT=${PORT} pm2 start $ENTRY
        else
            PORT=${PORT} pm2 start "${ENTRY}" --name "${SERVICE_NAME}" --update-env
        fi
    fi
    pm2 save
else
    echo "PM2 is not installed globally; skipping PM2 reload."
fi

# 5. Reload nv-landing container if docker is available to refresh static landing assets
if command -v docker >/dev/null 2>&1 && docker ps | grep -q nv-landing; then
    echo "[5/5] Reloading nv-landing nginx cache..."
    docker exec nv-landing nginx -s reload || true
else
    echo "[5/5] Skipping nv-landing reload (not running or docker unavailable)"
fi

echo "=========================================="
echo "Deployment completed successfully!"
echo "=========================================="
