#!/usr/bin/env bash

# Quick Start: Initialize git and push to GitHub
# Usage: ./push-to-github.sh [repo_name] [github_user] [ssh_host_alias] [branch] [tag]
# Example: ./push-to-github.sh secure-media-upload maged-hamed github-personal main v0.1.0

set -euo pipefail

REPO_NAME="${1:-secure-media-upload}"
GITHUB_USER="${2:-maged-hamed}"
SSH_HOST_ALIAS="${3:-github-personal}"
BRANCH="${4:-main}"
TAG="${5:-v0.1.0}"
REMOTE_NAME="origin"
REMOTE_URL="git@${SSH_HOST_ALIAS}:${GITHUB_USER}/${REPO_NAME}.git"

echo "🚀 Preparing GitHub deployment..."
echo "   remote: ${REMOTE_URL}"
echo "   branch: ${BRANCH}"
echo "   tag:    ${TAG}"

# Initialize git if needed.
if [ ! -d .git ]; then
    git init
fi

# Add files.
echo "📦 Staging files..."
git add .

# Commit only when there are staged changes.
if git diff --cached --quiet; then
    echo "ℹ️  No staged changes to commit."
else
    echo "💾 Creating commit..."
    git commit -m "Initial release: core upload validation and storage"
fi

# Add or update remote.
echo "🔗 Configuring GitHub remote..."
if git remote get-url "${REMOTE_NAME}" >/dev/null 2>&1; then
    git remote set-url "${REMOTE_NAME}" "${REMOTE_URL}"
else
    git remote add "${REMOTE_NAME}" "${REMOTE_URL}"
fi

# Set target branch.
echo "🌿 Setting up branch..."
git branch -M "${BRANCH}"

# Push to GitHub.
echo "⬆️  Pushing branch..."
git push -u "${REMOTE_NAME}" "${BRANCH}"

# Create and push tag if needed.
echo "🏷️  Publishing tag..."
if git rev-parse "${TAG}" >/dev/null 2>&1; then
    echo "ℹ️  Tag ${TAG} already exists locally."
else
    git tag -a "${TAG}" -m "Release ${TAG}"
fi
git push "${REMOTE_NAME}" "${TAG}" || true

echo ""
echo "✅ Done! Your package is now on GitHub:"
echo "   https://github.com/${GITHUB_USER}/${REPO_NAME}"
echo ""
echo "📝 Next steps:"
echo "   1. Verify repo files and release tag on GitHub"
echo "   2. (Optional) Publish to Packagist: https://packagist.org/packages/submit"
echo "   3. Create future tags (v0.2.0, v0.3.0, ...)"
