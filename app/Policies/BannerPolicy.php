<?php

namespace App\Policies;

use App\Models\Banner;
use App\Models\User;

class BannerPolicy extends Policy
{
    public function view(User $authUser, Banner $banner)
    {
        return true;
    }

    public function create(User $authUser)
    {
        return $authUser->can('create-banner');
    }

    public function update(User $authUser, Banner  $banner)
    {
        return $banner->user_id == $authUser->id || $authUser->can('update-banner');
    }

    public function delete(User $authUser, Banner  $banner)
    {
        return $banner->user_id == $authUser->id || $authUser->can('delete-banner');
    }
}
