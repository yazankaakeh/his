#!/bin/sh
set -e

# Role: app (optional logic based on env vars can go here)

# Exec the container's main process
exec "$@"
