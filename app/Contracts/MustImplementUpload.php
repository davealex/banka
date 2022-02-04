<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface MustImplementUpload
{
    public function upload(): MorphOne;
}
