<?php

namespace Common\Workspaces;

use App\User;
use Carbon\Carbon;
use Common\Auth\Traits\HasAvatarAttribute;
use Common\Auth\Traits\HasDisplayNameAttribute;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\WorkspaceInvite
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Workspace workspace
 * @property int $role_id
 * @property int $workspace_id
 * @property string $email
 * @property User user
 * @mixin Eloquent
 */
class WorkspaceInvite extends Model
{
    use HasDisplayNameAttribute, HasAvatarAttribute;

    protected $guarded = ['id'];
    protected $appends = ['display_name', 'model_type'];

    protected $keyType = 'orderedUuid';
    public $incrementing = false;

     protected $casts = [
         'user_id' => 'integer',
     ];

     public function workspace(): BelongsTo
     {
         return $this->belongsTo(Workspace::class);
     }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getModelTypeAttribute() {
        return 'invite';
    }
}
