<?php

namespace LaravelForum;

use LaravelForum\Notifications\ReplyMarkedAsBestReply;

class Discussion extends Model
{

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(Replay::class);
    }

    public function getRouteKeyName()
    {
      return 'slug';
    }

    public function bestReply()
    {
        return $this->belongsTo(Replay::class, 'reply_id');
    }

    public function scopeFilterByChannels($builder)
    {
        if (request()->query('channel')) {
            // filter
            $channel = Channel::where('slug', request()->query('channel'))->first();

            if ($channel) {
                return $builder->where('channel_id', $channel->id);
            }

            return $builder;
        }

        return $builder;
    }

    public function markAsBestReply(Replay $replay)
    {
        $this->update([
            'reply_id' => $replay->id
        ]);

        if ($replay->owner->id === $this->author->id) {
            return;
        }

        $replay->owner->notify(new ReplyMarkedAsBestReply($replay->discussion));

    }

}
