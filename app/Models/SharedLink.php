<?php

namespace App\Models;

use App\Enums\BoardVisibility;
use Database\Factories\SharedLinkFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Link publico de uma prancheta (RF-014).
 *
 * O token controla *por onde* o acesso acontece; quem controla *se* ele pode
 * acontecer e boards.visibility. Sao mecanismos separados de proposito — ver
 * docs/03 §6.2.
 */
class SharedLink extends Model
{
    /** @use HasFactory<SharedLinkFactory> */
    use HasFactory;

    /**
     * `board_id` fica fora do fillable pelo mesmo motivo que `user_id` em
     * Board: a prancheta vem da Action, nunca de dado de entrada.
     *
     * @var list<string>
     */
    protected $fillable = [
        'token',
        'expires_at',
    ];

    /**
     * Comprimento do token. 32 caracteres alfanumericos nao expoem o id
     * interno e sao inviaveis de adivinhar por tentativa (docs/03 §7.1).
     */
    private const TOKEN_LENGTH = 32;

    /**
     * Gera um token novo.
     *
     * O formato vive aqui, e nao nas Actions, porque tanto criar quanto trocar
     * um link precisam dele — e a decisao de docs/03 §7.1 deve ter um lugar so.
     */
    public static function newToken(): string
    {
        return Str::random(self::TOKEN_LENGTH);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Restringe aos links que realmente dao acesso publico.
     *
     * Cobre duas das tres condicoes de docs/03 §7.2 — prancheta publica e link
     * ainda nao expirado. A terceira, "o token existe", e o `where('token')` de
     * quem chama, porque o token e o dado que se procura, nao uma regra.
     *
     * Quem precisa resolver um token consulta por este scope em vez de repetir
     * as regras: falhar em qualquer uma significa nao encontrar o link, e o
     * visitante recebe 404.
     *
     * @param  Builder<SharedLink>  $query
     */
    public function scopeAccessible(Builder $query): void
    {
        $query
            ->whereHas('board', function (Builder $board): void {
                $board->where('visibility', BoardVisibility::Public);
            })
            ->where(function (Builder $link): void {
                $link->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }
}
