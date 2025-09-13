# CI/CD Workflows

This directory contains GitHub Actions workflows for automated testing, code quality checks, security audits, and releases.

## Workflows

### 1. CI (`ci.yml`)
Runs on every push and pull request to main/develop branches.

**Jobs:**
- **Test**: Runs PHPUnit tests across PHP 8.2 and 8.3
- **Code Quality**: Runs Laravel Pint (code style) and Rector (code quality)

**Features:**
- Composer dependency caching
- Code coverage reporting to Codecov
- Matrix testing across PHP versions

### 2. Release (`release.yml`)
Triggers on version tags (v*).

**Jobs:**
- **Release**: Creates GitHub releases and runs final tests
- **Packagist**: Updates Packagist repository automatically

**Requirements:**
- `PACKAGIST_USERNAME` secret
- `PACKAGIST_TOKEN` secret

### 3. Security (`security.yml`)
Runs weekly and on main branch changes.

**Jobs:**
- **Security Audit**: Runs `composer audit` and Symfony security checker
- **Dependency Review**: Reviews dependencies in pull requests

### 4. Documentation (`docs.yml`)
Deploys documentation to GitHub Pages on main branch changes.

**Features:**
- Automatic API documentation generation
- GitHub Pages deployment
- Custom domain support

## Setup Requirements

### Secrets
Add these secrets to your GitHub repository:

1. `PACKAGIST_USERNAME` - Your Packagist username
2. `PACKAGIST_TOKEN` - Your Packagist API token
3. `CODECOV_TOKEN` - Your Codecov token (optional)

### Branch Protection
Configure branch protection rules for `main`:

1. Require status checks to pass
2. Require branches to be up to date
3. Require review from code owners
4. Restrict pushes to matching branches

### Dependabot
Dependabot is configured to:
- Update Composer dependencies weekly
- Update GitHub Actions weekly
- Create PRs with proper labels and assignees

## Local Development

Before pushing, ensure your code passes all checks:

```bash
# Run tests
vendor/bin/phpunit

# Check code style
vendor/bin/pint --test

# Run static analysis
vendor/bin/rector --dry-run

# Security audit
composer audit
```

## Deployment Process

1. **Development**: Work on feature branches
2. **Testing**: Create PR to `develop` branch
3. **Release**: Merge to `main` and create version tag
4. **Publish**: Automatic release and Packagist update