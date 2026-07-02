#!/bin/bash
# --- JENNA: LOCAL DEVELOPMENT LIAISON ---
# Usage: ./jenna-sync.sh --push -m "Commit Message" [-t "v1.0.0"]

# 1. ESTABLISH PATHS
WORKSPACE_DIR=$(cd "$(dirname "$0")" && pwd)
ASSETS_ROOT=$(cd "$WORKSPACE_DIR/.." && pwd)
CMS_ROOT=$(cd "$ASSETS_ROOT/../stardust-engine-cms" && pwd)

# 2. DETECT RCLONE (For CDN Sync)
RCLONE_BIN="rclone"
RCLONE_REMOTE="my-cdn-space:assets.example.com"

# 3. ARGUMENT PARSING
ACTION=""
COMMIT_MSG=""
TAG_NAME=""

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

    if [[ -z "$COMMIT_MSG" && -z "$TAG_NAME" ]]; then
        echo "🛑 JENNA: ABORTING! You must provide a commit message (-m) or a tag (-t)."
        exit 1
    fi
    
    # Set fallback message if only tagging
    if [[ -z "$COMMIT_MSG" ]]; then
        COMMIT_MSG="Release $TAG_NAME"
    fi

    # --- PRE-FLIGHT TAG CHECK ---
    if [ -n "$TAG_NAME" ]; then
        echo "👱‍♀️ JENNA: Validating tag '$TAG_NAME'..."
        cd "$CMS_ROOT" || exit
        if git show-ref --tags "$TAG_NAME" --quiet || git ls-remote --tags origin | grep -q "refs/tags/$TAG_NAME"; then
            echo "🛑 JENNA: ABORTING! The tag '$TAG_NAME' is already used in the repository."
            exit 1
        fi
    fi

    echo "👱‍♀️ JENNA: Packaging the Source Code..."
    cd "$CMS_ROOT"
    git add .
    if ! git diff-index --quiet HEAD --; then
        git commit -m "$COMMIT_MSG"
        git push origin main
        echo "   ✓ CMS code synced to GitHub."
    else
        echo "   (CMS code is clean.)"
    fi

    # --- TAGGING LOGIC ---
    if [ -n "$TAG_NAME" ]; then
        echo "   > Stamping release with tag: $TAG_NAME..."
        git tag -a "$TAG_NAME" -m "Release $TAG_NAME"
        git push origin "$TAG_NAME"
        echo "   ✓ Tag $TAG_NAME officially stamped and sent to GitHub."
    fi

    echo "👱‍♀️ JENNA: Beaming heavy assets to the CDN..."
    "$RCLONE_BIN" copy "$ASSETS_ROOT/assets" $RCLONE_REMOTE \
        --exclude "/.git/**" \
        --fast-list
        
    echo "👱‍♀️ JENNA: Success! The production server will pick this up shortly."
fi