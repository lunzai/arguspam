#!/bin/bash
set -a
source .env
set +a

if [ -z "$GTM_ID" ]; then
    echo "Error: GTM_ID environment variable not set"
    exit 1
fi

mkdir -p dist
sed -e "s/GTM_ID/$GTM_ID/g" index.html > dist/index.html