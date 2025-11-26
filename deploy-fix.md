# 🔧 Fix Error 500 di Production Server

## 📋 Diagnosis

Error 500 pada URL `https://kurokana.alwaysdata.net/sensors/soil` kemungkinan disebabkan oleh:

1. ❌ **Autoload belum di-refresh** - `SensorDataService` dan `HasSensorQueries` tidak ditemukan
2. ❌ **Cache lama** - Config/view/route cache masih versi lama
3. ❌ **Migration belum dijalankan**

---

## ✅ Solusi: Jalankan di Server Production

### **Opsi 1: Via SSH (Recommended)**

Login ke server AlwaysData via SSH, lalu jalankan:

```bash
# 1. Masuk ke direktori project
cd ~/www

# 2. Pull latest code
git pull origin main

# 3. Install/Update dependencies
composer install --no-dev --optimize-autoloader

# 4. Refresh autoload (PENTING!)
composer dump-autoload --optimize

# 5. Run migrations
php artisan migrate --force

# 6. Clear ALL caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 7. Optimize untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Fix permissions (jika perlu)
chmod -R 775 storage bootstrap/cache
```

### **Opsi 2: Via AlwaysData Admin Panel**

1. **Login ke AlwaysData**: https://admin.alwaysdata.com
2. **Go to SSH Access** → Open Web SSH
3. **Jalankan commands di atas**

### **Opsi 3: Deployment Script (Automated)**

Buat file `deploy.sh` di server:

```bash
#!/bin/bash

echo "🚀 Starting deployment..."

cd ~/www

echo "📦 Pulling latest code..."
git pull origin main

echo "📦 Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "🔄 Refreshing autoload..."
composer dump-autoload --optimize

echo "🗄️  Running migrations..."
php artisan migrate --force

echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment complete!"
```

Lalu jalankan:

```bash
chmod +x deploy.sh
./deploy.sh
```

---

## 🔍 Verifikasi

Setelah deployment, test endpoint:

```bash
# Test dari terminal
curl https://kurokana.alwaysdata.net/sensors/soil
```

Atau buka di browser:
- https://kurokana.alwaysdata.net/sensors/soil
- https://kurokana.alwaysdata.net/sensors/environment
- https://kurokana.alwaysdata.net/sensors/health

---

## 🐛 Troubleshooting

### Error: "Class 'App\Services\SensorDataService' not found"

**Solusi:**
```bash
composer dump-autoload --optimize
```

### Error: "Class 'App\Traits\HasSensorQueries' not found"

**Solusi:**
```bash
composer dump-autoload --optimize
php artisan config:clear
```

### Error: "SQLSTATE[42S02]: Table not found"

**Solusi:**
```bash
php artisan migrate --force
```

### Error: "Permission denied"

**Solusi:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 📊 Expected Result

Setelah deployment berhasil, Anda akan melihat:

✅ Halaman sensor monitoring berjalan normal  
✅ Sidebar menu "Environment", "Soil Moisture", "Plant Health" berfungsi  
✅ Data sensor ditampilkan (jika ada device aktif)  
✅ No error 500  

---

## 🔐 Security Notes

- ✅ `composer install --no-dev` → Tidak install dev dependencies
- ✅ `--optimize-autoloader` → Optimasi performa
- ✅ Cache di-refresh untuk menghindari stale data
- ✅ Migration running dengan `--force` (production)

---

## 📞 Support

Jika masih error, check Laravel logs:

```bash
tail -f storage/logs/laravel.log
```

Atau via AlwaysData admin panel:
**Sites → Your Site → Logs → Application Logs**

---

**Last Updated:** November 26, 2025  
**Status:** Ready to Deploy ✅
