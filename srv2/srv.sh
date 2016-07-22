#/bin/bash

HOME=${HOME-/home5/devninni}

export TZ=US/Pacific
export PATH=$PATH:${HOME}/bin/node/bin
export NODE_PATH="$(npm root -g)"

CURDIR=${HOME}/public_html/af/srv2
cd $CURDIR

echo "Lock $(date)"
/usr/bin/flock -x -n "${CURDIR}/srv.lock" node "${CURDIR}/srv.js"
#node srv.js
echo "Unlock $(date)"
