<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushNotificationUser extends Model
{
    use HasFactory;
    protected $table = 'push_notification_users';
    protected $fillable = ['user_id', 'message_id', 'leido', 'is_new'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function message()
    {
        return $this->belongsTo(PushNotificationMsg::class, 'message_id');
    }
}
