<?php

namespace App\Models;

use App\Enums\BoardCategory;
use App\Enums\BoardVisibility;
use Database\Factories\BoardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    /** @use HasFactory<BoardFactory> */
    use HasFactory;

    /**
     * `user_id` fica fora do fillable de proposito: a propriedade e definida
     * pela Action a partir do usuario autenticado, nunca por dado de entrada.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'category',
        'canvas_data',
        'visibility',
    ];

    /**
     * Uma prancheta nova nasce com o canvas vazio, nunca com null: assim o
     * editor da Fase 3 nao precisa tratar ausencia de estrutura.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'canvas_data' => '{"elements":[]}',
        'visibility' => BoardVisibility::Private->value,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'canvas_data' => 'array',
            'category' => BoardCategory::class,
            'visibility' => BoardVisibility::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<SharedLink, $this>
     */
    public function sharedLinks(): HasMany
    {
        return $this->hasMany(SharedLink::class);
    }
}
