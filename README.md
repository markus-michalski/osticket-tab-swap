# Tab Swap Plugin for osTicket

[🇩🇪 Deutsche Version](./README-de.md)

## Overview

This plugin swaps the order of **Reply** and **Internal Note** tabs in the osTicket ticket detail view, making **Internal Note** the default active tab instead of Reply.

Perfect for teams that primarily use internal notes for collaboration and want to reduce accidental customer-facing replies.

## Use Cases

- **Support teams** that collaborate primarily via internal notes
- **Reduce accidental replies** to customers (Internal Note is default)
- **Faster workflow** for agents who use internal notes more frequently
- **Configurable** - can be toggled on/off in plugin settings

## Key Advantages

- ✅ **No core file modifications** - Uses osTicket's Signal API
- ✅ **Easy to uninstall** - Simply disable the plugin
- ✅ **Auto-update support** - Version tracking built-in
- ✅ **PJAX-compatible** - Works with osTicket's dynamic navigation
- ✅ **Permission-aware** - Handles users with/without reply permissions
- ✅ **Mobile-friendly** - Works with responsive design

## Requirements

- osTicket **1.18.x** (tested on 1.18.1)
- PHP 7.4+ (recommended: PHP 8.1+)
- jQuery (included in osTicket)

## Installation

### Step 1: Install Plugin Files

#### Method 1: ZIP Download

1. Download the latest release ZIP from [Releases](https://github.com/yourusername/osticket-tab-swap/releases)
2. Extract the ZIP file
3. Upload the `tab-swap` folder to `/include/plugins/` on your osTicket server

#### Method 2: Git Repository

```bash
cd /path/to/osticket/include/plugins
git clone https://github.com/yourusername/osticket-tab-swap.git tab-swap
```

### Step 2: Enable Plugin in osTicket

1. Log in to osTicket as **Admin**
2. Navigate to: **Admin Panel → Manage → Plugins**
3. Find "Tab Swap" in the plugin list
4. Click **Enable**

**Automatic installation happens on enable!**

### Step 3: Configuration

1. After enabling, click **Configure** (gear icon)
2. Toggle **Enable Tab Swap**: ON/OFF
3. Click **Save Changes**

## Updating the Plugin

**Automatic update detection included!**

### Method 1: Git Update

```bash
cd /path/to/osticket/include/plugins/tab-swap
git pull origin main
```

### Method 2: ZIP Update

1. Download the latest release
2. Replace the `/include/plugins/tab-swap` folder
3. Keep your existing `config.php` (settings preserved)

**Plugin auto-detects new version on next admin page load!**

## Usage

Once enabled, the plugin automatically swaps tabs in ticket view:

**Before:**

```
[Reply (active)] [Internal Note]
```

**After:**

```
[Internal Note (active)] [Reply]
```

### Behavior

- **Default active tab**: Internal Note
- **Tab order**: Internal Note appears first (left)
- **Form visibility**: Internal Note form is visible, Reply form is hidden
- **PJAX navigation**: Works seamlessly when navigating between tickets

### Edge Cases Handled

| Scenario                     | Behavior                                      |
| ---------------------------- | --------------------------------------------- |
| User has NO reply permission | Only Note tab exists → no swap performed      |
| Ticket is closed             | No forms present → no swap performed          |
| Mobile view                  | Tab swap works (responsive design compatible) |
| PJAX navigation              | Tab swap re-applied on dynamic load           |

## Troubleshooting

### Tab swap doesn't work

**Check:**

1. Plugin is **enabled** in Admin Panel → Plugins
2. Plugin config: "Enable Tab Swap" is **ON**
3. Browser console (F12) for JavaScript errors
4. User has **reply permission** (otherwise no Reply tab exists)

### Tab swap works on first load, but not after PJAX navigation

**Solution:**

- Ensure you're using the latest plugin version
- Check browser console for `[Tab-Swap] PJAX reload detected` message
- If missing, PJAX events may not be firing (osTicket version issue)

### JavaScript errors in console

**Common causes:**

- Old browser (requires ES5+ support)
- jQuery conflict (osTicket 1.18.x includes jQuery 3.x)
- Another plugin modifying the same elements

**Debug:**

```javascript
// In browser console:
$('#post-reply-tab').length  // Should be 1
$('#post-note-tab').length   // Should be 1
```

## Known Limitations

1. **Canned Responses**: osTicket inserts canned responses into the Reply form even when Note tab is active (osTicket core behavior, not fixable by plugin)
2. **Department-specific templates**: Custom templates may have different DOM structure (plugin may not work)
3. **Third-party themes**: Non-standard themes may break plugin functionality

## 📄 License

This Plugin is released under the GNU General Public License v2, compatible with osTicket core.

See [LICENSE](./LICENSE) for details.

## 💬 Support

For questions or issues, please create an issue on GitHub:
https://github.com/markus-michalski/osticket-plugins/issues

## 🤝 Contributing

Developed by [Markus Michalski](https://github.com/markus-michalski)

Inspired by the osTicket community's need for better internal collaboration workflows.

## ☕ Support Development

This plugin is completely free and open source. If it saves you time or makes your work easier, I'd appreciate a small donation to keep me caffeinated while developing and maintaining this plugin!

[![Donate](https://img.shields.io/badge/Donate-PayPal-blue.svg)](https://paypal.me/tondiar)

Your support helps me continue improving this and other osTicket plugins. Thank you! 🙏

## Changelog

See [CHANGELOG.md](./CHANGELOG.md) for version history.
