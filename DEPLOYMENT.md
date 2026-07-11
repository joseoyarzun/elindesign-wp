WordPress GitHub workflow (local -> GitHub -> production)

Goal
- Keep production stable.
- Develop and test locally.
- Deploy only controlled code from GitHub.

What this repository should control
- wp-content/themes/
- wp-content/mu-plugins/
- custom plugins (for this project):
  - wp-content/plugins/woocommerce-scancoordesign/
  - wp-content/plugins/woocommerce-sixwebsoft/
  - wp-content/plugins/product-categories-bottom-description-woo-comerce/

What should stay out of Git
- wp-config.php and secrets.
- uploads, cache, backups, logs.
- database dumps.

1) First sync from server (true code snapshot)
- Run from your local machine:
  bash scripts/sync_from_server.sh deploy@your-host:/absolute/path/to/wordpress /c/wamp64/www/elindesign

- Then create a branch and commit:
  git checkout -b prod-sync-YYYYMMDD
  git add -A
  git commit -m "chore: sync production code snapshot"
  git push -u origin prod-sync-YYYYMMDD

2) Test locally
- Import production database to local DB.
- Update local site URLs if needed.
- Validate key flows (home, checkout, product configurator, forms).

3) Configure GitHub Secrets for deploy
- DEPLOY_HOST: server hostname or IP
- DEPLOY_PORT: ssh port (optional, defaults to 22)
- DEPLOY_USER: ssh user
- DEPLOY_PATH: absolute path to WP root on server
- DEPLOY_SSH_KEY: private key for DEPLOY_USER

4) Deploy from GitHub Actions
- Open Actions -> Deploy WP Code (Manual)
- First run with dry_run=true and review output.
- Then run with dry_run=false.

Notes
- This workflow deploys only selected code paths, not the entire WordPress install.
- Database changes are not deployed by this workflow.
- If you add a new custom plugin, include its path in .github/workflows/deploy-manual.yml.
