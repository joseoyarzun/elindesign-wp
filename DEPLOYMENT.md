WordPress full mirror workflow (local <-> GitHub <-> production)

Goal
- Keep main as the production mirror.
- Test locally with a realistic full copy of production code.
- Deploy the full mirror from GitHub manually.

Repository model
- Track full WordPress code in Git (core, plugins, themes).
- Keep secrets/runtime out of deploy using .github/deploy-excludes.txt.
- Keep wp-config.php out of Git.

1) Sync full mirror from server to local
- Run from local machine:
  bash scripts/sync_from_server.sh deploy@your-host:/absolute/path/to/wordpress /c/wamp64/www/elindesign

- Notes:
  - This sync includes uploads for realistic local testing.
  - If rsync is unavailable, script uses ssh+tar fallback (no delete). In that case, remove stale local leftovers manually when needed.

2) Make local testable
- Import production DB to local DB.
- Set wp_options home/siteurl to local URL.
- Activate plugins in dependency-safe order if needed.

3) Update main as production mirror
- Ensure you are on main:
  git checkout main
- Commit mirror snapshot:
  git add -A
  git commit -m "chore: mirror production snapshot"
  git push origin main

4) Configure GitHub Secrets
- Production target secrets:
  - DEPLOY_HOST
  - DEPLOY_PORT (optional, defaults to 22)
  - DEPLOY_USER
  - DEPLOY_PATH
  - DEPLOY_SSH_KEY
- Development target secrets (for dev.elindesign.se):
  - DEPLOY_DEV_HOST
  - DEPLOY_DEV_PORT (optional, defaults to 22)
  - DEPLOY_DEV_USER
  - DEPLOY_DEV_PATH
  - DEPLOY_DEV_SSH_KEY

5) Deploy full mirror from GitHub Actions
- Open Actions -> Deploy WP Code (Manual)
- For development deploys (dev.elindesign.se):
  - target=dev
  - ref=your-branch-or-main
  - dry_run=true (preview)
  - then dry_run=false
- For production deploys:
  - target=prod
  - ref=main (enforced)
  - dry_run=true (preview)
  - then dry_run=false

Rollback
- Create a tag before risky changes:
  git tag pre-upgrade-YYYYMMDD
  git push origin pre-upgrade-YYYYMMDD
- To rollback, run workflow with ref=pre-upgrade-YYYYMMDD.

Important
- Production deploys are restricted to ref=main.
- Development deploys can use any branch ref.
- Deploy excludes are managed in .github/deploy-excludes.txt.
- DB changes are not handled by this workflow.
