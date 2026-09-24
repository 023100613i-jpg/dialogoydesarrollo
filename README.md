# DDP Noticias — Diálogo y Desarrollo Perú

Sitio web de periodismo independiente con panel de administración completo para la gestión de contenido informativo sobre diálogo, desarrollo y actualidad en el Perú.

![Estado](https://img.shields.io/badge/estado-en%20producci%C3%B3n-success?style=flat-square)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-4-7952B3?style=flat-square&logo=bootstrap&logoColor=white)
![Licencia](https://img.shields.io/badge/licencia-MIT-green?style=flat-square)

---

## 📖 Tabla de contenidos

- [Descripción](#-descripción)
- [Demo en vivo](#-demo-en-vivo)
- [Características](#-características)
- [Tecnologías](#-tecnologías)
- [Estructura del proyecto](#-estructura-del-proyecto)
- [Requisitos](#-requisitos)
- [Instalación local](#-instalación-local)
- [Base de datos](#-base-de-datos)
- [Despliegue en producción](#-despliegue-en-producción)
- [Capturas](#-capturas)
- [Credenciales de prueba](#-credenciales-de-prueba)
- [Autor](#-autor)
- [Licencia](#-licencia)

---

## 📝 Descripción

**DDP Noticias** es una plataforma web desarrollada para **Diálogo y Desarrollo Perú**, un espacio de periodismo independiente que busca visibilizar las acciones de diálogo en el país desde una mirada constructiva.

El sistema está compuesto por dos partes:

1. **Frontend público** — Sitio accesible para todos los visitantes con las secciones de noticias, reportajes, boletines, podcasts, alianzas y contenido especial.
2. **Panel de administración** — Área privada protegida con autenticación para gestionar todo el contenido mediante operaciones CRUD completas.

---

## 🚀 Demo en vivo

| Recurso | URL |
|---------|-----|
| 🌐 Aplicación pública | [https://dialogoydesarrollo.infinityfreeapp.com](https://dialogoydesarrollo.infinityfreeapp.com) |
| 🔐 Panel de administración | [https://dialogoydesarrollo.infinityfreeapp.com/admin](https://dialogoydesarrollo.infinityfreeapp.com/admin) |
| 💻 Repositorio GitHub | [https://github.com/023100613i-jpg/dialogoydesarrollo](https://github.com/023100613i-jpg/dialogoydesarrollo) |

---

## ✨ Características

### Frontend público

- 🏠 **Portada principal** con reportaje destacado, últimas noticias, boletín NTEP, podcasts, alianzas y especiales
- 📰 **Sección de noticias** con enlace externo y fecha de publicación
- 📄 **Reportajes** con paginación y vista de detalle con galería
- 📕 **Boletines NTEP** con descarga de PDF y visor de portada
- 🎙️ **Podcasts** con reproductor embebido (Spotify, YouTube, SoundCloud)
- 🤝 **Alianzas** institucionales con logos
- 👥 **Sobre nosotros** con historia y misión institucional
- ✉️ **Formulario de contacto** funcional con validación
- 📱 **Diseño responsive** (móvil, tablet, escritorio)

### Panel de administración

- 🔐 **Autenticación** por sesión con contraseñas hasheadas
- 👤 **Roles de usuario** (administrador, editor)
- 📊 **Dashboard** con KPIs, gráficos interactivos (Chart.js) y actividad reciente
- ✏️ **CRUD completo** para reportajes, noticias, boletines, podcasts, videos y especiales
- 📝 **Editor WYSIWYG** (Summernote) para contenido HTML enriquecido
- 🖼️ **Subida de imágenes** manteniendo el nombre original
- 📎 **Subida de PDFs** para boletines NTEP
- 🔄 **Sistema de estados** (publicado, borrador, archivado) con cambio rápido
- 🎯 **Filtros por estado** con contadores dinámicos
- 📄 **Paginación** en listados
- 🔔 **Notificaciones** de mensajes pendientes
- 👥 **Menú desplegable de usuario** con perfil y cierre de sesión

### Seguridad

- ✅ **Prepared statements (PDO)** — protege contra inyección SQL
- ✅ **Sanitización de inputs** con `htmlspecialchars()`
- ✅ **Protección XSS** en toda salida de datos
- ✅ **Autenticación por sesión** con `requireLogin()`
- ✅ **Validación de tipos** de archivo subidos (imágenes y PDFs)
- ✅ **Hash de contraseñas** con `password_hash()` y `password_verify()`

---

## 🛠️ Tecnologías

| Capa | Tecnología |
|------|-----------|
| **Backend** | PHP 8.2 |
| **Base de datos** | MySQL 8.0 / MariaDB |
| **Acceso a datos** | PDO con prepared statements |
| **Frontend** | HTML5, CSS3, JavaScript |
| **Framework CSS** | Bootstrap 4 |
| **Librería JS** | jQuery 3.7 |
| **Editor WYSIWYG** | Summernote 0.8.20 |
| **Gráficos** | Chart.js |
| **Iconografía** | Font Awesome 6 |
| **Fuentes** | Google Fonts (Cabin) |
| **Servidor local** | XAMPP (Apache + MySQL) |
| **Hosting** | InfinityFree |
| **Control de versiones** | Git + GitHub |

---

## 📁 Estructura del proyecto
