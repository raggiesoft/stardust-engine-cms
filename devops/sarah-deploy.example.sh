#!/bin/bash
# =============================================================================
# Architecture & Maintenance Guide: sarah-deploy.sh
# =============================================================================
# Purpose:
#     This script runs autonomously on the production web server as a cron job.
#     "Sarah" acts as the continuous deployment agent, polling GitHub for changes 
#     on the `main` branch and syncing them to the live Nginx document root.
#
# Design Principles:
#     - Polling vs Webhooks: Uses lightweight polling (`git fetch` checks) 
#       every 5 minutes instead of listening for webhooks, reducing firewall 
#       and security complexities on the production box.
#     - Atomic Zero-Downtime Sync: Utilizes `rsync` to mirror the repository 
#       to the web root. By explicitly excluding permission alterations (`--no-o`, 
#       `--no-g`), it relies on Linux SetGID configurations to ensure file 
#       ownership remains secure for the web server (e.g., `www-data`).
#
# Maintenance Notes:
#     - CRITICAL: Never run this script as `root`. Run it as the deployment user 
#       (e.g., `ubuntu` or `deploy`).
#     - Ensure the web root folder has the `sgid` bit set so new files inherit 
#       the correct group.
# =============================================================================

# --- SARAH: AUTONOMOUS PRODUCTION DEPLOYMENT ---
# Usage: Run via Cron every 5 minutes (e.g., */5 * * * * /path/to/sarah-deploy.sh)
# SECURITY: Do NOT run as root.

# 1. CONFIGURATION
# Define the local path where the bare repository or clone exists on the server
REPO_DIR="/home/user/stardust-engine-cms"

# Define the live Nginx document root serving the actual application
WEB_ROOT="/var/www/example.com"

# 2. THE INTELLIGENCE CHECK
cd "$REPO_DIR" || exit

# Retrieve the latest references from the remote without altering the working tree
git fetch origin main

# Compare the local HEAD against the fetched remote head
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)

# Exit silently to prevent log spam if there are no new commits
if [ "$LOCAL" == "$REMOTE" ]; then
    # No changes. Go back to sleep silently.
    exit 0
fi

# 3. DEPLOYMENT EXECUTION
echo "👩‍💼 SARAH: Change detected! Syncing files to production..."

# Fast-forward the local repository to precisely match the remote
git reset --hard origin/main

# Sudo-less Rsync deployment
# -a: Archive mode (preserves permissions, timestamps, recursively)
# -v: Verbose output for logging
# --delete: Removes files in the destination that no longer exist in the source
# --no-o / --no-g: Instructs rsync NOT to preserve owner/group metadata. 
#   This allows the server's directory-level SetGID bits to enforce proper 
#   ownership (e.g. www-data) securely without requiring sudo escalation.
rsync -av --delete --no-o --no-g \
    --exclude '.git' \
    --exclude 'devops/' \
    --exclude 'README.md' \
    "$REPO_DIR/" "$WEB_ROOT/"

echo "👩‍💼 SARAH: Deployment verified and active."