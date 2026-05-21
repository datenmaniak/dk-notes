<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSetting extends Model
{
    protected $fillable = ['user_id', 'key', 'value'];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
        
    }
    
    public static function getValue($userId, $key, $default = null)
    {
        $setting = self::where('user_id', $userId)->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
    
    public static function setValue($userId, $key, $value)
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value]
        );
    }
}
