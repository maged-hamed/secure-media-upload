# Contributing

Thanks for considering a contribution! Please follow these guidelines:

## Setup

```bash
git clone git@github.com:maged-hamed/secure-media-upload.git
cd secure-media-upload
composer install
```

## Testing

```bash
composer test
```

Or with coverage:

```bash
./vendor/bin/phpunit --coverage-html coverage/
```

## Code Style

We use Laravel Pint for formatting:

```bash
composer require --dev laravel/pint
./vendor/bin/pint
```

## Static Analysis

We use PHPStan:

```bash
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse src
```

## Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/my-feature`)
3. Commit your changes (`git commit -am 'Add my feature'`)
4. Push to the branch (`git push origin feature/my-feature`)
5. Submit a Pull Request

All PRs must:
- Have passing tests
- Pass code style checks
- Include documentation updates
- Have a clear commit message

## Issues

Please use GitHub Issues to report bugs or request features.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

