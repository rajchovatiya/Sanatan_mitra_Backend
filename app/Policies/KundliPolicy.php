<?php

namespace App\Policies;

use App\Models\Kundli;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class KundliPolicy
{
    /** Someone else's record answers 404, so ids cannot be probed. */
    public function update(User $user, Kundli $kundli): Response
    {
        return $this->owns($user, $kundli);
    }

    public function delete(User $user, Kundli $kundli): Response
    {
        return $this->owns($user, $kundli);
    }

    private function owns(User $user, Kundli $kundli): Response
    {
        return $user->id === $kundli->user_id ? Response::allow() : Response::denyAsNotFound();
    }
}
