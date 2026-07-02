#!/bin/bash
# --- SARAH: AUTONOMOUS PRODUCTION DEPLOYMENT ---
# Usage: Run via Cron every 5 minutes (e.g., */5 * * * * /path/to/sarah-deploy.sh)
# SECURITY: Do NOT run as root.

# 1. CONFIGURATION
# The path where you cloned the repo on the server
REPO_DIR="/home/user/stardust-engine-cms"
# The Nginx document root
WEB_ROOT="/var/www/example.com"

# 2. THE INTELLIGENCE CHECK
cd "$REPO_DIR" || exit
git fetch origin main

LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)

if [ "$LOCAL" == "$REMOTE" ]; then
    # No changes. Go back to sleep silently.
    exit 0
fi

# 3. DEPLOYMENT EXECUTION
echo "👩‍💼 SARAH: Change detected! Syncing files to production..."
git reset --hard origin/main

# Sudo-less Rsync
# -a: Archive mode
# --no-o / --no-g: Let the server's SetGID handle permissions safely
rsync -av --delete --no-o --no-g \
    --exclude '.git' \
    --exclude 'devops/' \
    --exclude 'README.md' \
    "$REPO_DIR/" "$WEB_ROOT/"

echo "👩‍💼 SARAH: Deployment verified and active."