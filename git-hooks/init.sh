#!/bin/sh

# Check if the CI environment variable is set (usually set by CI/CD platforms)
if [ "$CI" != "true" ]; then
  # Only run the script if we are not in a CI/CD environment
  cp git-hooks/pre-commit .git/hooks/pre-commit
else
  echo "Skipping pre-commit hook installation in CI/CD environment."
fi
