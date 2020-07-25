<?php

namespace Larapress\CRUD\Services\Session;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Larapress\CRUD\Extend\Helpers;
use Larapress\CRUD\ICRUDUser;

class SessionService implements ISessionService
{
    private $redis;
    private $req;

    public function __construct(Request $request)
    {
        $this->redis = Redis::connection(config('larapress.crud.session.connection'));
        $this->req = $request;
    }

    /**
     * @param string    $key
     * @param string    $value
     * @param \Larapress\CRUD\ICRUDUser $user
     *
     * @return ISessionService
     */
    public function setForUser($key, $value, $user = null)
    {
        $this->redis->set($this->getKey($key, $user), $value);

        return $this;
    }

    /**
     * @param string    $key
     * @param ICRUDUser $user
     * @param mixed     $default
     *
     * @return mixed
     */
    public function getForUser($key, $user = null, $default = null)
    {
        return $this->redis->get($this->getKey($key, $user));
    }

    /**
     * @param string    $key
     * @param ICRUDUser $user
     *
     * @return string
     */
    protected function getKey($key, $user)
    {
        $storage_id = Helpers::randomString();
        $storage_id = $this->req->session()->get('storage-id', $storage_id);
        $this->req->session()->put('storage-id', $storage_id);
        $user_id = is_null($user) ? 'none' : $user->id;

        return 'session.users.'.$user_id.'.session.'.$storage_id.'.'.$key;
    }
}
