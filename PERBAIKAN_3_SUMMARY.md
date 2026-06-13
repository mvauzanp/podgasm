# Perbaikan #3 - Input Validation Enhancement
**Status**: ✅ COMPLETED - 14 Mei 2026

## Overview
Implementasi comprehensive input validation dan sanitization untuk CartController dan AuthController untuk mencegah invalid data entry, XSS attacks, SQL injection, dan meningkatkan data integrity.

---

## 1. CartController Enhancements

### 1.1 addToCart() Method
**File**: `app/Http/Controllers/CartController.php`

**Improvements**:
- ✅ Validasi ID produk (numeric, > 0) sebelum query database
- ✅ Cek stok tersedia (stok_aktual > 0)
- ✅ Validasi max quantity per produk (1000 unit)
- ✅ Validasi stok cukup saat menambah quantity

**Validation Rules**:
```php
// ID validation
if (!is_numeric($id) || $id <= 0) {
    return error;
}

// Stock validation
if ($product->stok_aktual <= 0) {
    return error;
}

// Quantity limit
if ($newQuantity > 1000) {
    return error;
}
```

**Security Benefits**:
- Prevent invalid product IDs
- Prevent quantity manipulation
- Prevent overselling

---

### 1.2 updateCart() Method
**File**: `app/Http/Controllers/CartController.php`

**Validation Rules**:
```php
$request->validate([
    'id' => 'required|integer|exists:cart_items,id',
    'quantity' => 'required|integer|min:1|max:1000',
], [
    // Custom error messages untuk semua rules
    'id.required' => 'ID item tidak boleh kosong',
    'id.integer' => 'ID item harus berupa angka',
    'id.exists' => 'Item tidak ditemukan di keranjang',
    'quantity.required' => 'Jumlah tidak boleh kosong',
    'quantity.integer' => 'Jumlah harus berupa angka bulat',
    'quantity.min' => 'Jumlah minimal adalah 1 unit',
    'quantity.max' => 'Jumlah maksimal adalah 1000 unit',
]);
```

**Additional Checks**:
- Stock availability validation
- Clear error messages untuk user

**Security Benefits**:
- Prevent negative quantities
- Prevent non-numeric input
- Prevent quantity exceeding limits
- Custom validation messages

---

### 1.3 removeFromCart() Method
**File**: `app/Http/Controllers/CartController.php`

**Validation Rules**:
```php
$request->validate([
    'id' => 'required|integer|exists:cart_items,id',
], [
    'id.required' => 'ID item tidak boleh kosong',
    'id.integer' => 'ID item harus berupa angka',
    'id.exists' => 'Item tidak ditemukan di keranjang',
]);

// Ownership check (CRITICAL for security)
if ($cart->user_id !== Auth::id()) {
    return error('Anda tidak berhak menghapus item ini');
}
```

**Security Benefits**:
- Input validation
- Ownership verification (prevent unauthorized deletion)
- Authorization check

---

### 1.4 processCheckout() Method
**File**: `app/Http/Controllers/CartController.php`

#### Input Validation Rules:

**nama_penerima**:
```php
'nama_penerima' => [
    'required',
    'string',
    'max:255',
    'regex:/^[a-zA-Z\s\-\.\']+$/', // Only letters, spaces, dash, dot, apostrophe
]
```

**email**:
```php
'email' => [
    'required',
    'email',
    'max:255',
    'regex:/^[a-zA-Z0-9._\-@]+$/', // Strict email format
]
```

**no_telp** (Phone Number):
```php
'no_telp' => [
    'required',
    'numeric',
    'digits_between:10,15', // 10-15 digits
    'regex:/^(0|62)[0-9]{9,13}$/', // Local (0) or international (62)
]
```

**alamat_pengiriman** (Address):
```php
'alamat_pengiriman' => [
    'required',
    'string',
    'min:10',
    'max:500',
    'regex:/^[a-zA-Z0-9\s\.\,\-\(\)]+$/', // Valid address format
]
```

**metode_pembayaran** (Payment Method):
```php
'metode_pembayaran' => [
    'required',
    'string',
    'in:cash,transfer,e-wallet', // Enum validation
]
```

#### Additional Data Validation:

**Quantity Validation**:
- Prevent negative quantities
- Max 1000 per product
- Must match what's in cart

**Price Validation**:
- Subtotal > 0 and < 999,999,999
- Total price > 0 and < 999,999,999
- Price change detection

**Stock Validation**:
- Verify stock available
- Check stock before creating order
- Prevent overselling

**Business Logic Validation**:
- Check cart not empty
- Validate all items in transaction
- All or nothing principle

#### Custom Error Messages:
Semua fields memiliki custom error messages dalam bahasa Indonesia untuk better UX.

