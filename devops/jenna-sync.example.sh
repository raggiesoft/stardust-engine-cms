#!/bin/bash
# =============================================================================
# Architecture & Maintenance Guide: jenna-sync.sh
# =============================================================================
# Purpose:
#     This script serves as the developer-facing command line interface (CLI) 
#     for synchronizing local modifications to the production environment. 
#     "Jenna" acts as the liaison, committing source code to GitHub and directly 
#     beaming heavy assets (images, audio) to the Cloud/CDN using `rclone`.
#
# Design Principles:
#     - Safety First: Enforces mandatory commit messages or tags to prevent 
#       accidental blank pushes. Validates tag uniqueness beforehand.
#     - Separation of Concerns: Source code goes to Git. Large media files 
#       bypass Git entirely and go straight to the CDN via rclone, keeping 
#       the repository lightweight.
#
# Maintenance Notes:
#     - Ensure `RCLONE_REMOTE` is configured properly in `~/.config/rclone/rclone.conf`
#       on the local developer machine.
#     - If the repository structure moves, the `WORKSPACE_DIR` and `ASSETS_ROOT` 
#       relative path logic will need adjustment.
# =============================================================================

# --- JENNA: LOCAL DEVELOPMENT LIAISON ---
# Usage: ./jenna-sync.sh --push -m "Commit Message" [-t "v1.0.0"]

# 1. ESTABLISH PATHS
# Calculate absolute paths dynamically based on the script's execution location
WORKSPACE_DIR=$(cd "$(dirname "$0")" && pwd)
ASSETS_ROOT=$(cd "$WORKSPACE_DIR/.." && pwd)
CMS_ROOT=$(cd "$ASSETS_ROOT/../stardust-engine-cms" && pwd)

# 2. DETECT RCLONE (For CDN Sync)
# Configuration for the rclone binary to handle direct asset transfer
RCLONE_BIN="rclone"
RCLONE_REMOTE="my-cdn-space:assets.example.com"

# 3. ARGUMENT PARSING
ACTION=""
COMMIT_MSG=""
TAG_NAME=""

# Loop through provided arguments to capture flags
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --push) ACTION="push" ;;
        --msg|-m) COMMIT_MSG="$2"; shift ;;
        --tag|-t) TAG_NAME="$2"; shift ;;
        *) echo "👱‍♀️ JENNA: I don't recognize the command '$1'."; exit 1 ;;
    esac
    shift
done

# 4. EXECUTE PUSH
if [ "$ACTION" == "push" ]; then

    # Enforce basic validation constraints
    if [[ -z "$COMMIT_MSG" && -z "$TAG_NAME" ]]; then
        echo "🛑 JENNA: ABORTING! You must provide a commit message (-m) or a tag (-t)."
        exit 1
    fi
    
    # Generate fallback commit message if only a tag is provided
    if [[ -z "$COMMIT_MSG" ]]; then
        COMMIT_MSG="Release $TAG_NAME"
    fi

    # --- PRE-FLIGHT TAG CHECK ---
    # Ensure the requested tag doesn't already exist locally or remotely
    if [ -n "$TAG_NAME" ]; then
        echo "👱‍♀️ JENNA: Validating tag '$TAG_NAME'..."
        cd "$CMS_ROOT" || exit
        if git show-ref --tags "$TAG_NAME" --quiet || git ls-remote --tags origin | grep -q "refs/tags/$TAG_NAME"; then
            echo "🛑 JENNA: ABORTING! The tag '$TAG_NAME' is already used in the repository."
            exit 1
        fi
    fi

    echo "👱‍♀️ JENNA: Packaging the Source Code..."
    cd "$CMS_ROOT" || exit
    
    # Stage all local modifications
    git add .
    
    # Commit and push only if there are actual diffs present
    if ! git diff-index --quiet HEAD --; then
        git commit -m "$COMMIT_MSG"
        git push origin main
        echo "   ✓ CMS code synced to GitHub."
    else
        echo "   (CMS code is clean.)"
    fi

    # --- TAGGING LOGIC ---
    # Apply formal version tag and push to repository
    if [ -n "$TAG_NAME" ]; then
        echo "   > Stamping release with tag: $TAG_NAME..."
        git tag -a "$TAG_NAME" -m "Release $TAG_NAME"
        git push origin "$TAG_NAME"
        echo "   ✓ Tag $TAG_NAME officially stamped and sent to GitHub."
    fi

    echo "👱‍♀️ JENNA: Beaming heavy assets to the CDN..."
    
    # Execute rapid rclone transfer, excluding internal git data
    "$RCLONE_BIN" copy "$ASSETS_ROOT/assets" $RCLONE_REMOTE \
        --exclude "/.git/**" \
        --fast-list
        
    echo "👱‍♀️ JENNA: Success! The production server will pick this up shortly."
fi