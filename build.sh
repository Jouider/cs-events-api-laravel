#!/bin/bash

# Ensure the init.sh script is executable
chmod +x ./git-hooks/init.sh

# Run Composer install
composer install --ignore-platform-reqs
