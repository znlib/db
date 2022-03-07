<?php

namespace ZnLib\Db\Base;

use ZnCore\Base\Helpers\DeprecateHelper;

DeprecateHelper::softThrow();

/**
 * Class BaseEloquentCrudRepository
 * @package ZnLib\Db\Base
 * @deprecated
 */
abstract class BaseEloquentCrudRepository extends \ZnDatabase\Eloquent\Domain\Base\BaseEloquentCrudRepository
{

}
