# 🍔 Restaurant / Food Delivery API

A robust RESTful API built with **Laravel**, designed to manage a restaurant menu system including Categories and Dishes (Plats). The API features secure authentication, role-based authorization, and cloud-based image storage.

## ✨ Features

* **Categories Management:** CRUD operations for menu categories.
* **Plats (Dishes) Management:** CRUD operations for dishes, linked to categories.
* **Cloud Storage Integration:** Images for categories and dishes are uploaded, updated, and deleted securely on **Cloudflare R2** (S3 Compatible Storage).
* **Advanced API Documentation:** Fully documented API endpoints using **Swagger (OpenAPI)** via PHP 8 Attributes.
* **Security:** * API endpoints secured with Bearer Token Authentication.
  * Action authorization protected by Laravel Gates/Policies.

## 💻 Tech Stack

* **Framework:** Laravel 10/11 (PHP 8.2+)
* **Database:** MySQL / PostgreSQL
* **Cloud Storage:** Cloudflare R2 / AWS S3 Sdk (`league/flysystem-aws-s3-v3`)
* **Documentation:** L5-Swagger (`darkaonline/l5-swagger`)

## 🚀 Installation & Setup

Follow these steps to set up the project locally:

**1. Clone the repository**
```bash
git clone <your-repository-url>
cd <your-project-folder>