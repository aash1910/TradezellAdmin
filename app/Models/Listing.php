<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class Listing extends Model
{
    use SoftDeletes;
    use CrudTrait;

    public const IMAGE_UPLOAD_PATH = 'uploads/listings';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'condition',
        'category',
        'price',
        'currency',
        'images',
        'status',
        'lat',
        'lng',
    ];

    protected $casts = [
        'images' => 'array',
        'price'  => 'decimal:2',
        'lat'    => 'decimal:7',
        'lng'    => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function swipes()
    {
        return $this->hasMany(ListingSwipe::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Listing $listing) {
            static::deleteStoredImages($listing->images ?? []);
        });
    }

    /**
     * Normalize incoming image values and persist base64 uploads to disk.
     *
     * @param  array<int, string|null>  $incoming
     * @return string[]
     */
    public function setImagesAttribute($value): void
    {
        // Admin listing edit syncs images separately; do not overwrite via mass assignment.
        if (request()->hasFile('images') || request()->has('clear_images')) {
            return;
        }

        if (is_array($value)) {
            if (collect($value)->every(fn ($item) => $item === null || $item === '')) {
                return;
            }

            $this->attributes['images'] = json_encode(static::processImagesForStorage($value));

            return;
        }

        if (is_string($value) && $value !== '') {
            $this->attributes['images'] = $value;
        }
    }

    /**
     * Handle image uploads/removals from the admin listing edit form.
     *
     * @param  array<int, string>|null  $existingImages  Images before the main form save.
     */
    public function syncImagesFromAdminRequest(?array $existingImages = null): void
    {
        if (! request()->hasFile('images') && ! request()->has('clear_images')) {
            return;
        }

        $images = $existingImages ?? $this->images ?? [];

        if (is_string($images)) {
            $images = json_decode($images, true) ?? [];
        }
        $disk = 'public';
        $destination = self::IMAGE_UPLOAD_PATH;

        foreach ((array) request()->get('clear_images', []) as $path) {
            static::deleteStoredImages([$path]);
            $relative = ltrim($path, '/');
            $images = array_values(array_filter($images, function ($stored) use ($path, $relative) {
                return $stored !== $path && ltrim((string) $stored, '/') !== $relative;
            }));
        }

        if (request()->hasFile('images')) {
            foreach (request()->file('images') as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }

                $extension = $file->getClientOriginalExtension() ?: 'jpg';
                if ($extension === 'jpeg') {
                    $extension = 'jpg';
                }

                try {
                    $encoded = Image::make($file)->encode($extension, 90);
                } catch (\Exception $e) {
                    continue;
                }

                $filename = Str::uuid().'.'.$extension;
                $relative = $destination.'/'.$filename;

                Storage::disk($disk)->put($relative, $encoded);

                $images[] = '/'.$relative;
            }
        }

        $images = array_slice(array_values($images), 0, 5);
        $this->attributes['images'] = json_encode($images);
    }

    public static function processImagesForStorage(array $incoming): array
    {
        return collect($incoming)
            ->map(fn ($image) => static::storeImageValue($image))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return string[]
     */
    public function getPublicImageUrls(): array
    {
        return collect($this->images ?? [])
            ->map(fn ($image) => static::resolvePublicUrl(is_string($image) ? $image : null))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Resolve a stored image value to a URL usable in the admin panel.
     */
    public static function resolveImageUrlForDisplay(?string $image): ?string
    {
        return static::resolvePublicUrl($image);
    }

    /**
     * @return string[]
     */
    public function getDisplayImageUrls(): array
    {
        return $this->getPublicImageUrls();
    }

    public static function resolvePublicUrl(?string $image): ?string
    {
        if (empty($image)) {
            return null;
        }

        if (Str::startsWith($image, 'data:image')) {
            return $image;
        }

        if (Str::startsWith($image, ['file://', 'content://', 'ph://'])) {
            return null;
        }

        $path = static::extractUploadPath($image);

        if ($path !== null) {
            return static::publicBaseUrl().$path;
        }

        if (Str::startsWith($image, ['http://', 'https://', '//'])) {
            return $image;
        }

        return static::publicBaseUrl().'/'.ltrim($image, '/');
    }

    /**
     * Base URL for publicly served uploads (current request host in web, APP_URL otherwise).
     */
    public static function publicBaseUrl(): string
    {
        if (! app()->runningInConsole() && request()) {
            return rtrim(request()->getSchemeAndHttpHost(), '/');
        }

        return rtrim(config('app.url'), '/');
    }

    /**
     * Extract /uploads/... path from a stored path or absolute URL.
     */
    protected static function extractUploadPath(string $image): ?string
    {
        if (Str::startsWith($image, '//')) {
            $image = 'https:'.$image;
        }

        if (Str::startsWith($image, ['http://', 'https://'])) {
            $path = parse_url($image, PHP_URL_PATH) ?: '';
        } else {
            $path = '/'.ltrim($image, '/');
        }

        $relative = ltrim($path, '/');

        if (Str::startsWith($relative, ['uploads/listings/', 'uploads/sample/'])) {
            return '/'.$relative;
        }

        return null;
    }

    /**
     * Delete listing image files from public storage.
     *
     * @param  string[]  $paths
     */
    public static function deleteStoredImages(array $paths): void
    {
        foreach ($paths as $path) {
            if (! is_string($path) || Str::startsWith($path, 'data:')) {
                continue;
            }

            $relative = ltrim($path, '/');

            if (Str::startsWith($relative, self::IMAGE_UPLOAD_PATH.'/')) {
                Storage::disk('public')->delete($relative);
            }
        }
    }

    protected static function storeImageValue(?string $image): ?string
    {
        if (empty($image)) {
            return null;
        }

        if (static::isStoredPath($image)) {
            return static::normalizeStoredPath($image);
        }

        if (Str::startsWith($image, 'data:image')) {
            return static::storeBase64Image($image);
        }

        return null;
    }

    protected static function isStoredPath(string $image): bool
    {
        if (Str::startsWith($image, ['file://', 'content://', 'ph://', 'data:'])) {
            return false;
        }

        if (Str::startsWith($image, ['http://', 'https://', '//'])) {
            return static::extractUploadPath($image) !== null;
        }

        return Str::startsWith(ltrim($image, '/'), ['uploads/listings/', 'uploads/sample/']);
    }

    protected static function normalizeStoredPath(string $image): string
    {
        if (Str::startsWith($image, ['http://', 'https://'])) {
            $path = parse_url($image, PHP_URL_PATH) ?: '';

            return '/'.ltrim($path, '/');
        }

        return '/'.ltrim($image, '/');
    }

    protected static function storeBase64Image(string $value): ?string
    {
        if (! preg_match('/^data:image\/(\w+);base64,(.+)$/', $value, $matches)) {
            return null;
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $binary = base64_decode($matches[2], true);

        if ($binary === false) {
            return null;
        }

        try {
            $image = Image::make($binary)->encode($extension, 90);
        } catch (\Exception $e) {
            return null;
        }

        $filename = Str::uuid().'.'.$extension;
        $relative = self::IMAGE_UPLOAD_PATH.'/'.$filename;

        Storage::disk('public')->put($relative, $image->stream());

        return '/'.$relative;
    }
}
