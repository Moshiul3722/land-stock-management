#!/bin/bash

# Check if project directory exists
if [ ! -d "/Volumes/MacSSD/AI-Engineering/land-stock-management" ]; then
    echo "Error: /Volumes/MacSSD/AI-Engineering/land-stock-management directory not found."
    echo "Please ensure the directory exists and retry."
    exit 1
fi

# Navigate to project directory
cd /Volumes/MacSSD/AI-Engineering/land-stock-management || exit 1

# Display project info
echo "======================================="
echo "Project Directory:"
pwd
echo "======================================="
echo "Project Files:"
ls -la

echo ""
echo "Composer.json content:"
cat composer.json | grep -A 20 -B 5 laravel/framework

echo ""
echo "Environment file content:"
cat .env | grep -E "(APP_)"