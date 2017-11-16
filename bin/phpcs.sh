#!/usr/bin/env bash

vendor/bin/phpcs --config-set installed_paths $(pwd)/vendor/wp-coding-standards/wpcs
vendor/bin/phpcs --standard=phpcs.ruleset.xml $(find ./src -name '*.php')
