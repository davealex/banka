<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface MustImplementUpload
{
    /**
     * @return MorphOne
     */
    public function upload(): MorphOne;
}
