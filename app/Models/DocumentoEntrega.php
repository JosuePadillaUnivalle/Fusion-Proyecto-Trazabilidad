<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoEntrega extends Model
{
    protected $table = 'documento_entrega';
    protected $primaryKey = 'documentoentregaid';

    protected $fillable = [
        'externo_envio_id',
        'pedidoid',
        'usuarioid',
        'tipo_documento',
        'titulo',
        'archivo_path',
        'metadata',
        'almacenid',
    ];

    protected $casts = [
        'documentoentregaid' => 'integer',
        'pedidoid' => 'integer',
        'usuarioid' => 'integer',
        'almacenid' => 'integer',
        'metadata' => 'array',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedidoid', 'pedidoid');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuarioid', 'usuarioid');
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacenid', 'almacenid');
    }
}

