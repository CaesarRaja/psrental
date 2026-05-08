# Folder Gambar

Taruh gambar statis di sini untuk digunakan di view Blade.

## Struktur Folder

- `landing/`  -> Gambar hero, banner, ilustrasi landing page
- `auth/`     -> Gambar background login / register / forgot password
- `logo/`     -> Logo aplikasi, favicon, watermark
- `icons/`    -> Icon custom (selain FontAwesome)
- `avatars/`  -> Avatar default user/admin

## Cara pakai di Blade

Contoh:
```blade
<img src="{{ asset('images/landing/hero-ps5.png') }}" alt="PS5">
<img src="{{ asset('images/logo/logo.png') }}" alt="Logo">
<img src="{{ asset('images/auth/bg-login.jpg') }}" alt="Background Login">
```

## Catatan

- Gambar di folder `public/images/` langsung bisa diakses browser tanpa symlink.
- Untuk gambar upload user, gunakan `storage/app/public/` + `php artisan storage:link`.
