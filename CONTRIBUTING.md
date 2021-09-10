# Contributing

Contributions are **welcome** and will be fully **credited**.

Please read and understand the contribution guide before creating an issue or pull request.

## Etiquette

Be kind.

## Viability

When requesting or submitting new features, first consider whether it might be useful to others. Open source projects are used by many developers, who may have entirely different needs to your own. Think about whether or not your feature is likely to be used by other users of the project.

## Procedure

Before filing an issue:

- Attempt to replicate the problem, to ensure that it wasn't a coincidental incident.
- Check to make sure your feature suggestion isn't already present within the project.
- Check the pull requests tab to ensure that the bug doesn't have a fix in progress.
- Check the pull requests tab to ensure that the feature isn't already in progress.

Before submitting a pull request:

- Check the codebase to ensure that your feature doesn't already exist.
- Check the pull requests to ensure that another person hasn't already submitted the feature or fix.
- Run `./vendor/bin/phpunit` and `./vendor/bin/duster`

## Local Setup

- Run `composer install`
- Run `cp .env.example .env` in the terminal
- Generate a [Github Personal Access token](https://github.com/settings/tokens) and store it as the `GITHUB_TOKEN` value in `.env`
- Create a database called `php_versions`
- Run `php artisan migrate --seed`

### Testing

- Run `cp .env.testing.example .env.testing` in the terminal
- Run `vendor/bin/phpunit` in the terminal

## Requirements

If the project maintainer has any additional requirements, you will find them listed here.

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** - The easiest way to apply the conventions is to install [PHP Code Sniffer](https://github.com/squizlabs/PHP_CodeSniffer).

- **One pull request per feature** - If you want to do more than one thing, send multiple pull requests.
