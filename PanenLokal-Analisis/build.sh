#!/bin/sh
set -eu
rm -rf dist
mkdir -p dist
cp index.html styles.css app.js dist/
cp -R public/. dist/
