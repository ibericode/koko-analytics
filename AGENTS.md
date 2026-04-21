# AGENTS.md

This file contains repo-specific guidance for agents and contributors working in `koko-analytics`.

## Working Rules

- Do not revert unrelated user changes in the worktree.
- Prefer the smallest correct change.
- When changing JavaScript, treat `assets/js/src/*.js` as the source of truth.
- `assets/js/*.js` is generated output and should be rebuilt, not hand-edited.
- After agent-made code changes, run the relevant validation commands before finishing.
- Do not report code work as complete if a required validation step has not been run, or if it is still failing.

## JS Workflow

- Source files live in `assets/js/src/`.
- Built files are written to `assets/js/` by `npm run build`.
- The frontend tracking script is printed inline from `assets/js/script.js` in `src/Script_Loader.php`.
- After changing JS source, run:

```sh
npm run lint
npm run build
```

## PHP Checks

To save time and context, prefer the narrowest useful check first.

- Fast syntax-only pass for all PHP files:

```sh
composer check-syntax
```

- Code style:

```sh
composer check-codestyle
```

- Static analysis:

```sh
composer static-analysis
```

- Full PHPUnit suite:

```sh
composer test
```

- Everything in the usual order:

```sh
composer check-all
```

## Focused Test Commands

Use targeted commands when only one area changed.

```sh
vendor/bin/phpunit tests/FunctionsTest.php
vendor/bin/phpunit tests/StatsTest.php
vendor/bin/phpunit tests/RestTest.php
```

## When To Run What

- JS-only change: `npm run lint && npm run build`
- Small PHP edit: start with `composer check-syntax`
- PHP logic change: usually `composer check-syntax` then `composer static-analysis`
- Behavior change with test coverage nearby: run the relevant `vendor/bin/phpunit tests/...` file
- Broad PHP change or before finishing substantial backend work: `composer check-all`

## Completion Rules

- For any PHP code change, before finishing: run `composer check-syntax` and `composer static-analysis`.
- For any PHP code change with nearby or updated tests, also run the most relevant `vendor/bin/phpunit tests/...` command or `composer test`.
- For any JS code change, before finishing: run `npm run lint` and `npm run build`.
- If a validation command fails, either fix the issue and rerun it, or clearly stop and report the failure.
- Do not rely on a previous successful run from earlier in the conversation after making additional edits; rerun the affected checks.

## Notes

- `bin/check-php-syntax` skips `vendor/` and `node_modules/`, so it is the cheapest broad PHP validation step.
- If built assets are committed, include both the source change and rebuilt output in the final diff.
