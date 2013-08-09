<?php

/*
 * cache.php
 * Andrew Kubera
 * 
 * 
 * File for managing the templr caching mechanism
 * 
 */

namespace templr;

/**
 * Cache object stores and retrives compiled files from local storage
 * 
 */
class Cache {

    var $cache_dir = "";
    
    function __construct($cache_dir = TEMPLR_CACHE_DIR) {
        $this->cache_dir = $cache_dir;
        _check_cache_dir();
    }
    
    /**
     * Checks the cache dir for existance and read/write ability
     * 
     * 
     * 
     */
    protected function _check_cache_dir() {
        $stat = @\stat($this->cache_dir);
        
        if (!$stat) {
            // stat call failed - attempt make the directory
            if (!@\mkdir($this->cache_dir)){
                // directory could not be created
                throw ("Could not create cache directory!");
            }
            
        } else if (!($stat[mode] & 040000)) {
            // not a directory !
        }
    }
}
