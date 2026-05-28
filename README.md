# API de Firma Electrónica de Documentos 🖊️

API REST construida con Laravel 11 que implementa un flujo completo de firma electrónica de documentos. Permite subir un PDF, registrar firmantes con orden secuencial, enviar links de firma únicos con expiración, y generar el PDF final con una tabla de firmas al pie.

Desarrollada como proyecto de portafolio basado en experiencia real con sistemas de firma electrónica en producción (Zora Systems, 2022–2026).

---

## 🚀 Tecnologías

| Tecnología | Descripción |
|---|---|
| `PHP 8.2+` | Lenguaje principal |
| `Laravel 11` | Framework backend |
| `MySQL` | Base de datos relacional |
| `Laravel Sanctum` | Autenticación por tokens Bearer |
| `barryvdh/laravel-dompdf` | Generación del PDF firmado |
| `L5-Swagger / OpenAPI 3.0` | Documentación interactiva |
| `PHPUnit` | Pruebas automatizadas |
| `Mailpit (local)` | Simulación de emails en desarrollo |

---

## ✨ Funcionalidades

- Autenticación segura con tokens Bearer (Sanctum)
- Firma secuencial: el firmante N no puede firmar hasta que firme el N-1
- Tokens únicos de firma con expiración configurable (default: 48 horas)
- Soft deletes en documentos para conservar historial legal
- Log de auditoría de todas las acciones (quién, qué, cuándo, desde dónde)
- Generación automática del PDF final con tabla de firmas al pie
- Documentación interactiva con Swagger UI
- Validaciones de negocio con Form Requests
- Arquitectura con Services para separar lógica de negocio del controller

---

## 📋 Instalación

### Requisitos previos

- PHP 8.2+
- Composer
- MySQL

### Pasos

```bash
# 1. Clonar el repositorio
git clone https://github.com/xCJRA/laravel-firma-digital.git
cd laravel-firma-digital

# 2. Instalar dependencias
composer install

# 3. Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar tu base de datos en .env
# DB_DATABASE=firma_digital
# DB_USERNAME=root
# DB_PASSWORD=tu_password

# 5. Crear la base de datos
mysql -u root -p -e "CREATE DATABASE firma_digital CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 6. Ejecutar migraciones y seeders
php artisan migrate --seed

# 7. Configurar el storage
php artisan storage:link

# 8. Generar documentación Swagger
php artisan l5-swagger:generate

# 9. Levantar el servidor
php artisan serve
```

Con el servidor corriendo, accede a:

- API: `http://localhost:8000/api`
- Documentación Swagger: `http://localhost:8000/api/documentation`

---

## 🔐 Autenticación

Todos los endpoints (excepto `POST /api/firmar/{token}`) requieren un token Bearer. Para obtenerlo:

```json
POST /api/login
Content-Type: application/json

{
    "email": "admin@firmadigital.com",
    "password": "password123"
}
```

Usa el token en el header de todas las peticiones:

```
Authorization: Bearer {tu_token}
```

---

## 🔄 Flujo principal

```
[POST /api/documentos]                        → Subes el doc → estado "pendiente"
[POST /api/documentos/{id}/firmantes]         → Agregas firmantes con nombre, email y orden
                                              → Se genera token y se envía el link por email
[POST /api/firmar/{token}]                    → El firmante valida el token y registra su firma
                                              → Firmante 2 no puede firmar hasta que firme el 1
                                              → Cuando todos firman → PDF final → "completado"
[GET  /api/documentos/{id}]                   → Consultas el estado en cualquier momento
```

---

## 📌 Endpoints

| Método | Endpoint | Descripción |
|---|---|---|
| `POST` | `/api/login` | Obtener token de acceso |
| `GET` | `/api/documentos` | Listar todos los documentos |
| `POST` | `/api/documentos` | Crear documento y subir PDF |
| `GET` | `/api/documentos/{id}` | Ver detalle y estado del documento |
| `DELETE` | `/api/documentos/{id}` | Eliminar documento (soft delete) |
| `POST` | `/api/documentos/{id}/firmantes` | Agregar firmante y generar token |
| `POST` | `/api/documentos/{id}/reenviar/{firmante_id}` | Reenviar link de firma |
| `POST` | `/api/firmar/{token}` | Registrar firma con timestamp e IP |
| `GET` | `/api/documentos/{id}/auditoria` | Ver log de auditoría del documento |

---

## 💡 Ejemplo — Registrar firma

El firmante recibe un link con el token. Al hacer clic, se ejecuta:

```json
POST /api/firmar/a3f9c2e1b8d74506a2e1c3f9b8d74506
```

Respuesta cuando todos los firmantes han completado el proceso:

```json
{
    "mensaje": "Documento firmado exitosamente",
    "firmante": "María García",
    "firmado_at": "2026-05-27T14:35:00",
    "documento": {
        "id": 1,
        "nombre": "Contrato de servicios",
        "estado": "completado",
        "archivo_firmado": "/storage/documentos/firmados/doc_1_signed.pdf"
    }
}
```

---

## 🗄️ Estructura de la base de datos

| Tabla | Descripción |
|---|---|
| `users` | Usuarios autenticados del sistema |
| `documentos` | Documentos a firmar (soft deletes) |
| `firmantes` | Firmantes con nombre, email y orden de firma |
| `tokens_firma` | Tokens únicos con expiración (48 hrs) |
| `firmas` | Registro de firma con timestamp e IP |
| `auditoria` | Log de todas las acciones del sistema |

---

## 👨‍💻 Autor

**César José Reyes Alonso** — Backend Developer  
[LinkedIn](https://linkedin.com/in/xcjra) · [GitHub](https://github.com/xCJRA) · cesarjreyesa1@gmail.com
