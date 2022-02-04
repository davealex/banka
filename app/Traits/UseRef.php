<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait UseRef
{
    /**
     * get route-model binding attribute.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'ref';
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->ref = Str::orderedUuid();
        });
    }
}
