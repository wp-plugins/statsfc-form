=== StatsFC Form ===
Contributors: willjw
Donate link:
Tags: widget, football, soccer, premier league
Requires at least: 3.3
Tested up to: 4.2.2
Stable tag: 1.7.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This widget will place a current football form guide in your website.

== Description ==

Add a football form guide to your WordPress website. To request a key sign up for your free trial at [statsfc.com](https://statsfc.com/sign-up).

For a demo, check out [wp.statsfc.com/form/](http://wp.statsfc.com/form/).

== Installation ==

1. Upload the `statsfc-form` folder and all files to the `/wp-content/plugins/` directory
2. Activate the widget through the 'Plugins' menu in WordPress
3. Drag the widget to the relevant sidebar on the 'Widgets' page in WordPress
4. Set the StatsFC key and any other options. If you don't have a key, sign up for free at [statsfc.com](https://statsfc.com)

You can also use the `[statsfc-form]` shortcode, with the following options:

- `key` (required): Your StatsFC key
- `competition` (required*): Competition key, e.g., `EPL`
- `date` (optional): For a back-dated form guide, e.g., `2013-12-31`
- `limit` (optional): Number of teams to show form for, e.g., `5`, `10`
- `highlight` (optional): Name of the team you want to highlight, e.g., `Liverpool`
- `show_badges` (optional): Display team badges, `true` or `false`
- `show_score` (optional): Display match scores (on mouse over), `true` or `false`
- `default_css` (optional): Use the default widget styles, `true` or `false`

== Frequently asked questions ==



== Screenshots ==



== Changelog ==

**1.0.1**: Fixed CSS bugs.

**1.0.2**: Fixed possible CSS overlaps.

**1.0.4**: Changed 'Highlight' option from a textbox to a dropdown.

**1.0.5**: Load images from CDN.

**1.0.6**: Allow the form of a single team to be displayed.

**1.0.7**: Fixed a bug when selecting a specific team.

**1.1**: Updated team badges for 2013/14.

**1.1.1**: Fixed a formatting bug where teams don't have 6 results.

**1.1.2**: Use cURL to fetch API data if possible.

**1.1.3**: Fixed possible cURL bug.

**1.1.4**: Added fopen fallback if cURL request fails.

**1.1.5**: More reliable team icons.

**1.1.6**: Tweaked error message.

**1.2**: Updated to use new API.

**1.3**: Added a `date` parameter.

**1.4**: Added `[statsfc-form]` shortcode.

**1.5**: Added a `limit` parameter.

**1.5.2**: Updated team badges.

**1.5.3**: Default `default_css` parameter to `true`

**1.5.4**: Added badge class for each team

**1.5.5**: Use built-in WordPress HTTP API functions

**1.6**: Enabled ad-support

**1.6.1**: Allow more discrete ads for ad-supported accounts

**1.7**: Added `show_badges` and `show_score` options; removed `team` option

**1.7.1**: Fixed bug with multiple widgets on one page

**1.7.2**: Fixed bug with boolean options

== Upgrade notice ==

