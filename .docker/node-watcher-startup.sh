#!/bin/bash
set -e

npm install || exit $?
npm run watch || exit $?
