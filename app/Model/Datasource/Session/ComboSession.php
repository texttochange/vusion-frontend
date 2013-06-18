<?php
App::uses('DatabaseSession', 'Model/Datasource/Session');


class ComboSession extends DatabaseSession implements CakeSessionHandlerInterface {
    public $cacheKey;

    public function __construct() {
        $this->cacheKey = Configure::read('Session.handler.cache');
        parent::__construct();
    }


    // read data from the session.
    public function read($id) {
        $result = Cache::read($id, $this->cacheKey);
        if ($result) {
            return $result;
        }
        return parent::read($id);
    }


    // write data into the session.
    public function write($id, $data) {
        $result = Cache::write($id, $data, $this->cacheKey);
        if ($result) {
            return parent::write($id, $data);
        }
        return false;
    }


    // destroy a session.
    public function destroy($id) {
        $result = Cache::delete($id, $this->cacheKey);
        if ($result) {
            return parent::destroy($id);
        }
        return false;
    }


    // removes expired sessions.
    public function gc($expires = null) {
        return Cache::gc($this->cacheKey) && parent::gc($expires);
    }
}