<?php

namespace App\Enums;

enum TeamRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Member => 'Member',
        };
    }

    public function canManageTeam(): bool
    {
        return $this === self::Owner || $this === self::Admin;
    }

    /**
     * Roles that can be assigned through invitations or role changes.
     * Ownership is never granted this way.
     *
     * @return list<self>
     */
    public static function assignable(): array
    {
        return [self::Admin, self::Member];
    }

    /**
     * @return list<string>
     */
    public static function assignableValues(): array
    {
        return array_map(fn (self $role) => $role->value, self::assignable());
    }
}
