<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Document;

class DocumentPolicy
{
    public function update(User $user, Document $doc): bool { return $doc->user_id === $user->id; }
    public function view(User $user, Document $doc): bool   { return $doc->user_id === $user->id; }
}
