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
- DEPLOY_HOST: server hostname or IP
- DEPLOY_PORT: ssh port (optional, defaults to 22)
- DEPLOY_USER: ssh user
- DEPLOY_PATH: absolute path to WP root on server
- DEPLOY_SSH_KEY: private key for DEPLOY_USER

5) Deploy full mirror from GitHub Actions
- Open Actions -> Deploy WP Code (Manual)
- Run with:
  - ref=main
  - dry_run=true (preview)
- If output is correct, run again with:
  - ref=main
  - dry_run=false

Rollback
- Create a tag before risky changes:
  git tag pre-upgrade-YYYYMMDD
  git push origin pre-upgrade-YYYYMMDD
- To rollback, run workflow with ref=pre-upgrade-YYYYMMDD.

Important
- Workflow is restricted to branch main.
- Deploy excludes are managed in .github/deploy-excludes.txt.
- DB changes are not handled by this workflow.
