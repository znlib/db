<?php

namespace ZnLib\Db\Base;

use ZnCore\Base\Helpers\DeprecateHelper;

DeprecateHelper::softThrow();

/**
 * Class BaseEloquentRepository
 * @package ZnLib\Db\Base
 * @deprecated
 */
abstract class BaseEloquentRepository extends \ZnDatabase\Eloquent\Domain\Base\BaseEloquentRepository
{

}
