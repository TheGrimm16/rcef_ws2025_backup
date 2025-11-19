<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class SeedReplacementRequest extends Model
{
    protected $connection = 'local_request';
    protected $table = 'tbl_requests';
    protected $primaryKey = 'id';

    // Important for string PK
    public $incrementing = false;       // Disable auto-increment
    protected $keyType = 'string';      // Tell Eloquent it's a string

    public $timestamps = true;

    protected $fillable = [
        'id',                 // add this so we can mass-assign SRID
        'user_id',
        'new_released_id',
        'geo_code',
        'purpose_id',
        'attachment_dir'
    ];

    public function user()
    {
        return $this->belongsTo(
            'App\Models\SeedReplacementUser',
            'user_id',
            'userId'
        );
    }

    /**
     * Mutator to automatically store uploaded file in a dynamic folder
     * Folder example: seed_replacement/{userId}/{year}/{month}
     */
    public function setAttachmentDirAttribute($file)
    {
        if ($file instanceof UploadedFile) {
            $folder = $this->buildAttachmentFolder();
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs($folder, $filename);
            $this->attributes['attachment_dir'] = $path;
        } else {
            $this->attributes['attachment_dir'] = $file;
        }
    }

    /**
     * Build dynamic folder path with placeholders
     */
    protected function buildAttachmentFolder()
    {
        
    $placeholders = array(
        '{userId}' => isset($this->user_id) ? $this->user_id : 'unknown_user',
        '{year}'   => date('Y'),
        '{month}'  => date('m')
    );

        $folderTemplate = 'seed_replacement/{userId}/{year}/{month}';

        return str_replace(array_keys($placeholders), array_values($placeholders), $folderTemplate);
    }

    /**
     * Accessor to get full public URL for attachment
     */
    public function getAttachmentUrlAttribute()
    {
        return $this->attachment_dir ? asset('storage/' . $this->attachment_dir) : null;
    }
}
