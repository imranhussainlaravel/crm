<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// DM channel name is "chat.dm.{minId}.{maxId}" - only those two users may join.
Broadcast::channel('chat.dm.{userAId}.{userBId}', function ($user, $userAId, $userBId) {
    return in_array((int) $user->id, [(int) $userAId, (int) $userBId], true);
});

// Group channels are open to any authenticated panel user.
Broadcast::channel('chat.group.{channel}', function ($user, $channel) {
    return (bool) $user;
});

// Presence channel for online/offline indicators across the team.
Broadcast::channel('presence.team', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});
