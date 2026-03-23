# SICPRO 🚀
### Sistema de Información y Control de Proyectos

[![Laravel Version](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php)](https://www.php.net)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

SICPRO es una plataforma empresarial de alto rendimiento diseñada para centralizar la gestión administrativa, logística y técnica de proyectos complejos. Desarrollado sobre un robusto stack moderno, permite un control granular desde la planificación inicial hasta la ejecución financiera y técnica detallada.

---

## 🌟 Características Principales

### 🏗️ Gestión de Proyectos
- **Estructura Jerárquica**: Administración de proyectos y subproyectos con seguimiento individual.
- **Cronogramas**: Control de tiempos y hitos clave.

### 📦 Logística y Adquisiciones
- **Control de Compras**: Flujo completo de adquisiciones, desde la solicitud hasta la recepción.
- **Gestión de Proveedores**: Directorio especializado por categorías (Materiales, Servicios, Contratistas).
- **Inventario**: Control en tiempo real de bienes y suministros.

### 💰 Gestión Financiera
- **Balance Global**: Reportes financieros consolidados por proyecto.
- **Control de Caja**: Seguimiento de flujo de efectivo y revisiones periódicas.
- **Costos de Mano de Obra**: Liquidación y seguimiento de pagos semanales.

### 👥 Recursos Humanos y Solicitudes
- **Reposición de Tiempos**: Módulo para la gestión de ausencias y reposiciones de personal.
- **Roles y Permisos**: Seguridad basada en perfiles (RBAC) con control de acceso por módulo.

---

## 🛠️ Stack Tecnológico

### **Backend**
- **Framework**: [Laravel 10](https://laravel.com/)
- **Lenguaje**: [PHP 8.1+](https://php.net/)
- **Base de Datos**: PostgreSQL / MySQL (Soporte Multi-driver)
- **Seguridad**: Laravel Sanctum & Laravel UI

### **Frontend**
- **Estilos**: [Bootstrap 5](https://getbootstrap.com/) & Sass
- **Interactividad**: JavaScript (ES6+), Axios, Popper.js
- **Assets**: Laravel Mix

### **Librerías Clave**
- **[Spatie Permission](https://spatie.be/docs/laravel-permission)**: Gestión avanzada de autorizaciones.
- **[Yajra DataTables](https://yajrabox.com/docs/laravel-datatables)**: Procesamiento de grandes volúmenes de datos en tablas dinámicas.
- **[Maatwebsite Excel](https://docs.laravel-excel.com/)**: Generación inteligente de reportes en .xlsx.
- **[DOMPDF](https://github.com/barryvdh/laravel-dompdf)** & **[FPDF](http://www.fpdf.org/)**: Motor de generación de documentos PDF de alta fidelidad.
- **[AWS SDK](https://aws.amazon.com/sdk-for-php/)**: Integración con Amazon S3 para almacenamiento en la nube.
- **[Web Push](https://github.com/web-push-libs/web-push-php)**: Notificaciones en tiempo real para navegadores.

---

## 🚀 Instalación y Despliegue

### Requisitos Técnicos
- PHP >= 8.1
- Composer >= 2.x
- Node.js >= 16.x & NPM
- Servidor de BD (PostgreSQL recomendado)

### Pasos de Configuración

1. **Clonación del Repositorio**
   ```bash
   git clone <repo-url>
   cd sicpro
   ```

2. **Gestión de Dependencias**
   ```bash
   composer install
   npm install
   ```

3. **Configuración de Entorno**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   > **Nota**: Configure sus credenciales de base de datos y AWS S3 en el archivo `.env`.

4. **Base de Datos**
   ```bash
   php artisan migrate --seed
   ```

5. **Compilación y Servidor**
   ```bash
   npm run dev
   php artisan serve
   ```

---

## 📈 Dashboard y Reportes
El sistema incluye un módulo avanzado de **Reportería Gerencial** que permite visualizar:
- **Balance de Proyectos**: Rentabilidad y gastos acumulados.
- **Reporte de Gasolina**: Control específico para flota vehicular.
- **Estado de Solicitudes**: Seguimiento de ausentismo y reposiciones.

---

## 📄 Licencia
Este proyecto es software privado. Para consultas sobre licenciamiento o soporte, contactar al administrador del sistema.

---
*SICPRO - Potenciando la eficiencia en la gestión de proyectos.*
