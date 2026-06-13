# Perbaikan #4 - Order Creation Security (PEMBUATAN PESANAN TIDAK AMAN)
**Status**: ✅ COMPLETED - 14 Mei 2026

## Overview
Implementasi secure order creation workflow dengan payment-deferred stock decrement pattern. Mencegah overselling dan financial loss dengan memisahkan order creation dari stock decrement.

---

## Problem Analysis

### Workflow Lama (❌ Tidak Aman)
```
1. User membuat order (processCheckout)
   ↓
2. Order dibuat + Stock LANGSUNG dikurangi
   ↓
3. Payment diproses (async/manual)
   ↓
4. Payment GAGAL atau timeout
   ↓
5. MASALAH: Stok sudah berkurang, tapi tidak ada uang masuk! 💥
```

**Risiko**:
- Overselling: Stok negatif jika banyak pembayaran gagal
- Financial Loss: Uang tidak masuk tapi barang sudah disimpan
- Data Inconsistency: Stock tidak sesuai dengan pembayaran yang diterima

### Workflow Baru (✅ Aman)
```
1. User membuat order + Validasi stok & harga
   ↓
2. Order dibuat (status='pending_payment') → Stok AMAN
   ↓
3. User redirect ke halaman pembayaran
   ↓
4a. Payment BERHASIL → confirmPayment()
    - Stok dikurangi
    - Status berubah → 'paid'
    - OrderItem disimpan
    ✅ SAFE: Stok hanya berkurang untuk payment yang confirmed

4b. Payment GAGAL/TIMEOUT
    - Order tetap 'pending_payment'
    - Stok TIDAK berkurang
    - ✅ SAFE: Stok tetap aman, user bisa retry atau cancel
```

---

## Implementation Details

### 1. CartController.processCheckout() - MODIFIED

**Changes**:
```php
// SEBELUM: Status 'pending', stock immediately decremented
'status' => 'pending'
// ...stock decrement logic here

// SESUDAH: Status 'pending_payment', NO stock decrement
'status' => 'pending_payment'
// ...NO stock decrement here (akan dilakukan di confirmPayment)
```

**Key Points**:
- ✅ Still validates stock & price before creating order
- ✅ Still uses transaction for data consistency
- ✅ NO stock decrement at this stage
- ✅ Stock decrement deferred to confirmPayment()
- ✅ Clear comment explaining the workflow

**Flow**:
```
Validation → Create Order (pending_payment) → Clear Cart → Commit → Redirect
```

---

### 2. Order Model - NEW METHODS

#### confirmPayment() - Payment Confirmation with Stock Decrement
```php
public function confirmPayment()
{
    // 1. Validasi status masih pending_payment
    if ($this->status !== 'pending_payment') {
        return false;
    }

    try {
        DB::beginTransaction();
        
        // 2. Update status ke 'paid'
        $this->update(['status' => 'paid']);
        
        // 3. Decrement stok untuk setiap item
        foreach ($this->items as $orderItem) {
            // Safety check terakhir
            if ($product->stok_aktual < $orderItem->quantity) {
                DB::rollBack();
                return false;
            }
            // Decrement
            $product->decrement('stok_aktual', $orderItem->quantity);
        }
        
        DB::commit();
        return true;
        
    } catch (\Exception $e) {
        DB::rollBack();
        return false;
    }
}
```

**Benefits**:
- ✅ Only decrements for confirmed payments
- ✅ Atomic transaction (all or nothing)
- ✅ Final safety check sebelum decrement
- ✅ Rollback jika ada error

---

#### cancelOrder() - Cancel Pending Payment
```php
public function cancelOrder()
{
    // Hanya bisa batalkan order yang menunggu pembayaran
    if ($this->status !== 'pending_payment') {
        return false;
    }
    
    // Update status ke 'cancelled'
    // Stock TIDAK berubah (belum dikurangi dari awal)
    $this->update(['status' => 'cancelled']);
    return true;
}
```

**Benefits**:
- ✅ User bisa cancel order sebelum pembayaran
- ✅ No rollback needed (stock belum dikurangi)
- ✅ Clear audit trail

---

#### Helper Methods
```php
public function isPaid()              { return $this->status === 'paid'; }
public function isPendingPayment()    { return $this->status === 'pending_payment'; }
public function isCancelled()         { return $this->status === 'cancelled'; }
```

---

### 3. OrderController - NEW METHODS

#### confirmPayment($id) - Payment Confirmation Endpoint
```php
public function confirmPayment(Request $request, $id)
{
    // 1. Auth: Verify order belongs to current user
    $order = Order::where('user_id', Auth::id())->findOrFail($id);
    
    // 2. Validate order is pending_payment
    if (!$order->isPendingPayment()) {
        return error('Pesanan tidak bisa dikonfirmasi');
    }
    
    // 3. Call confirmPayment() - will decrement stock
    if ($order->confirmPayment()) {
        return success('Pembayaran berhasil dikonfirmasi!');
    } else {
        return error('Gagal mengkonfirmasi pembayaran');
    }
}
```

**Flow**:
1. Payment gateway memverifikasi pembayaran
2. Trigger endpoint ini (via webhook atau manual)
3. Stock decremented & order status updated
4. Return success/error

**Route**: `POST /history/{id}/confirm-payment`

---

#### cancelOrder($id) - Cancel Pending Order
```php
public function cancelOrder(Request $request, $id)
{
    // 1. Auth: Verify order belongs to current user
    $order = Order::where('user_id', Auth::id())->findOrFail($id);
    
    // 2. Validate order is pending_payment
    if (!$order->isPendingPayment()) {
        return error('Pesanan tidak bisa dibatalkan');
    }
    
    // 3. Cancel order
    if ($order->cancelOrder()) {
        return success('Pesanan berhasil dibatalkan');
    } else {
        return error('Gagal membatalkan pesanan');
    }
}
```

