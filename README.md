# AgroNexus

Sistema de Gestión Agrícola integrado.

![AgroNexus Banner](https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg)

## 📋 Documentación de Despliegue

Siguiendo los requisitos del proyecto, se presentan dos formas de ejecutar la aplicación: **Contenedorizada (Docker)** y **Nativa (Local)**.

### 🐳 Opción 1: Ejecución con Contenedores (Docker) - **Recomendada**

Esta opción levanta todo el entorno (App, Base de Datos y Web Server) automáticamente sin necesidad de instalar PHP o Postgres en su máquina.

**Requisitos:**
- Docker Desktop instalado y corriendo.
- Git.

**Pasos:**

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/liquiddominator/AgroNexus.git
    cd AgroNexus
    ```

2.  **Configurar variables de entorno:**
    ```bash
    cp .env.example .env
    ```
    *Nota: El archivo `docker-compose.yml` ya preconfigura la conexión a la base de datos `AgroNexusDB` con usuario `postgres` y contraseña `user`.*

3.  **Construir y levantar contenedores:**
    ```bash
    docker-compose up -d --build
    ```

4.  **Instalar dependencias y preparar base de datos:**
    Ejecute los siguientes comandos dentro del contenedor de la aplicación:
    ```bash
    # Instalar dependencias de PHP
    docker-compose exec app composer install

    # Generar llave de aplicación
    docker-compose exec app php artisan key:generate
    ```
    Luego descargar el archivo agronexusdb.backup de la rama db-script, y restaurarlo en pgAdmin

5.  **Acceder al sistema:**
    Abra su navegador en: `http://localhost:8080`

---

### 💻 Opción 2: Ejecución Nativa (Local)

Para ejecutar directamente en su sistema operativo.

**Requisitos:**
- PHP >= 8.2
- Composer
- PostgreSQL instalado y corriendo.
- NodeJS y NPM (opcional, para compilar assets).

**Pasos:**

1.  **Clonar el repositorio:**
    ```bash
    git clone https://github.com/liquiddominator/AgroNexus.git
    cd AgroNexus
    ```

2.  **Instalar dependencias:**
    ```bash
    composer install
    ```

3.  **Configurar entorno:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Configurar Base de Datos:**
    - Cree una base de datos en PostgreSQL llamada `AgroNexusDB` (o el nombre que desee).
    - Abra el archivo `.env` y modifique las credenciales de base de datos:
      ```ini
      DB_CONNECTION=pgsql
      DB_HOST=127.0.0.1
      DB_PORT=5432
      DB_DATABASE=AgroNexusDB
      DB_USERNAME=su_usuario
      DB_PASSWORD=su_contraseña
      ```

5.  **Descargar el archivo backup de la rama db-script y restaurarlo en pgAdmin**

6.  **Ejecutar servidor de desarrollo:**
    ```bash
    php artisan serve
    ```

7.  **Acceder al sistema:**
    Abra su navegador en: `http://localhost:8000`

---

## 📸 Evidencias del Proyecto

El sistema incluye:
- **Dashboard Ejecutivo:** Estadísticas en tiempo real, gráficos de producción y widgets climáticos.
- **Reportes PDF:** Generación de reportes de Ventas, Producción, Inventario y Actividades.
- **Módulo Climático:** Integración con OpenWeather API e historial de registros.

### Generar Reportes de Prueba
Para verificar la generación de PDFs, puede ejecutar el siguiente comando:
```bash
php artisan reportes:test
```
Esto generará archivos de prueba en la carpeta `public/reportes_test`.
