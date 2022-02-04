<?php

namespace App\Models;

use App\Contracts\MustImplementUpload;
use App\Traits\HasUpload;
use App\Traits\UseRef;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory, UseRef;

    /**
     * Default uploads.
     *
     * @const array<string, string>
     */
    const DEFAULTS_UPLOAD = [
        'avatar' => 'images/default-avatar.png'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ref',
        'filename',
        'url',
    ];

    /**
     * Get the parent uploadable model (eg. user).
     */
    public function uploadable()
    {
        return $this->morphTo();
    }

    /**
     * get specified default type url.
     *
     * @return void
     */
    public static function defaultUploadUrl(Model $model, string $type): string
    {
        abort_unless(
            class_implements($model, MustImplementUpload::class)
            &&
            in_array(HasUpload::class, class_uses($model), true), '403')
        ;

        return $model->upload()->create([
            'url' => secure_url(self::DEFAULTS_UPLOAD[$type])
        ]);
    }
}
