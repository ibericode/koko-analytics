# AGENTS.md

This file contains repo-specific guidance for agents and contributors working in `koko-analytics`.

## Working Rules

- Do not revert unrelated user changes in the worktree.
- Prefer the smallest correct change.
- When changing JavaScript, treat `assets/js/src/*.js` as the source of truth.
- `assets/js/*.js` is generated output and should be rebuilt, not hand-edited.

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

## Notes

- `bin/check-php-syntax` skips `vendor/` and `node_modules/`, so it is the cheapest broad PHP validation step.
- If built assets are committed, include both the source change and rebuilt output in the final diff.
