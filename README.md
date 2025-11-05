# Tab Swap Plugin

[![CI](https://github.com/markus-michalski/osticket-tab-swap/workflows/CI/badge.svg)](https://github.com/markus-michalski/osticket-tab-swap/actions)
[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](./LICENSE)

📚 **[Full Documentation & FAQ (English)](https://faq.markus-michalski.net/en/osticket/tab-swap)**

📚 **[Komplette Dokumentation & FAQ (Deutsch)](https://faq.markus-michalski.net/de/osticket/tab-swap)**

- Complete guide with troubleshooting, best practices, and advanced usage

## Overview

The **Tab Swap Plugin** swaps the order of **Reply** and **Internal Note** tabs in the osTicket ticket detail view, making **Internal Note** the default active tab instead of Reply.

Perfect for teams that primarily use internal notes for collaboration and want to reduce accidental customer-facing replies.

## Key Features

- ✅ **No core file modifications** - Uses osTicket's Signal API
- ✅ **Easy to uninstall** - Simply disable the plugin
- ✅ **Auto-update support** - Version tracking built-in
- ✅ **PJAX-compatible** - Works with osTicket's dynamic navigation
- ✅ **Permission-aware** - Handles users with/without reply permissions

## Requirements

- osTicket **1.18.x**
- PHP **7.4+** (recommended: PHP 8.1+)
- jQuery (included in osTicket)

## Quick Start

### 1. Install Plugin

```bash
# Method 1: ZIP Download
# Download from: https://github.com/markus-michalski/osticket-tab-swap/releases
unzip tab-swap-vX.X.X.zip
cp -r tab-swap /path/to/osticket/include/plugins/

# Method 2: Git Clone
cd /path/to/osticket/include/plugins
git clone https://github.com/markus-michalski/osticket-tab-swap.git tab-swap
```

### 2. Enable Plugin

1. **Admin Panel** → **Manage** → **Plugins**
2. Find "Tab Swap"
3. Click **Enable**

The plugin automatically installs and updates `.htaccess`.

### 3. Configure

1. Click **Configure** (gear icon)
2. Toggle **"Enable Tab Swap"**: **ON**
3. **Save Changes**

## Use Cases

- **Support teams** that collaborate primarily via internal notes
- **Reduce accidental replies** to customers (Internal Note is default)
- **Faster workflow** for agents who use internal notes frequently

## Documentation

📚 **[Full Documentation & FAQ (English)](https://faq.markus-michalski.net/en/osticket/tab-swap)**

📚 **[Komplette Dokumentation & FAQ (Deutsch)](https://faq.markus-michalski.net/de/osticket/tab-swap)**

The FAQ includes:

- Detailed installation instructions
- Troubleshooting guide (PJAX issues, JavaScript errors, .htaccess problems)
- Edge case handling
- Known limitations
- Update procedures
- Security considerations
- Best practices

## 📄 License

This Plugin is released under the GNU General Public License v2, compatible with osTicket core.

See [LICENSE](./LICENSE) for details.

## 💬 Support

For questions or issues, please create an issue on GitHub:

[Github Issues](https://github.com/markus-michalski/osticket-tab-swap/issues)

Or check the [FAQ](https://faq.markus-michalski.net/en/osticket/tab-swap) for common questions.

## ☕ Support Development

This plugin is completely free and open source. If it saves you time or makes your work easier, I'd appreciate a small donation to keep me caffeinated while developing and maintaining this plugin!

[![Donate](https://img.shields.io/badge/Donate-PayPal-blue.svg)](https://paypal.me/tondiar)

Your support helps me continue improving this and other osTicket plugins. Thank you! 🙏

## Changelog

See [CHANGELOG.md](./CHANGELOG.md) for version history.