**Security Benefits**:
- XSS prevention via regex validation
- SQL injection prevention
- Format validation untuk data integrity
- Max length validation untuk database safety
- Price manipulation prevention
- Overselling prevention
- Complete data validation

---

## 2. AuthController Enhancements

### 2.1 register() Method
**File**: `app/Http/Controllers/AuthController.php`

#### Validation Rules:

**name**:
```php
'name' => [
    'required',
    'string',
    'max:255',
    'regex:/^[a-zA-Z\s\-\.\']+$/', // Only letters, spaces, dash, dot, apostrophe
]
```

**email**:
```php
'email' => [
    'required',
    'string',
    'email',
    'max:255',
    'unique:users',
    'regex:/^[a-zA-Z0-9._\-+]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/',
]
```

**password**:
```php
'password' => [
    'required',
    'string',
    'min:8',
    'confirmed',
    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', // Min 1 lowercase, 1 uppercase, 1 digit
]
```

**role**:
```php
'role' => [
    'required',
    'in:customer,branch', // Strict enum validation
]
```

#### Input Sanitization:
```php
$name = strip_tags(trim($request->name));
$email = strtolower(trim($request->email));
$role = trim($request->role);
```

**Sanitization Benefits**:
- Remove HTML tags (prevent XSS)
- Trim whitespace
- Normalize email case
- Prevent injection attacks

**Security Benefits**:
- Strong password enforcement (min 8 chars, uppercase, lowercase, digit)
- Email format validation
- Name format validation
- XSS prevention via sanitization
- Role manipulation prevention
- Duplicate email prevention

---

### 2.2 login() Method
**File**: `app/Http/Controllers/AuthController.php`

#### Validation Rules:

**email**:
```php
'email' => [
    'required',
    'email',
    'max:255',
    'regex:/^[a-zA-Z0-9._\-+]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/',
]
```

**password**:
```php
'password' => [
    'required',
    'string',
    'min:8',
    'max:255',
]
```

#### Email Normalization:
```php
$credentials['email'] = strtolower(trim($credentials['email']));
```

**Security Benefits**:
- Email format validation
- Case-insensitive login
- Input sanitization
- Prevent credential enumeration (onlyInput('email'))

---

## Security Improvements Summary

| Issue | Before | After | Status |
|-------|--------|-------|--------|
| Quantity validation | ❌ Missing | ✅ min:1, max:1000 | Fixed |
| Max quantity per product | ❌ No limit | ✅ 1000 unit max | Fixed |
| Email format | ⚠️ Basic | ✅ Regex validation | Enhanced |
| Phone number | ❌ No format check | ✅ Regex validation | Fixed |
| Name format | ❌ No validation | ✅ Regex validation | Fixed |
| Address validation | ❌ Basic | ✅ Format + length | Enhanced |
| Password strength | ⚠️ Min 8 chars | ✅ + uppercase + digit | Enhanced |
| XSS Prevention | ❌ Missing | ✅ strip_tags, regex | Fixed |
| SQL Injection | ⚠️ Basic | ✅ Laravel validation | Enhanced |
| Ownership check | ❌ Missing | ✅ Added | Fixed |
| Custom error messages | ❌ Generic | ✅ Detailed, localized | Fixed |
| Input sanitization | ❌ Missing | ✅ trim, strtolower | Added |

---

## Testing Checklist

- ✅ PHP Syntax Check (no errors)
- ⏳ Test cart add with invalid quantity
- ⏳ Test cart update with non-numeric input
- ⏳ Test cart remove with invalid ID
- ⏳ Test checkout with invalid email format
- ⏳ Test checkout with invalid phone number
- ⏳ Test register with weak password
- ⏳ Test register with duplicate email
- ⏳ Test login with invalid email format
- ⏳ Test max quantity (1001 should fail)
- ⏳ Test XSS attempt in name/address fields

---

## Files Modified

1. **app/Http/Controllers/CartController.php**
   - Enhanced addToCart()
   - Enhanced updateCart()
   - Enhanced removeFromCart()
   - Enhanced processCheckout()

2. **app/Http/Controllers/AuthController.php**
   - Enhanced register()
   - Enhanced login()

3. **ANALISIS_PERBAIKAN.txt**
   - Updated improvement #3 status to COMPLETED

---

## Next Steps

1. **Improvement #4**: Buat OrderController dengan proper validation dan workflow
2. **Testing**: Run comprehensive validation tests
3. **Documentation**: Update API documentation
4. **Deployment**: Deploy changes to staging environment

---

**Completed by**: GitHub Copilot  
**Date**: 14 Mei 2026  
**Status**: ✅ READY FOR TESTING
