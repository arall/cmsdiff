<?php

namespace Arall\CMSDiff\GitHub;

use InvalidArgumentException;

/**
 * GitHub Repository manager.
 */
class Repository
{
    /**
     * Owner.
     *
     * @var string
     */
    public $owner;

    /**
     * Repo.
     *
     * @var string
     */
    public $repo;

    /**
     * Construct.
     *
     * @param string $name
     *
     * @throws Exception Invalid repository
     */
    public function __construct($name)
    {
        $tmp = explode('/', $name);
        if (count($tmp) != 2) {
            throw new InvalidArgumentException('Invalid repository name: '.$name);
        }

        $this->owner = $tmp[0];

        $this->repo = $tmp[1];
    }

    /**
     * Get tags.
     *
     * @return array
     */
    public function getTags()
    {
        $tags = array();
        $page = 1;

        do {
            $result = API::call('repos/'.$this->owner.'/'.$this->repo.'/tags?page='.$page);
            if (!empty($result)) {
                $tags = array_merge($tags, $result);
                $page++;
            } else {
                break;
            }
        } while (true);

        return $tags;
    }
}
