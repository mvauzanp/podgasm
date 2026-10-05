# Antigravity Workspace Guidelines (Penjualan Podgasm)

This workspace is a high-performance e-commerce and POS synchronization system. Follow these engineering constraints strictly:

1. **Tailwind CSS v4 Standard**:
   - Reference: `.agents/rules/tailwind-v4.md`
   - Strict CSS-first setup via `@tailwindcss/vite` and `resources/css/app.css`. Never generate `tailwind.config.js`.

2. **Defensive Laravel 13 Standards**:
   - Reference: `.agents/rules/laravel-13-defensive.md`
   - Enforce database transaction locks (`lockForUpdate`) on cart/checkout to prevent race conditions.
   - Enforce strict signature validation and idempotent processing on Midtrans callbacks and Olsera webhooks.

3. **Anti-Slop Doctrine**:
   - Reference: `.agents/rules/anti-slop.md`
   - Zero boilerplate, zero redundant comments, zero premature architectural layers.
