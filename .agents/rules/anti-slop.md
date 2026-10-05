# Anti-Slop & Clean Code Doctrine (Podgasm Workspace)

## Philosophy
Code is written for maintainability, clarity, and performance. AI fluff, boilerplate clutter, and redundant explanations degrade repository quality.

## Banned Patterns (Zero Tolerance)
1. **Redundant & Obvious Comments**:
   - BANNED:
     ```php
     // Menghitung total harga
     $total = $quantity * $price;
     
     // Loop setiap order item
     foreach ($items as $item) {
     ```
   - ALLOWED: Comments explaining *WHY* non-intuitive logic exists (e.g., specific edge case workarounds or external API idiosyncrasies).

2. **Premature Abstractions & "Clean Code" Over-engineering**:
   - Do NOT create single-use Interfaces, Repository classes, or DTO layers for standard Eloquent CRUD that Laravel handles natively.
   - Keep business logic in dedicated Services (`OrderService`, `OlseraApiService`) or Actions when multiple steps are involved.

3. **Silent Failures & Lazy Fallbacks**:
   - Never catch exceptions with empty catch blocks:
     ```php
     // BANNED:
     try { ... } catch (\Exception $e) {}
     ```
   - Always log with context (`Log::error(...)`) or rethrow appropriate domain exceptions.

4. **Polite AI Filler in Explanations**:
   - Keep chat responses concise, technical, and directly focused on code diffs, architecture tradeoffs, and edge cases. Avoid verbose introductions and repetitive disclaimers.
