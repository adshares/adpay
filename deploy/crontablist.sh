#!/usr/bin/env bash
set -eu

SERVICE_DIR=${SERVICE_DIR:-$(dirname "$(dirname "$(readlink -f "$0")")")}

echo -n "10-15,*/5 * * * * "
echo -n "php ${SERVICE_DIR}/bin/console ops:payments:calculate"
echo ""
