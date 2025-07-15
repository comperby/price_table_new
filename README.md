# WP Price Manager

WP Price Manager is a WordPress plugin for managing service prices and displaying them with an Elementor widget. It lets you create categories of services, associate them with price groups, and edit everything in a user friendly interface.

## Installation

1. Copy the `WP Price Manager` folder to your `wp-content/plugins` directory.
2. Activate **WP Price Manager** in the WordPress admin panel.
3. Make sure Elementor 3.5+ is installed and active to use the widget.

## Usage

After activation new menu items appear under **Price Manager**:

- **Категории** – create and sort service categories with drag-and-drop.
- **Все услуги** – add and search services. The form provides autocomplete for categories and price groups.
- **Группа цен** – manage price groups. Editing a group prompts for confirmation before bulk price updates.
- **Стиль** – configure colors, fonts and other appearance options for the front-end price table.

### Elementor Widget

In the Elementor editor search for **Price List**. Drop the widget onto the page and select a service category in the **Content** tab. Style the table in the **Style** tab (colors, borders, typography, link color, alignment, etc.). Each row displays an info icon; hover or tap it to view the service description. The default font is **Montserrat**.

The admin interface loads `js/admin.js` and exposes AJAX parameters via `wppm_ajax_obj` for all asynchronous operations.

Price tables initially show only a limited number of rows. The **Стиль** page lets you configure the "Show more" button (size, font and animation speed) and fine tune table borders just like in Google Sheets.
You can also set the text shown after expanding (default "Свернуть") and choose the button alignment.
