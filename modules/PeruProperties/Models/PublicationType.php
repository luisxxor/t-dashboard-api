<?php

namespace Modules\PeruProperties\Models;

use Modules\Common\Models\PublicationType as CommonPublicationType;

class PublicationType extends CommonPublicationType
{
    /**
     * @var string
     */
    protected $connection = 'pe-properties';

    /**
     * @var string
     */
    protected $collection = 'publication_types';

    /**
     * @var string
     */
    protected $primaryKey = '_id';
}
