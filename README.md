# Fathom Analytics — TYPO3 Extension

[![TYPO3 11.5–14.0](https://img.shields.io/badge/TYPO3-11.5–14.0-orange.svg)](https://get.typo3.org/)
[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net/)
[![License: GPL-2.0-or-later](https://img.shields.io/badge/License-GPL--2.0--or--later-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Privacy-focused analytics for TYPO3 powered by [Fathom Analytics](https://usefathom.com/). Provides a backend dashboard module, page-specific analytics in the page module, and optional frontend tracking script injection.

## Features

- **Backend Dashboard Module** — View analytics data directly in the TYPO3 backend
- **Page-Specific Analytics** — See per-page metrics in the TYPO3 page module
- **Frontend Tracking** — Optional automatic injection of the Fathom tracking script
- **Dashboard Widgets** — Integration with TYPO3's Dashboard extension for custom widgets
- **Multi-Version Support** — Compatible with TYPO3 11.5, 12.4, 13.4, and 14.0
- **Caching** — Uses TYPO3's Caching Framework for API response caching

## Installation

```bash
composer require moselwal/fathom-analytics
```

### Requirements

- PHP 7.4+
- TYPO3 11.5, 12.4, 13.4, or 14.0
- Fathom Analytics account with API key

## Architecture

```
Classes/
├── Controller/      # Backend module controllers
├── Domain/          # Models and repositories
├── Exception/       # Domain-specific exceptions
├── Middleware/       # Frontend tracking script injection
├── Service/         # Fathom API client and data services
└── Widgets/         # TYPO3 Dashboard widgets
```

## Configuration

1. Obtain an API key from your [Fathom Analytics dashboard](https://app.usefathom.com/)
2. Configure the extension in the TYPO3 backend (Extension Configuration)
3. Set your Site ID for frontend tracking (optional)

### Dashboard Widgets

When `typo3/cms-dashboard` is installed, additional widgets are available:

- Page views over time
- Top pages
- Referrer sources
- Browser/device breakdown

## Development

```bash
composer install
composer test                    # Unit tests
composer phpstan                 # Static analysis
vendor/bin/php-cs-fixer fix      # Code style (PER-CS3x0)
```

## Dependencies

| Package | Type | Purpose |
|---------|------|---------|
| `typo3/cms-dashboard` | Optional | Dashboard widget support |
| `moselwal/dev` | Dev | Shared QA tooling |

## License

GPL-2.0-or-later — see [LICENSE](LICENSE) for details.
