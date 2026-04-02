# GitHub Release & Installation Instructions

## Step 1: Initialize GitHub Repository

1. Go to https://github.com/new
2. Create a new repository named `secure-media-upload`
3. Make it **private** or **public** (recommended: public for open-source)
4. **Do NOT initialize** with README/gitignore/.gitattributes (we already have them)

## Step 2: Push to GitHub via SSH

In the package directory (`/Users/maged/Projects/image-package/packages/secure-media-upload`):

```bash
# Initialize git if not already done
git init

# Add all files
git add .

# Initial commit
git commit -m "Initial release: core upload validation and storage"

# Add remote (replace 'maged' with your GitHub username if different)
git remote add origin git@github.com:maged/secure-media-upload.git

# Push to GitHub
git branch -M main
git push -u origin main
```

## Step 3: Create a Release Tag

```bash
# Tag the release
git tag -a v0.1.0 -m "Initial release with core upload functionality"

# Push tags to GitHub
git push origin v0.1.0
```

## Step 4: Install in Another Laravel Project

In any Laravel app's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:maged/secure-media-upload.git"
    }
  ],
  "require": {
    "maged/secure-media-upload": "^0.1"
  }
}
```

Then:

```bash
composer install
php artisan vendor:publish --tag=secure-media-upload-config
```

## Step 5: Publish to Packagist (Optional)

1. Sign up at https://packagist.org
2. Click "Submit Package"
3. Enter: `https://github.com/maged/secure-media-upload`
4. Click "Check"
5. You'll automatically get GitHub webhooks set up for new releases

After that, users can install via:

```bash
composer require maged/secure-media-upload
```

## SSH Key Setup (if not already done)

If you haven't set up SSH for GitHub:

```bash
# Generate SSH key (use your GitHub email)
ssh-keygen -t ed25519 -C "your-email@example.com"

# Add to SSH agent
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_ed25519

# Copy public key
cat ~/.ssh/id_ed25519.pub | pbcopy

# Go to https://github.com/settings/keys and add the key
```

Test the connection:

```bash
ssh -T git@github.com
```

You should see: "Hi maged! You've successfully authenticated..."

## Future Releases

```bash
# Make changes, commit
git commit -am "Feature: add chunked upload support"

# Tag new version
git tag -a v0.2.0 -m "Add multipart upload module"

# Push everything
git push origin main --tags
```

GitHub automatically creates a release page for each tag. You can add release notes via the GitHub UI.

