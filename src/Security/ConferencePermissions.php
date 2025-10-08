<?php

declare(strict_types=1);

namespace App\Security;

enum ConferencePermissions
{
    public const NEW = 'conference/new';
    public const EDIT = 'conference/edit';
    public const LIST = 'conference/list';
}
