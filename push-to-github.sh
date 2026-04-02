#!/bin/bash

# Quick Start: Initialize git and push to GitHub
# Usage: ./push-to-github.sh

set -e

REPO_NAME="${1:-secure-media-upload}"
GITHUB_USER="${2:-maged}"

echo "🚀 Initializing Git repository..."

# Initialize git if needed
if [ ! -d .git ]; then
    git init
    git config user.name "Your Name"
    git config user.email "your-email@example.com"
fi

# Add files
echo "📦 Staging files..."
git add .

# Initial commit
echo "💾 Creating initial commit..."
git commit -m "Initial release: core upload validation and storage" || echo "Already committed"

# Add remote
echo "🔗 Adding GitHub remote..."
git remote remove origin 2>/dev/null || true
git remote add origin "git@github.com:${GITHUB_USER}/${REPO_NAME}.git"

# Set main branch
echo "🌿 Setting up main branch..."
git branch -M main

# Push to GitHub
echo "⬆️  Pushing to GitHub..."
git push -u origin main

# Create and push tag
echo "🏷️  Creating release tag..."
git tag -a v0.1.0 -m "Initial release with core upload functionality" 2>/dev/null || true
git push origin v0.1.0 2>/dev/null || true

echo ""
echo "✅ Done! Your package is now on GitHub:"
echo "   https://github.com/${GITHUB_USER}/${REPO_NAME}"
echo ""
echo "📝 Next steps:"
echo "   1. Update CONTRIBUTING.md with your contact info"
echo "   2. (Optional) Publish to Packagist: https://packagist.org/packages/submit"
echo "   3. Create releases via GitHub UI for future versions"

