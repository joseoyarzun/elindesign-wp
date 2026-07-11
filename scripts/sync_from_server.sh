#!/usr/bin/env bash
set -euo pipefail

# Usage:
#   ./scripts/sync_from_server.sh user@host:/absolute/path/to/wp /local/path/to/repo
# Example:
#   ./scripts/sync_from_server.sh deploy@example.com:/var/www/html /c/wamp64/www/elindesign

if [[ $# -lt 2 ]]; then
  echo "Usage: $0 <ssh_user@host:/remote_wp_root> <local_repo_path>"
  exit 1
fi

REMOTE_WP_ROOT="$1"
LOCAL_REPO_PATH="$2"
EXCLUDES_FILE="$LOCAL_REPO_PATH/scripts/sync_excludes.txt"

if [[ ! -f "$EXCLUDES_FILE" ]]; then
  echo "Missing excludes file: $EXCLUDES_FILE"
  exit 1
fi

mkdir -p "$LOCAL_REPO_PATH"

echo "[1/2] Syncing server code into local repository..."

if command -v rsync >/dev/null 2>&1; then
  rsync -az --delete \
    --exclude-from="$EXCLUDES_FILE" \
    "$REMOTE_WP_ROOT/" "$LOCAL_REPO_PATH/"
else
  if [[ "$REMOTE_WP_ROOT" != *:* ]]; then
    echo "Remote path must be in the form user@host:/absolute/path"
    exit 1
  fi

  REMOTE_HOST="${REMOTE_WP_ROOT%%:*}"
  REMOTE_PATH="${REMOTE_WP_ROOT#*:}"

  if [[ -n "${RSYNC_RSH:-}" ]]; then
    read -r -a SSH_CMD <<< "$RSYNC_RSH"
  else
    SSH_CMD=(ssh)
  fi

  if [[ "$REMOTE_PATH" == "~" || "$REMOTE_PATH" == ~/* ]]; then
    REMOTE_HOME="$("${SSH_CMD[@]}" "$REMOTE_HOST" 'printf %s "$HOME"')"
    if [[ "$REMOTE_PATH" == "~" ]]; then
      REMOTE_PATH="$REMOTE_HOME"
    else
      REMOTE_PATH="$REMOTE_HOME/${REMOTE_PATH#~/}"
    fi
  fi

  REMOTE_CD_PATH="$(printf '%q' "$REMOTE_PATH")"

  REMOTE_CMD="cd $REMOTE_CD_PATH && tar -cf -"
  while IFS= read -r line; do
    line="${line%$'\r'}"
    [[ -z "$line" || "$line" == \#* ]] && continue
    pattern="$line"
    # rsync-style '***' is not needed for tar excludes.
    pattern="${pattern%/***}"
    REMOTE_CMD+=" $(printf '%q' "--exclude=$pattern")"
  done < "$EXCLUDES_FILE"
  REMOTE_CMD+=" ."

  echo "rsync not found; using ssh+tar fallback (no --delete behavior)."
  "${SSH_CMD[@]}" "$REMOTE_HOST" "$REMOTE_CMD" | tar -xf - -C "$LOCAL_REPO_PATH"
fi

echo "[2/2] Sync complete. Suggested next steps:"
echo "  cd $LOCAL_REPO_PATH"
echo "  git checkout -b prod-sync-$(date +%Y%m%d)"
echo "  git status"
echo "  git add -A && git commit -m 'chore: sync production code snapshot'"
