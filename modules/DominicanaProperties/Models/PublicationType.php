<?php

namespace Modules\DominicanaProperties\Models;

use Modules\Common\Models\PublicationType as CommonPublicationType;

class PublicationType extends CommonPublicationType
{
    /**
     * @var string
     */
    protected $connection = 'do-properties';

    /**
     * @var string
     */
    protected $collection = 'publication_types';

    /**
     * @var string
     */
    protected $primaryKey = '_id';
}
