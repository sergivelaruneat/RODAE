<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'bio','sport','birthdate',
        'avatar_b64','avatar_mime','avatar_size'
    ];

    protected $casts = ['birthdate' => 'date'];
    protected $guarded = ['role'];

    // ¡IMPRESCINDIBLE!
    protected $appends = ['avatar_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function getAvatarUrlAttribute()
    {
        // si guardas en BBDD en base64
        if ($this->avatar_b64 && $this->avatar_mime) {
            return 'data:'.$this->avatar_mime.';base64,'.$this->avatar_b64;
        }
        // (opcional) si alguna vez usas filesystem
        if ($this->avatar) return asset('storage/'.$this->avatar);
        return null;
    }
}

