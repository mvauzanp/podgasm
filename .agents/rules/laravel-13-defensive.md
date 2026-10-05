# Laravel 13 & Defensive Engineering Rules (Podgasm Workspace)

## Core Architecture
- **PHP Version**: PHP 8.3+ with strict typing enabled (`declare(strict_types=1);` where appropriate).
- **Framework Version**: Laravel 13.x monolit.
- **Critical Domains**: E-commerce Checkout, Midtrans Payment Webhook, Olsera POS Sync Job.

## Defensive Programming Standards
1. **Concurrency & Race Conditions in Cart & Checkout**:
   - Stock deduction and order state changes MUST occur within atomic database transactions:
     ```php
     DB::transaction(function () use ($orderData) {
         // Lock product/variant row during stock deduction
         $variant = ProductVariant::where('id', $variantId)->lockForUpdate()->firstOrFail();
         if ($variant->stok < $requestedQty) {
             throw new InsufficientStockException("Stok {$variant->nama} tidak mencukupi.");
         }
         $variant->decrement('stok', $requestedQty);
         // Process order...
     });
     ```
   - Never perform stock updates or payment approvals outside a transaction.

2. **Strict Webhook & Signature Verification**:
   - For Midtrans callbacks and Olsera webhooks:
     - Always verify payload checksum/signature (e.g., SHA512 of `order_id + status_code + gross_amount + server_key`).
     - Webhook jobs MUST be idempotent. Check if the order has already been marked as paid before dispatching notifications or stock sync.
     - Never trust status codes sent from client-side redirects; only trust server-to-server webhook notifications.

3. **Database & Memory Optimization**:
   - Prevent N+1 queries: ALWAYS eager load relationships in controllers (`Order::with(['items.variant.product', 'user'])`).
   - Limit memory consumption on batch operations: Use `chunkById()` or `lazy()` when synchronizing large catalogs from Olsera API, never `get()` on thousands of records.
   - Specify required columns in `select()` when reading large tables.

4. **Exception Handling**:
   - Wrap external API calls (Olsera, Midtrans, Resend) in explicit `try-catch` blocks.
   - Always log errors with contextual details (order_id, user_id, response payload).