**Benefits**:
- ✅ User dapat membatalkan order sebelum payment
- ✅ No stock impact (belum dikurangi)
- ✅ Clear status tracking

**Route**: `POST /history/{id}/cancel`

---

### 4. Routes - NEW ENDPOINTS

**Added to routes/web.php**:
```php
Route::middleware(['auth'])->group(function () {
    // Existing routes
    Route::get('/checkout', ...);
    Route::post('/checkout/process', ...);
    Route::get('/history', ...);
    Route::get('/history/{id}', ...);
    
    // NEW: Payment & Cancellation Routes
    Route::post('/history/{id}/confirm-payment', [OrderController::class, 'confirmPayment'])
         ->name('order.confirmPayment');
    Route::post('/history/{id}/cancel', [OrderController::class, 'cancelOrder'])
         ->name('order.cancel');
});
```

**All protected with `auth` middleware** ✅

---

## Database Status Values

| Status | Meaning | Stock Impact | User Actions | Admin Actions |
|--------|---------|--------------|--------------|---------------|
| `pending_payment` | Order dibuat, menunggu pembayaran | No change | Cancel, Retry Payment | View, Cancel |
| `paid` | Pembayaran dikonfirmasi, stok dikurangi | ✅ Decreased | Track, Return | Process, Ship |
| `cancelled` | Order dibatalkan oleh user | No change | None | None |

---

## Security & Business Benefits

### Security
✅ **Prevent Overselling**: Stok hanya berkurang saat payment confirmed
✅ **No Financial Loss**: Payment fail = stok tetap aman, tidak ada loss
✅ **Atomic Operations**: All or nothing - semua data konsisten
✅ **Authorization**: User hanya manage order mereka sendiri
✅ **Data Consistency**: Transaction rollback jika ada error
✅ **Clear Audit Trail**: Status transitions tracked

### Business
✅ **Flexible Payment**: Support multiple payment methods/delays
✅ **User Experience**: User bisa lihat order status yang jelas
✅ **Inventory Accuracy**: Stock selalu akurat
✅ **Admin Control**: Admin bisa track payment status
✅ **Payment Gateway Ready**: Easy integration (just call confirmPayment)
✅ **Order Cancellation**: User dapat membatalkan sebelum payment

---

## Integration with Payment Gateway

### Webhook Flow (Example)
```php
// Payment Gateway Webhook (external)
Route::post('/webhook/payment-confirmation', function (Request $request) {
    $orderId = $request->order_id;
    $paymentStatus = $request->status;
    
    if ($paymentStatus === 'success') {
        $order = Order::findOrFail($orderId);
        $order->confirmPayment(); // Stock decremented here ✅
    }
});
```

### Benefits
✅ Clean separation of concerns
✅ Payment logic isolated
✅ Easy to test
✅ Easy to debug

---

## Testing Scenarios

### Test 1: Normal Checkout → Payment Confirmation
```
1. Add items to cart
2. Checkout (order created with status='pending_payment', stock not changed)
3. Verify stock unchanged
4. Call confirmPayment()
5. Verify stock decremented ✅
6. Verify status changed to 'paid' ✅
```

### Test 2: Payment Failure → Order Cancellation
```
1. Add items to cart
2. Checkout (order created, stock safe)
3. Verify stock unchanged
4. User cancels order
5. Verify status changed to 'cancelled'
6. Verify stock still unchanged ✅
```

### Test 3: Concurrent Orders (Stock Validation)
```
1. Order 1 confirmed (stock decremented)
2. Order 2 pending payment (stock not changed yet)
3. If Order 2 confirmPayment() before Order 1 completes
4. Should validate stock still sufficient
5. If not enough → rollback & error ✅
```

### Test 4: Authorization Check
```
1. User A creates order
2. User B tries to confirm User A's payment
3. Should fail (403 Forbidden) ✅
```

---

## Files Modified

1. **app/Http/Controllers/CartController.php**
   - Modified: processCheckout() - No stock decrement, status='pending_payment'

2. **app/Http/Controllers/OrderController.php**
   - Added: confirmPayment($id) - Confirm payment & decrement stock
   - Added: cancelOrder($id) - Cancel pending order
   - Existing: history(), show() - unchanged

3. **app/Models/Order.php**
   - Added: confirmPayment() - Payment confirmation logic
   - Added: cancelOrder() - Order cancellation logic
   - Added: isPaid(), isPendingPayment(), isCancelled() - Status helpers

4. **routes/web.php**
   - Added: POST /history/{id}/confirm-payment
   - Added: POST /history/{id}/cancel

5. **ANALISIS_PERBAIKAN.txt**
   - Updated: Improvement #4 status to COMPLETED

---

## Verification

- ✅ PHP Syntax: No errors
- ✅ Transaction Logic: Correct
- ✅ Authorization: User ownership verified
- ✅ Status Transitions: Valid
- ✅ Routes: Protected with auth middleware
- ✅ Error Handling: Proper rollback on failure

---

## Next Steps

1. **Test Payment Integration**: Test with payment gateway webhook
2. **View Templates**: Update order detail view to show payment status
3. **Admin Dashboard**: Track pending payments
4. **Email Notifications**: Send payment confirmation emails
5. **Improvement #5**: Implement data seeders for testing

---

**Completed by**: GitHub Copilot  
**Date**: 14 Mei 2026  
**Status**: ✅ READY FOR TESTING & PAYMENT GATEWAY INTEGRATION
