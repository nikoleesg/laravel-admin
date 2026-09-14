<?php

namespace Tests\Models;

use Encore\Admin\Auth\Database\Administrator;
use Parental\HasParent;

class ManagerAdministrator extends Administrator
{
    use HasParent;
}
