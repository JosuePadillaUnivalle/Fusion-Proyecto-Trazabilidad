---
name: AgroNexus Models Guide
description: Documentation for AI agents on the Laravel models architecture, naming conventions, and relationships in the traceability system
---

# AgroNexus / Fusion Models Architecture

**Project**: Agricultural Traceability System | **Tech Stack**: Laravel + PostgreSQL | **Language**: PHP 8.2+

## Quick Start for AI Agents

When working with models in this project:
1. Models use **custom primary keys** (e.g., `loteid`, `usuarioid`) NOT Laravel defaults
2. Models live in `app/Models/` — follow existing naming conventions (PascalCase)
3. Tables use **snake_case** with Spanish names (e.g., `usuario`, `lote`, `cultivo`)
4. Many models **disable timestamps** (`public $timestamps = false`) — manage dates manually
5. Always check `$fillable` array before mass assignment; use explicit field lists
6. Use `protected $casts` for type safety (dates, floats, integers)

## Model Naming & Conventions

| Convention | Example | Notes |
|-----------|---------|-------|
| **Model class** | `Usuario`, `Lote`, `Cultivo` | PascalCase, singular form |
| **Table name** | `usuario`, `lote`, `cultivo` | snake_case, plural implied |
| **Primary key** | `usuarioid`, `loteid`, `cultivoid` | Suffix `id` to singular table name |
| **Foreign keys** | `usuarioid` in other tables | Reference pattern: `{table}id` |
| **Timestamps** | Often disabled | Manage with `fecharegistro`, `fechamodificacion`, `ultimologin` manually |

## Core Models & Responsibilities

### Agricultural Foundation
- **`Lote`** (Plot/Batch): Core entity representing a cultivation plot. References cultivo, usuario, estado. Links to producciones, loteInsumos, actividades.
- **`Cultivo`** (Crop): Type of cultivation (e.g., tomato, lettuce). Master catalog entity.
- **`Produccion`** (Production): Output from a lote. Connected to almacenamiento (warehouse).

### Operations & Logistics
- **`Almacen`** (Warehouse): Storage facilities with types (e.g., cold storage, general).
- **`AlmacenMovimiento`** (Warehouse Movement): Tracks inventory transactions in/out.
- **`Envio`** / **`EnvioPendiente`** (Shipment): Orders in transit with tracking.
- **`RutaMultiEntrega`** (Multi-stop Route): Delivery routes with multiple paradas (stops).

### Planning & Resources
- **`Actividad`** (Activity): Tasks assigned to lotes (e.g., irrigation, pest control).
- **`TipoActividad`** (Activity Type): Classification of activities.
- **`Insumo`** (Input/Supply): Materials used (seeds, fertilizers, pesticides).
- **`LoteInsumo`** (Lote-Input Link): Join table tracking insumo usage per lote.

### Process & Quality
- **`ProcesoPlanta`** (Plant Process): Industrial processing workflows.
- **`MaquinaPlanta`** (Plant Machinery): Equipment in processing.
- **`EstadoLote`** (Lote Status): State transitions for lotes (historical log).
- **`EstadoLoteInsumo`** (Lote-Input Status): Status of inputs within a lote.
- **`HistorialEstadoLote`** (Status History): Audit trail of status changes.
- **`CertificacionLote`** (Certification): Quality certifications and compliance.

### Sales & Commerce
- **`Pedido`** (Order): Customer orders with detailLotes (details).
- **`DetallePedido`** (Order Detail): Line items in an order.
- **`Venta`** (Sale): Completed sales records.
- **`DestinoProduccion`** (Production Destination): Where production is routed (market, export, etc.).

### People & Organization
- **`Usuario`** (User): System users with roles (uses Spatie permissions).
- **`Rol`** (Role): Permission roles (ADMIN, OPERATOR, etc.).
- **`UsuarioRol`** (User-Role Link): User role assignments.
- **`ActorAbastecimiento`** (Supply Actor): External suppliers/vendors.

### Reference & Master Data
- **`Clima`** (Weather): Historical weather data per lote (OpenWeather integration).
- **`UnidadMedida`** (Unit of Measure): Standard units (kg, liters, hectares, etc.).
- **`TipoInsumo`** (Input Type): Categories for insumos.
- **`TipoAlmacen`** (Warehouse Type): Categories for almacenes.
- **`TipoMovimientoAlmacen`** (Movement Type): Types of warehouse transactions.
- **`Prioridad`** (Priority): Priority levels for tasks/shipments.

## Relationship Patterns

### Common Relationship Structures

```php
// belongsTo (Foreign key on this model)
public function usuario()
{
    return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
}

// hasMany (One-to-many from this model)
public function loteInsumos()
{
    return $this->hasMany(LoteInsumo::class, 'loteid', 'loteid');
}

// Many-to-many (via pivot table)
// Example: User has many Roles (via usuario_rol)
public function roles()
{
    return $this->belongsToMany(Rol::class, 'usuario_rol', 'usuarioid', 'rolid');
}
```

### Key Relationship Examples

