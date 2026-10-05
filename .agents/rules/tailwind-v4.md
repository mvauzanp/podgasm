# Tailwind CSS v4 Engineering Rules (Podgasm Workspace)

## Core Architecture
- **Framework Version**: Tailwind CSS v4 with `@tailwindcss/vite`.
- **CSS-First Configuration**: The primary stylesheet is located at `resources/css/app.css`.
- **STRICT PROHIBITION**: Do NOT create `tailwind.config.js` or `postcss.config.js`. Tailwind v4 has eliminated configuration files in favor of CSS directives.

## Syntax & Ground Truth Rules
1. **Import Syntax**:
   - Always use `@import 'tailwindcss';` at the very top of `resources/css/app.css`.
   - Do NOT use `@tailwind base;`, `@tailwind components;`, or `@tailwind utilities;` (v3 legacy syntax).

2. **Source Detection**:
   - Sources are declared directly in CSS using `@source` directives:
     ```css
     @source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';
     @source '../../storage/framework/views/*.php';
     @source '../**/*.blade.php';
     @source '../**/*.js';
     ```

3. **Theme Customization via `@theme`**:
   - Define custom fonts, colors, breakpoints, and design tokens inside the `@theme` block:
     ```css
     @theme {
       --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
       --color-brand-primary: #ff4d00;
       --color-brand-dark: #0f172a;
     }
     ```
   - Reference tokens in utility classes automatically (e.g., `bg-brand-primary`, `font-sans`).

4. **Component Best Practices in Blade**:
   - Create modular Blade components in `resources/views/components/`.
   - Use dynamic attributes with `@props(['variant' => 'default'])` and `$attributes->class([...])`.
   - Never inject raw inline styles when Tailwind utilities are available.
