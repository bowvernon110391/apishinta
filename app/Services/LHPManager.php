<?php
namespace App\Services;

use App\LHP;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LHPManager {
    public function __construct(Instancer $instancer)
    {
        $this->instancer = $instancer;
    }

    // a simple class to manage lhp related stuffs
    public function resolveParent($path) {
        // find the owner of the lhp in question based on route
        if (preg_match("/(\w+)\/(\d+)\/lhp$/i", $path, $matches)) {
            // the first group is the name, second is the id
            return $this->instancer->find($matches[1], $matches[2]);
        }
    }

    // is it a simple request
    public function isDefaultRequest($path) {
        if (preg_match("/lhp\/(\d+)$/i", $path, $matches)) {
            return $matches[1];
        }
        return false;
    }

    // is it a parent based request
    public function isParentBasedRequest($path) {
        return preg_match("/(\w+)\/(\d+)/i", $path);
    }

    // return an instance based on route?
    public function findOrFail($path) {
        // depending on route, solve the instances
        // 1st case, simple parent based
        $parent = $this->resolveParent($path);

        if (!$parent && $this->isParentBasedRequest($path)) {
            throw new ModelNotFoundException("Parent of LHP was not found [$path]");
            return null;
        }

        if ($parent) {
            if (!method_exists($parent, 'lhp')) {
                throw new \Exception("'" . class_basename($parent). "' cannot have LHP!");
            }
            return $parent->lhp()->firstOrFail();
        }

        // hmm, perhaps the standard /lhp/id?
        if ( ($lhpId = $this->isDefaultRequest($path)) !== false) {
            return LHP::findOrFail($lhpId);
        }

        // UNHANDLED Path resolve
        throw new \Exception("no specifier for path [{$path}]...");
        return null;
    }

    // find only?
    public function find($path) {
        // depending on route, solve the instances
        // 1st case, simple parent based
        $parent = $this->resolveParent($path);

        if (!$parent && $this->isParentBasedRequest($path)) {
            // throw new ModelNotFoundException("Parent of LHP was not found [$path]");
            return null;
        }

        if ($parent) {
            if (!method_exists($parent, 'lhp')) {
                // throw new \Exception("'" . class_basename($parent). "' cannot have LHP!");
                return null;
            }
            return $parent->lhp()->firstOrFail();
        }

        // hmm, perhaps the standard /lhp/id?
        if ( ($lhpId = $this->isDefaultRequest($path)) !== false) {
            return LHP::find($lhpId);
        }

        // UNHANDLED Path resolve
        // throw new \Exception("no specifier for path [{$path}]...");
        return null;
    }
}