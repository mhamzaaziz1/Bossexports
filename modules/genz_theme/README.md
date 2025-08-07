# GenZ Theme Module for Perfex CRM

A modern, vibrant theme with Gen Z aesthetics for Perfex CRM. This theme provides a fresh, interactive user interface with customizable colors, dark mode, and animations.

## Features

- 🎨 Modern UI with vibrant colors and rounded corners
- 🌙 Dark mode support with toggle button
- ✨ Subtle animations and hover effects
- 📱 Fully responsive design
- 🎯 Interactive elements for better user engagement
- 🔄 Customizable accent colors
- 🖌️ Gradient text effects
- 🎭 Custom login page with animated background

## Installation

1. Download the module files
2. Extract the `genz_theme` folder to your Perfex CRM's `modules` directory
3. Go to **Setup → Modules** in your Perfex CRM admin panel
4. Find "GenZ Theme" in the list and click "Activate"
5. Once activated, go to **Setup → Settings → GenZ Theme Settings** to configure the theme

## Configuration

The GenZ Theme module provides several configuration options:

### General Settings

- **Enable theme for staff**: Enable the theme in the admin/staff area
- **Enable theme for customers**: Enable the theme in the client/customer area
- **Enable dark mode by default**: Start with dark mode enabled
- **Enable animations**: Enable subtle animations throughout the interface

### Color Settings

- **Accent Color**: The primary color used for buttons, links, and highlights
- **Secondary Color**: Used for gradients and alternate elements

## Usage

### Dark Mode Toggle

A floating dark mode toggle button is added to the bottom right corner of the screen. Click it to switch between light and dark modes. The preference is saved per user.

### Animations

When animations are enabled, you'll notice:
- Smooth transitions when hovering over elements
- Fade-in effects when loading pages
- Subtle animations for notifications
- Interactive hover effects on cards and buttons

### Gradient Text

Add the class `gradient-text` to any heading to apply a gradient effect using your accent and secondary colors.

Example:
```html
<h1 class="gradient-text">This heading has a gradient effect</h1>
```

### Hover Card Effect

Add the class `hover-card` to any element to give it a lift effect on hover.

Example:
```html
<div class="panel_s hover-card">
    Panel content here
</div>
```

## Customization

### Custom CSS

You can add custom CSS to further customize the theme by editing the following files:

- `modules/genz_theme/assets/css/genz_styles.css` - Main theme styles
- `modules/genz_theme/assets/css/dark_mode.css` - Dark mode styles
- `modules/genz_theme/assets/css/animations.css` - Animation styles
- `modules/genz_theme/assets/css/clients/clients.css` - Client area styles

### Custom JavaScript

You can extend the theme's functionality by editing:

- `modules/genz_theme/assets/js/main.js` - Main JavaScript for admin area
- `modules/genz_theme/assets/js/clients.js` - JavaScript for client area
- `modules/genz_theme/assets/js/login.js` - JavaScript for login page

## Compatibility

- Requires Perfex CRM version 2.3.2 or higher
- Tested with PHP 7.4 and PHP 8.0
- Compatible with all modern browsers (Chrome, Firefox, Safari, Edge)

## Support

If you encounter any issues or have questions about the GenZ Theme module, please contact the developer.

## License

This module is licensed under the MIT License.

## Credits

- Developed by AI Developer
- Inspired by modern web design trends and Gen Z aesthetics
- Uses [GSAP](https://greensock.com/gsap/) for animations
- Uses [Google Fonts](https://fonts.google.com/) for typography