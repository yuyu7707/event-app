<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $description
 * @property string $venue
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['user_id', 'title', 'description', 'venue', 'starts_at', 'ends_at'])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * このイベントを作ったユーザー。
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * このイベントの券種。
     *
     * @return HasMany<TicketType, $this>
     */
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    /**
     * 一覧に出す価格表示。最安の券種で「1,500円〜」「無料」、券種が無ければ「─」。
     * withMin('ticketTypes', 'price') で読み込み済みならそれを使う。
     */
    public function lowestPriceLabel(): string
    {
        $lowestPrice = array_key_exists('ticket_types_min_price', $this->attributes)
            ? $this->attributes['ticket_types_min_price']
            : $this->ticketTypes()->min('price');

        if ($lowestPrice === null) {
            return '─';
        }

        if ((int) $lowestPrice === 0) {
            return '無料';
        }

        return number_format((int) $lowestPrice).'円〜';
    }

    /**
     * ログイン中のユーザーが作ったイベントか。
     */
    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && $this->user_id === $user->id;
    }
}
