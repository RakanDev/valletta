# Valletta 👜

متجر Valletta لشنط الظهر: موقع ثنائي اللغة (عربي RTL / إنجليزي LTR)، الطلب بينحفظ وبيفتح واتساب برسالة جاهزة، والدفع كاش عند الاستلام.

**التقنيات:** Laravel 12 · Filament 3 · MySQL · Tailwind + Alpine · Cloudflare R2 · Railway (Docker)

## التشغيل محلياً
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
# حط ADMIN_PASSWORD بالـ .env
php artisan migrate --seed && php artisan storage:link
npm run build && php artisan serve
```
- الموقع: http://127.0.0.1:8000
- لوحة التحكم: http://127.0.0.1:8000/admin
- الاختبارات: `php artisan test`

## النشر على Railway
1. New Project ← Deploy from GitHub repo (بيستخدم الـ `Dockerfile` تلقائياً)
2. أضف خدمة **MySQL** بنفس المشروع
3. متغيرات خدمة الموقع (Variables):

| المتغير | القيمة |
|---|---|
| `APP_KEY` | ناتج `php artisan key:generate --show` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | رابط الموقع على Railway |
| `DB_CONNECTION` | `mysql` |
| `DB_URL` | `${{MySQL.MYSQL_URL}}` |
| `MEDIA_DISK` | `r2` |
| `R2_ACCESS_KEY_ID` / `R2_SECRET_ACCESS_KEY` | من Cloudflare ← R2 ← Manage API Tokens |
| `R2_BUCKET` | اسم الـ bucket |
| `R2_ENDPOINT` | `https://<ACCOUNT_ID>.r2.cloudflarestorage.com` |
| `R2_PUBLIC_URL` | رابط الـ bucket العام (r2.dev أو دومين خاص) |
| `ADMIN_EMAIL` / `ADMIN_PASSWORD` | حساب لوحة التحكم |
| `SEED_ON_DEPLOY` | `true` **لأول نشر بس**، بعدها احذفه |
| `PORT` | `8080` |

4. Settings ← Networking ← Generate Domain
5. الـ migrations بتشتغل تلقائياً مع كل نشر.

> ⚠️ الـ R2 bucket لازم يكون **Public** (R2.dev subdomain أو custom domain) عشان الصور تظهر.

## كيف بيشتغل المخزون
- طلب **جديد** ما بيلمس المخزون
- لما يصير **مؤكد / مشحون / مسلّم** الكمية بتنقص مرة وحدة
- إذا **انلغى / ارتجع / انحذف** الكمية بترجع
- إذا ما في كمية كافية، التأكيد بيترفض برسالة واضحة

## الترجمة
كل النصوص بـ `lang/ar.json` و `lang/en.json` (نفس المفاتيح، وفي اختبار بيتأكد من هيك).
