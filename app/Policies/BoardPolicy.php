<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;

/**
 * RN-001: cada prancheta tem um proprietário, e somente ele pode visualizar,
 * editar, excluir e alterar as configurações dela.
 *
 * O acesso público por link é outro caminho e não passa por aqui: ele é
 * anônimo, não tem User, e será resolvido pelo token em shared_links (Fase 4).
 */
class BoardPolicy
{
    public function view(User $user, Board $board): bool
    {
        return $this->owns($user, $board);
    }

    public function update(User $user, Board $board): bool
    {
        return $this->owns($user, $board);
    }

    public function delete(User $user, Board $board): bool
    {
        return $this->owns($user, $board);
    }

    /**
     * RN-001 lista "gerar links de compartilhamento" entre os poderes do
     * proprietário. A regra tem nome próprio, e não reusa `update`, para que a
     * intenção fique visível no `route:list`.
     */
    public function share(User $user, Board $board): bool
    {
        return $this->owns($user, $board);
    }

    private function owns(User $user, Board $board): bool
    {
        return $user->id === $board->user_id;
    }
}
