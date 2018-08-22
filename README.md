# A Wordpress Plugin for displaying Member Forms

## Setup

- Install and activate the plugin
- Create a page and add the following shortcodes to them:

| Page | Shortcode | Description |
| --- | --- | --- |
| Register | `[member-register]` | Shows a registration form when not logged in or an 'update my account' when the user is logged in |
| Login/Update Account | `[member-dashboard]` | Shows a login form when not logged in or an 'update my account' when the user is logged in |

## Templating

Don't like the look of the forms? You're able to override the templates in your own theme, by adding your own views within your theme.

_*We recommend copying the source template so that you have the correct fields for the forms_

| Place the new template | Source | Description |
| --- | --- | --- |
| `<YOUR THEME>/member-frontend/login.php` | `<PLUGIN>/resources/views/login.php` | The login form |
| `<YOUR THEME>/member-frontend/register.php` | `<PLUGIN>/resources/views/register.php` | The sign up form |
| `<YOUR THEME>/member-frontend/update-profile.php` | `<PLUGIN>/resources/views/update-profile.php` | The update profile form |
