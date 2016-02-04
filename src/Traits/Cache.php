<?php
namespace App\Traits;

trait Cache
{
    // Requires
//    protected $cacheDir = './cache';

    protected function getCachedData($objectType, $objectId, $cacheBuilder)
    {
        static $cache;

        if (!empty($cache[$objectType.'-'.$objectId])) {
            return $cache[$objectType.'-'.$objectId];
        }

        $cacheFile = $this->cacheDir."/$objectType-$objectId.cache";

        // TTL = 10min
//        if (file_exists($cacheFile)) {
//            if (time() - filectime($cacheFile) > 600) {
//                unlink($cacheFile);
//            }
//        }

        if (file_exists($cacheFile)) {
            $object = unserialize(file_get_contents($cacheFile));
        } else {
            $object = $cacheBuilder();

            if (!file_exists($this->cacheDir)) {
                mkdir($this->cacheDir, 0777, true);
            }

            file_put_contents($cacheFile, serialize($object));
        }

        $cache[$objectType.'-'.$objectId] = $object;
        return $object;
    }
}