| From | To | Type | Key | Example |
|------|----|----|-----|---------|
| `Lote` | `Usuario` | belongsTo | `usuarioid` | Tracks who owns/manages the plot |
| `Lote` | `Cultivo` | belongsTo | `cultivoid` | What is being grown |
| `Lote` | `EstadoLote` | hasMany | `loteid` | Historical status log |
| `Lote` | `Produccion` | hasMany | `loteid` | Multiple harvests per plot |
| `Lote` | `LoteInsumo` | hasMany | `loteid` | Materials used |
| `Lote` | `Actividad` | hasMany | `loteid` | Tasks performed |
| `Produccion` | `AlmacenMovimiento` | hasMany | `produccionid` | Warehouse transactions |
| `Pedido` | `DetallePedido` | hasMany | `pedidoid` | Order line items |
| `Usuario` | `Rol` | belongsToMany | via `usuario_rol` | Permissions (Spatie) |

## Working with Models: Best Practices

### 1. **Mass Assignment with $fillable**
Always define explicit `$fillable` arrays—never use `$guarded = []`:
```php
protected $fillable = [
    'nombre',
    'apellido',
    'email',
    'usuarioid',  // if needed
];
```

### 2. **Type Casting with $casts**
Use `$casts` to ensure type safety:
```php
protected $casts = [
    'usuarioid' => 'integer',
    'almacenid' => 'integer',
    'activo' => 'boolean',
    'fecharegistro' => 'datetime',
    'superficie' => 'float',
];
```

### 3. **Hidden Attributes**
Hide sensitive or nested relations by default to prevent accidental exposure:
```php
protected $hidden = [
    'passwordhash',
    'usuario',  // Avoid circular includes
    'cultivo',
];
```

### 4. **Manual Date Management**
Most models don't use Laravel's automatic `created_at`/`updated_at`. Instead:
- Use `fecharegistro` (registration/creation date)
- Use `fechamodificacion` (last modified)
- Use `ultimologin` (for users) or similar domain-specific dates
- Manually set these in controllers or models when creating/updating

### 5. **Custom Primary Keys**
Always specify when not using `id`:
```php
protected $table = 'lote';
protected $primaryKey = 'loteid';
public $timestamps = false;  // Most models have this
```

## API Relationships & Controllers

Controllers follow domain grouping:
- **`Web/`**: Web interface controllers (60+ models)
- **`Api/`**: API endpoints (auth, external integrations)

Key controller structure:
- `LoteController` handles Lote CRUD + relationships
- `ProduccionController` manages production & warehouse linking
- `EnvioController` & `AsignacionMultipleController` handle logistics
- `DashboardController` aggregates data across models

## Querying Models: Common Patterns

```php
// Eager load relationships
$lote = Lote::with(['usuario', 'cultivo', 'producciones'])->find($id);

// Filter by custom key
$usuario = Usuario::where('usuarioid', $id)->first();

// Lazy load relationships
$lote->load('loteInsumos', 'actividades');

// Use scope for reusable filters
$activeUsers = Usuario::where('activo', true)->get();
```

## Important Model-Specific Notes

- **`Usuario`**: Uses Spatie permissions (`HasRoles` trait). Primary key is `usuarioid`.
- **`Lote`**: Core entity—links nearly all other models. No timestamps by design.
- **`ProcesoPlanta`**: Newer addition for industrial process tracking. May have additional metadata fields.
- **`AlmacenMovimiento`** & **`EnvioPendiente`**: Recently refactored for consolidation (see migrations).
- **`EnvioAsignacionMultiple`**: Handles multi-unit shipments with JSON metadata.
- **Models with JSON columns**: Some newer models store supplementary data as JSON (e.g., metadatos, detalles_productos).

## Migrations & Schema

Migrations follow chronological naming. Key migration groups:
1. **Core tables** (`2025_12_11_000000` — `_000003`): usuario, lote, cultivo, almacen
2. **Operational tables** (`2025_12_11_000002`): envios, envios_pendientes
3. **Consolidated tables** (`2026_04_25` onward): Plant processes, certifications, logistics refinements
4. **Recent updates** (`2026_05_06`): Almacen consolidations, JSON metadata fields

When adding new models:
- Create migration with explicit `->id()` or custom primary key definition
- Follow table naming (snake_case, implied plural)
- Define foreign keys with proper references
- Use indexes on frequently filtered columns

## Quick Reference: Running Common Queries

```php
// Fetch lote with all relationships
$lote = Lote::with(['usuario', 'cultivo', 'estados', 'producciones'])->find($id);

// Get active users by warehouse
$usuarios = Usuario::where('activo', true)->where('almacenid', $warehouseId)->get();

// Track lote status history
$statusHistory = HistorialEstadoLote::where('loteid', $id)->orderBy('fechacambio', 'desc')->get();

// Find pending shipments
$pending = EnvioPendiente::where('estadoenvio', 'pending')->get();

// Aggregate production data
$totalProduccion = Produccion::where('loteid', $id)->sum('cantidad');
```

---

**Last Updated**: 2026-05-28 | **Domain**: Agricultural Traceability | **Tech**: Laravel 11 + PostgreSQL
