<?php

namespace App\Traits;

use App\Models\Upload;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasUpload
{
    /**
     * Associate an Upload with an uploadable instance. eg. User
     *
     * @return MorphOne
     */
    public function upload(): MorphOne
    {
        return $this->morphOne(Upload::class, 'uploadable');
    }
}
