# Deploy ke Railway.app

## Langkah-langkah Deploy:

### 1. Buat Akun Railway
- Kunjungi https://railway.app
- Sign up dengan GitHub
- Verifikasi email

### 2. Buat Project Baru
1. Klik **"New Project"**
2. Pilih **"Deploy from GitHub repo"**
3. Pilih repository **QuickLow/Project-Rekon**
4. Railway akan auto-detect Dockerfile

### 3. Tambah Database
1. Di project Railway, klik **"New"** → **"Database"** → **"Add MySQL"**
   (atau **PostgreSQL** jika mau)
2. Tunggu database provisioning selesai
3. Railway otomatis inject environment variables:
   - `MYSQLHOST`
   - `MYSQLPORT`
   - `MYSQLUSER`
   - `MYSQLPASSWORD`
   - `MYSQLDATABASE`

### 4. Setting Environment Variables
Klik service Laravel Anda → **Variables** → Tambahkan:

```bash
# Application
APP_NAME="Rekon System"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:XXXXX  # Generate dulu dengan: php artisan key:generate --show
APP_URL=https://your-app.up.railway.app

# Database (gunakan Railway reference variables)
DB_CONNECTION=mysql
DB_HOST=${MYSQLHOST}
DB_PORT=${MYSQLPORT}
DB_DATABASE=${MYSQLDATABASE}
DB_USERNAME=${MYSQLUSER}
DB_PASSWORD=${MYSQLPASSWORD}

# Cache & Session
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=database

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### 5. Generate APP_KEY
Di terminal lokal:
```bash
php artisan key:generate --show
```
Copy hasilnya (misal: `base64:xxxxx`) dan paste ke Railway environment variable `APP_KEY`

### 6. Deploy & Migrate
1. Railway otomatis deploy setelah detect Dockerfile
2. Setelah deploy sukses, buka **Railway Console** (terminal icon):
   ```bash
   php artisan migrate --force
   ```

### 7. Akses Website
- Railway akan generate URL: `https://your-app.up.railway.app`
- Klik **"Settings"** → **"Networking"** untuk lihat public URL

## Storage Persistence
Railway punya persistent volume otomatis untuk `/var/www/html/storage`

Jika mau explicit volume:
1. Settings → Volumes → Add Volume
2. Mount Path: `/var/www/html/storage/app`

## Troubleshooting

### Error "No application encryption key"
Generate APP_KEY dan set di Railway environment variables

### Database connection error
Pastikan reference variables benar: `${MYSQLHOST}` bukan hardcoded

### Storage permission error
Dockerfile sudah handle, tapi kalau masih error:
```bash
# Di Railway console
chmod -R 775 storage bootstrap/cache
```

### Memory limit
Free tier Railway: 512MB RAM
Kalau error memory, optimize di `.env`:
```
MEMORY_LIMIT=256M
```

## Custom Domain (Optional)
1. Settings → Networking → Custom Domain
2. Tambah domain Anda
3. Update DNS sesuai instruksi Railway
4. Update `APP_URL` di environment variables

## Monitoring
- Railway Dashboard → Metrics
- Logs real-time di Deployments → View Logs

## Backup Database
Railway MySQL bisa di-export:
1. Database service → Data → Export
2. Atau via CLI: `mysqldump` dari Railway console
