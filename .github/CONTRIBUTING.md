# CONTRIBUTING

Contributions are welcome, and are accepted via pull requests.
Please review these guidelines before submitting any pull requests.

## Guidelines

* Ensure that your code follows the package coding standard by running `composer lint`.
* Ensure static analysis pass by running `composer analyse`.
* Ensure tests pass by running `composer test`.
* Send a coherent commit history, making sure each individual commit in your pull request is meaningful, make sure commit messages follow [conventional commits](https://conventionalcommits.org).
* You may need to [rebase](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) to avoid merge conflicts.


## Process

1. Fork the package
2. Create a new branch
3. Code, test, analyse, lint, commit and push
4. Open a pull request detailing your changes. Make sure to follow the [template](.github/PULL_REQUEST_TEMPLATE.md)

## Setup

Clone your fork and then install the dependencies:
```bash
composer install
```

## Lint

Lint your code:
```bash
composer lint
```

Lint and fix your code:
```bash
composer lint-fix
```

## Static analysis

Analyse your code:
```bash
composer analyse
```

## Tests

Test your code:
```bash
composer test
```
