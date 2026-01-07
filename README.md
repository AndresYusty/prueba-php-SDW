# Formulario de Registro de Usuario

Sistema completo de formulario con validaciones, Ajax y geolocalización.

## Características

- ✅ Validación de documento único (evita duplicados)
- ✅ Validación de nombre (solo letras, números y espacios)
- ✅ Validación de edad (mayor de 18 años)
- ✅ Select con búsqueda para género
- ✅ Multi-select para preferencias (almacenadas como JSON)
- ✅ Captura de coordenadas GPS del dispositivo
- ✅ Validaciones en cliente (JavaScript) y servidor (PHP)
- ✅ Envío de datos mediante Ajax
- ✅ Código completamente documentado

## Estructura del Proyecto

```
formulario-registro/
│
├── index.html              # Formulario principal
├── css/
│   └── styles.css          # Estilos del formulario
├── js/
│   └── script.js           # Validaciones, Ajax y geolocalización
├── php/
│   ├── config.php          # Configuración de base de datos
│   ├── controlador.php     # Controlador principal (POST)
│   └── verificar_usuario.php # Verificación de usuario existente
├── database.sql            # Script SQL para MySQL
└── README.md              # Este archivo
```

## Instalación

### 1. Base de Datos

1. Abre MySQL Workbench
2. Copia y pega el contenido del archivo `database.sql`
3. Ejecuta el script para crear la base de datos y tabla

### 2. Configuración PHP

Edita el archivo `php/config.php` y ajusta las credenciales de tu base de datos:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'tu_contraseña');
define('DB_NAME', 'formulario_db');
```

### 3. Servidor Web

Coloca todos los archivos en el directorio de tu servidor web (XAMPP, WAMP, etc.):

- **XAMPP**: `C:\xampp\htdocs\formulario-registro\`
- **WAMP**: `C:\wamp64\www\formulario-registro\`

### 4. Acceder al Formulario

Abre tu navegador y accede a:
```
http://localhost/Prueba-Oliver/
```

## Requisitos

- PHP 7.0 o superior
- MySQL 5.7 o superior (soporte para JSON)
- Servidor web (Apache, Nginx, etc.)
- Navegador moderno con soporte para geolocalización

## Dependencias Externas

El proyecto utiliza las siguientes librerías CDN:

- **jQuery 3.6.0**: Para Ajax y manipulación del DOM
- **Select2 4.1.0**: Para selects con búsqueda

## Validaciones Implementadas

### Cliente (JavaScript)
- Documento: Solo números
- Nombre: Solo letras, números y espacios
- Edad: Mayor de 18 años
- Verificación en tiempo real de usuario existente
- Validación de campos requeridos

### Servidor (PHP)
- Validación de tipo de documento
- Validación de formato de documento
- Validación de nombre (sin caracteres especiales)
- Validación de edad (18-120 años)
- Validación de género
- Validación de preferencias (array)
- Verificación de usuario duplicado
- Sanitización de datos de entrada

## Campos del Formulario

1. **Tipo de Documento**: CC, CE, PA, TI
2. **Documento**: Solo números, único en la base de datos
3. **Nombre**: Letras, números y espacios
4. **Edad**: Número entero, mínimo 18 años
5. **Género**: Select con búsqueda (M, F, O, PN)
6. **Preferencias**: Multi-select (almacenado como JSON)
7. **Coordenadas**: Latitud y longitud (opcional)

## Funcionalidades

### Geolocalización
- El usuario puede obtener sus coordenadas GPS haciendo clic en "Obtener Coordenadas"
- Requiere permiso del navegador para acceder a la ubicación
- Las coordenadas se almacenan con 6 decimales de precisión

### Verificación de Usuario
- Verificación en tiempo real mientras el usuario escribe el documento
- Previene registro de usuarios duplicados
- Funciona tanto en creación como en modificación

### Almacenamiento JSON
- Las preferencias se almacenan como JSON en la base de datos
- Permite múltiples valores seleccionados
- Fácil de consultar y manipular

## Notas de Seguridad

- Todos los datos de entrada son sanitizados
- Uso de prepared statements para prevenir SQL injection
- Validación tanto en cliente como en servidor
- Headers de seguridad configurados

## Soporte

Para cualquier problema o consulta, revisa:
1. Los logs de errores de PHP
2. La consola del navegador (F12)
3. Los mensajes de error del formulario

