<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

### Gubuk Tani Backend

1. Instal Package
    `composer install`
2. Change environment
3. Create symbolic link to public storage
    `php artisan storage:link`
4. Run database migration
    `php artisan migrate`


# API Endpoints

## User

| Method | Endpoint | Expectation | Code | Response Body | Result |
| --- | --- | --- | --- | --- | --- |
| POST | /auth/register | Bisa menambahkan data User | 201 | Access Token dan Satu data User | ✅ |
| GET | /auth/login | Bisa log In dengan Email dan Password | 200 | Access Token dan Satu data User | ✅ |
| GET | /profile | Bisa mengambil data User | 200 | Satu data User | ✅ |
| POST | /profile | Bisa mengubah data User | 200 | Satu data User | ✅ |
| POST | /auth/logout | Bisa Log Out dan revoke Access Token | 200 | Token revoke status | ✅ |

## Article

| Method | Endpoint | Expectation | Code | Response Body | Result |
| --- | --- | --- | --- | --- | --- |
| POST | /article | Bisa menambahkan Article | 201 | Satu data Article | ✅ |
| GET | /article | Bisa mengambil daftar data Article | 200 | Array data Article | ✅ |
| GET | /article/{id} | Bisa mengambil salah satu data Article | 200 | Satu data Article | ✅ |
| POST | /article/{id} | Bisa mengubah salah satu data Article | 200 | Satu data Article | ✅ |
| DELETE | /article/{id} | Bisa menghapus data salah satu Article | 200 | ID Article | ✅ |

## Article Image

| Method | Endpoint | Expectation | Code | Response Body | Result |
| --- | --- | --- | --- | --- | --- |
| POST | /article/{article_id}/image | Bisa menambahkan Article Image | 201 | Satu data Article Image | ✅ |
| GET | /article/{article_id}/image | Bisa mengambil daftar data Article Image | 200 | Array data Article Image | ✅ |
| GET | /article/{article_id}/image/{id} | Bisa mengambil salah satu data Article Image | 200 | Satu data Article Image | ✅ |
| POST | /article/{article_id}/image/{id}

Additional form data:
_method: ‘PUT’ | Bisa mengubah salah satu data Article Image | 200 | Satu data Article Image | ✅ |
| DELETE | /article/{article_id}/image/{id} | Bisa menghapus data salah satu Article Image | 200 | ID Article Image | ✅ |

## Comment

| Method | Endpoint | Expectation | Code | Response Body | Result |
| --- | --- | --- | --- | --- | --- |
| POST | /article/{article_id}/comment | Bisa menambahkan Comment | 201 | Satu data Comment | ✅ |
| GET | /article/{article_id}/comment | Bisa mengambil daftar data Comment | 200 | Array data Comment | ✅ |
| GET | /article/{article_id}/comment/{id} | Bisa mengambil salah satu data Comment | 200 | Satu data Comment | ✅ |
| PUT | /article/{article_id}/comment/{id} | Bisa mengubah salah satu data Comment | 200 | Satu data Comment | ✅ |
| DELETE | /article/{article_id}/comment/{id} | Bisa menghapus data salah satu Comment | 200 | ID Comment | ✅ |

## Disease

| Method | Endpoint | Expectation | Code | Response Body | Result |
| --- | --- | --- | --- | --- | --- |
| POST | /disease | Bisa menambahkan Disease dan Article Disease | 201 | Satu data Disease dan Article Disease | ✅ |
| GET | /disease | Bisa mengambil daftar data Disease | 200 | Array data Disease | ✅ |
| GET | /disease/{id} | Bisa mengambil salah satu data Disease | 200 | Satu data Disease | ✅ |
| POST | /disease/{id} | Bisa mengubah salah satu data Disease | 200 | Satu data Disease | ✅ |
| DELETE | /disease/{id} | Bisa menghapus data salah satu Disease dan Article Disease | 200 | ID Disease | ✅ |

## Pesticide

| Method | Endpoint | Expectation | Code | Response Body | Result |
| --- | --- | --- | --- | --- | --- |
| POST | /pesticide | Bisa menambahkan Pesticide dan Article Pesticide | 201 | Satu data Pesticide dan Article Pesticide | ✅ |
| GET | /pesticide | Bisa mengambil daftar data Pesticide | 200 | Array data Pesticide | ✅ |
| GET | /pesticide/{id} | Bisa mengambil salah satu data Pesticide | 200 | Satu data Pesticide | ✅ |
| POST | /pesticide/{id} | Bisa mengubah salah satu data Pesticide | 200 | Satu data Pesticide | ✅ |
| DELETE | /pesticide/{id} | Bisa menghapus data salah satu Pesticide dan Article Pesticide | 200 | ID Pesticide | ✅ |

## Setting

| Method | Endpoint | Expectation | Code | Response Body | Result |
| --- | --- | --- | --- | --- | --- |
| POST | /setting | Bisa menambahkan satu data Setting | 201 | Semua data Setting | ✅ |
| GET | /setting | Bisa mengambil semua data Setting | 200 | Semua data Setting | ✅ |
| POST | /setting/update | Bisa mengubah salah satu Setting | 200 | Semua data Setting | ✅ |
| DELETE | /setting/delete | Bisa menghapus salah satu data Setting | 200 | Key Setting | ✅ |

## Image Recognition (Machine Learning)

### Plant (ML)

| Method | Endpoint | Expectation | Code | Response Body | Result |
| --- | --- | --- | --- | --- | --- |
| POST | /plant

Ket.:
⚠️ Admin only | Bisa menambahkan Plant | 201 | Satu data Plant | ✅ |
| GET | /plant | Bisa mengambil daftar data Plant | 200 | Array data Plant | ✅ |
| GET | /plant/{id} | Bisa mengambil salah satu data Plant | 200 | Satu data Plant | ✅ |
| POST | /plant/{id} | Bisa mengubah salah satu data Plant | 200 | Satu data Plant | ✅ |
| DELETE | /plant/{id} | Bisa menghapus data salah satu Plant dan Label | 200 | ID Plant | ✅ |

### Label (ML)

| Method | Endpoint | Expectation | Code | Response Body | Result |
| --- | --- | --- | --- | --- | --- |
| POST | /plant/{plant_id}/label | Bisa menambahkan Label | 201 | Satu data Label | ✅ |
| GET | /plant/{plant_id}/label | Bisa mengambil daftar data Label | 200 | Array data Label | ✅ |
| GET | /plant/{plant_id}/label/{id} | Bisa mengambil salah satu data Label | 200 | Satu data Label | ✅ |
| PUT | /plant/{plant_id}/label/{id} | Bisa mengubah salah satu data Label | 200 | Satu data Label | ✅ |
| DELETE | /plant/{plant_id}/label/{id} | Bisa menghapus data salah satu Label | 200 | ID Label | ✅ |

### Detection (ML)

| Method | Endpoint | Expectation | Code | Response Body | Result |
| --- | --- | --- | --- | --- | --- |
| POST | /detection | Bisa menambahkan Detection | 201 | Hasil Detection | ✅ |
| GET | /detection | Bisa mengambil daftar data Detection | 200 | Array data Detection | ✅ |
| GET | /detection/{id} | Bisa mengambil salah satu data Detection | 200 | Satu data Detection | ✅ |
| DELETE | /detection/{id} | Bisa menghapus data salah satu Detection | 200 | ID Detection | ✅ |

---

#LifeAtBangkit