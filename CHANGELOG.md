# Changelog

All notable changes to `laravel-data-migrations` will be documented in this file.

## 1.0.0 - 2026-09-30

- Load data migrations from a configurable folder alongside schema migrations.
- Skip loading data migrations when a published config omits `run_with_migrate`.
- Add the `make:data-migration` command with a publishable stub.
