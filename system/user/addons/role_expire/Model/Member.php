<?php

namespace Mithra62\RoleExpire\Model;

use ExpressionEngine\Service\Model\Model;

class Member extends Model
{
    protected static $_primary_key = 'id';
    protected static $_table_name = 'role_expire_members';

    protected $id;
    protected $member_id;
    protected $date_registered;
    protected $date_activated;

    protected static $_relationships = [
        'Member' => [
            'type' => 'BelongsTo',
            'model' => 'ee:Member',
            'from_key' => 'member_id',
            'to_key' => 'member_id',
        ],
    ];
}